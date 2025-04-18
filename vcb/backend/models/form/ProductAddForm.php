<?php

namespace backend\models\form;

use common\components\utils\Strings;
use common\models\db\Product;
use common\util\ImageUtils;
use yii\base\Model;
use Yii;
use common\components\libs\Tables;
use common\models\db\ProductImage;
use common\models\db\ProductAreaProduct;
use common\components\utils\Validation;
use common\models\db\Keyword;
use common\models\business\KeywordBusiness;

class ProductAddForm extends Product {

    public $product_area_quantities = null;
    public $product_area_ids = null;
    public $product_area_status = null;
    public $keywords = null;

    public function rules() {
        return [
            [['product_category_id', 'producer_id',
            'status', 'publish', 'position', 'weight','is_bulky',
            'view', 'new', 'promotion', 'hot',
            'total_quantity', 'class', 'type'
                ], 'integer', 'message' => '{attribute} không hợp lệ'],
            [['description', 'content', 'tags'], 'string'],
            [['name', 'code', 'weight', 'position'], 'required', 'message' => 'Bạn phải nhập {attribute}'],
            [['name', 'code'], 'string', 'max' => 255],
            [['product_category_id'], 'isProductCategoryId'],
            [['producer_id'], 'isProducerId'],
            [['name'], 'isName'],
            [['code'], 'isCode'],
            [['product_category_id', 'producer_id'], 'number', 'min' => 1, 'tooSmall' => 'Bạn phải chọn {attribute}'],
            [['product_area_quantities', 'product_area_ids', 'product_area_status'], 'isArrayInteger', 'message' => '{attribute} không hợp lệ'],
            [['keywords'], 'isKeywords'],
        ];
    }

    public function attributeLabels() {
        $attributes = parent::attributeLabels();
        $attributes['product_area_quantities'] = 'Số lượng';
        $attributes['product_area_ids'] = 'Khu vực giao hàng';
        $attributes['product_area_status'] = 'Trạng thái';
        $attributes['keywords'] = 'Từ khóa tìm kiếm';
        return $attributes;
    }
    
    public function isKeywords($attribute, $params) {
        if (!empty($this->$attribute) && !is_array($this->$attribute)) {
            $this->addError($attribute, $labels[$attribute] . ' không hợp lệ');
        }
    }

    public function isArrayInteger($attribute, $params) {
        if (!empty($this->$attribute) && !Validation::isArrayInteger($this->$attribute)) {
            $labels = $this->attributeLabels();
            $this->addError($attribute, $labels[$attribute] . ' không hợp lệ');
        }
    }

    public function beforeSave($insert) {
        if ($insert) {
            $this->status = self::STATUS_ACTIVE;
            $this->publish = self::UNPUBLISH;
            $this->view = 0;
            $this->total_quantity = 0;
            $this->promotion = 0;
            $this->time_created = time();
            $this->user_created = Yii::$app->user->getId();
        }
        $this->type = Product::getTypeByClass($this->class);
        $keyword_ids = $this->_getKeywordIds();
        $this->tags = json_encode($keyword_ids);
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes) {
        if ($insert === true) {
            $this->id = $this->getDb()->getLastInsertID();
            $this->_updateProductAreaProducts();
            $this->_addProductImages();
            $this->_setProductImageDefault();
        } else {
            $this->_updateProductAreaProducts();
            $this->_deleteProductImages();
            $this->_addProductImages($this->_getMaxPosition());
            $this->_setProductImageDefault();
        }
        parent::afterSave($insert, $changedAttributes);
    }
    
    protected function _getKeywordIds() {
        $result = array();
        if (!empty($this->keywords)) {
            foreach ($this->keywords as $value) {
                $keyword_id = $this->_getKeywordId($value);
                if ($keyword_id !== false && !in_array($keyword_id, $result)) {
                    $result[] = $keyword_id;
                }
                if (count($result) >= 5) {
                    break;
                }
            }
        }
        return $result;
    }
    
    protected function _getKeywordId($value) {
        if (intval($value) != 0) {
            if (Keyword::getInfoById(intval($value))) {
                return intval($value);
            }
        } else {
            $value = Strings::lowerCase(trim($value));
            $keyword_info = Keyword::getInfoByName($value);
            if ($keyword_info != false) {
                return intval($keyword_info['id']);
            } else {
                $inputs = array(
                    'name' => $value, 
                    'link' => '', 
                    'weight' => 1,
                    'user_created' => Yii::$app->user->getId(),
                );
                $result = KeywordBusiness::add($inputs);
                if ($result['error_message'] == '') {
                    return intval($result['id']);
                }
            }
        }
        return false;
    }

    protected function _deleteProductImages() {
        $ids = array();
        $image_ids = Yii::$app->request->post('image_ids');
        if (!empty($image_ids)) {
            foreach ($image_ids as $image_id) {
                if (intval($image_id) != 0) {
                    $ids[] = intval($image_id);
                }
            }
        }
        if (!empty($ids)) {
            $conditions = "id NOT IN (" . implode(',', $ids) . ") AND product_id = " . $this->id . " ";
        } else {
            $conditions = "product_id = " . $this->id . " ";
        }
        $data = Tables::selectAllDataTable("product_image", $conditions, " ");
        if ($data != false) {
            foreach ($data as $row) {
                $default_path = IMAGES_PRODUCT_PATH . 'default' . DS . date('Ymd', $row['time_created']) . DS . $row['image'];
                if (file_exists($default_path)) {
                    @unlink($default_path);
                }
                $small_path = IMAGES_PRODUCT_PATH . 'small' . DS . date('Ymd', $row['time_created']) . DS . $row['image_small'];
                if (file_exists($small_path)) {
                    @unlink($small_path);
                }
            }
            ProductImage::deleteAll($conditions);
        }
    }

    protected function _setProductImageDefault() {
        $model = ProductImage::findBySql("SELECT * FROM product_image WHERE product_id = " . $this->id . " ORDER BY product_image.default DESC, position ASC, id DESC ")->one();
        if ($model != null) {
            if ($model->default != 1) {
                $model->default = 1;
                $model->time_updated = time();
                $model->user_updated = Yii::$app->user->getId();
                $model->save();
            }
        }
    }

    protected function _getMaxPosition() {
        $data = Tables::selectOneDataTable("product_image", 1, "position DESC ");
        if ($data != false) {
            return $data['position'];
        }
        return 0;
    }

    protected function _updateProductAreaProducts() {
        $now = time();
        $transaction = ProductAreaProduct::getDb()->beginTransaction();
        //--------
        $all = true;
        if (Tables::selectOneDataTable("product_area_product", "product_id = " . $this->id) != false) {
            $command = ProductAreaProduct::getDb()->createCommand();
            $command->delete("product_area_product", "product_id = " . $this->id);
            $delete = $command->execute();
            if (!$delete) {
                $all = false;
            }
        }
        if ($all) {
            $product_area_products = $this->getProductAreaProducts();
            if (!empty($product_area_products)) {
                foreach ($product_area_products as $item) {
                    $model = new ProductAreaProduct();
                    $model->product_id = $this->id;
                    $model->product_area_id = $item['product_area_id'];
                    $model->status = $item['status'];
                    $model->quantity = $item['quantity'];
                    $model->time_created = $now;
                    $model->user_created = Yii::$app->user->getId();
                    $model->time_updated = $now;
                    $model->user_updated = Yii::$app->user->getId();
                    if ($model->validate()) {
                        if (!$model->save()) {
                            $all = false;
                            break;
                        }
                    } else {
                        $all = false;
                        break;
                    }
                }
            }
        }
        if ($all) {
            $command = Product::getDb()->createCommand();
            $command->update("product", array('total_quantity' => $this->_getTotalQuantity(), 'time_updated' => time()), "id = " . $this->id);
            $update = $command->execute();
            if (!$update) {
                $all = false;
            }
        }
        if ($all) {
            $transaction->commit();
            return true;
        } else {
            $transaction->rollBack();
            return false;
        }

        return true;
    }

    protected function _getProductAreaProducts() {
        $product_area_product_info = Tables::selectAllDataTable("product_area_product", "product_id = " . $this->id);
        return $product_area_product_info;
    }

    protected function _getTotalQuantity() {
        $total_quantity = 0;
        $product_area_product_info = $this->getProductAreaProducts();
        if (!empty($product_area_product_info)) {
            foreach ($product_area_product_info as $row) {
                $total_quantity+= intval($row['quantity']);
            }
        }
        return $total_quantity;
    }

    protected function _addProductImages($index = 0) {
        $image_datas = Yii::$app->request->post('image_datas');
        if (!empty($image_datas)) {
            $now = time();
            $image_names = $this->_uploadImages($image_datas, $this->_getBaseName() . '_' . $this->id, '.jpg', $now);
            if ($image_names === false) {
                return false;
            } else {
                if (!empty($image_names)) {
                    foreach ($image_names as $image_name) {
                        $index++;
                        $model = new ProductImage();
                        $model->product_id = $this->id;
                        $model->default = 0;
                        $model->image = $image_name;
                        $model->image_small = $image_name;
                        $model->position = $index;
                        $model->status = ProductImage::STATUS_ACTIVE;
                        $model->time_created = $now;
                        $model->user_created = Yii::$app->user->getId();
                        if ($model->validate()) {
                            $model->save();
                        }
                    }
                }
            }
        }
        return true;
    }

    protected function _uploadImages($datas, $base_name, $extension, $now) {
        $image_names = array();
        $index = 0;
        $error = false;
        $image_default_path = IMAGES_PRODUCT_PATH . 'default' . DS . date('Ymd', $now) . DS;
        $image_small_path = IMAGES_PRODUCT_PATH . 'small' . DS . date('Ymd', $now) . DS;
        foreach ($datas as $data) {
            if (ImageUtils::isDataImageBase64($data)) {
                $index++;
                $image_names[$index] = $base_name . '_' . uniqid() . $extension;
                $upload = ImageUtils::uploadImageBase64($data, $image_default_path, $image_names[$index]);
                if (!$upload) {
                    $error = true;
                    break;
                } else {
                    ImageUtils::resizeImage($image_default_path . $image_names[$index], $image_small_path . $image_names[$index], 211, 253, 100);
                }
            }
        }
        if ($error) {
            if (!empty($image_names)) {
                foreach ($image_names as $image_name) {
                    if (file_exists($image_default_path . $image_name)) {
                        @unlink($image_default_path . $image_name);
                    }
                    if (file_exists($image_small_path . $image_name)) {
                        @unlink($image_small_path . $image_name);
                    }
                }
            }
        }
        return $image_names;
    }

    protected function _getBaseName() {
        $base_name = Strings::_convertToSMS($this->name);
        $base_name = str_replace('  ', ' ', $base_name);
        $base_name = str_replace(' ', '_', $base_name);
        $base_name = str_replace('/', '-', $base_name);
        return $base_name;
    }

    public function getProductAreaProducts() {
        $product_area_products = array();
        if (!empty($this->product_area_ids)) {
            foreach ($this->product_area_ids as $key => $product_area_id) {
                $product_area_products[$key]['product_area_id'] = $product_area_id;
                $product_area_products[$key]['quantity'] = $this->product_area_quantities[$key];
                $product_area_products[$key]['status'] = $this->product_area_status[$key];
            }
        }
        return $product_area_products;
    }

    public function getProductAreas() {
        $product_area_info = Tables::selectAllDataTable("product_area", "status = " . \common\models\db\ProductArea::STATUS_ACTIVE, "id ASC ");
        if ($product_area_info != false) {
            return \common\components\libs\Weblib::getArraySelectBoxForData($product_area_info, "id", "name");
        }
        return array();
    }

}

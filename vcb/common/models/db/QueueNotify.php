<?php

namespace common\models\db;

use common\models\business\SendMailBussiness;
use common\components\utils\Strings;
use common\components\libs\Tables;
use Yii;

/**
 * This is the model class for table "queue_notify".
 *
 * @property integer $id
 * @property integer $type
 * @property Strings $name
 * @property Strings $target
 * @property Strings $content
 * @property Strings $source
 * @property Strings $files
 * @property integer $time_start
 * @property integer $time_end
 * @property integer $time_queue
 * @property integer $number_process
 * @property Strings $result
 * @property integer $status
 * @property integer $time_created
 * @property integer $time_updated
 */
class QueueNotify extends MyActiveRecord
{
    const STATUS_NOT_PROCESS = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_PROCESSED = 3;
    const STATUS_SUCCESS = 4;
    const STATUS_ERROR = 5;

    const TYPE_EMAIL = 1;
    const TYPE_SMS = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'queue_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'target', 'content', 'time_start', 'status'], 'required'],
            [['type', 'time_start', 'time_end', 'time_queue', 'number_process', 'status', 'time_created', 'time_updated'], 'integer'],
            [['target', 'content', 'files', 'result'], 'string'],
            [['name', 'source'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
            'target' => 'Target',
            'content' => 'Content',
            'source' => 'Source',
            'files' => 'Files',
            'time_start' => 'Time Start',
            'time_end' => 'Time End',
            'time_queue' => 'Time Queue',
            'number_process' => 'Number Process',
            'result' => 'Result',
            'status' => 'Status',
            'time_created' => 'Time Created',
            'time_updated' => 'Time Updated',
        ];
    }

    public static function getStatus()
    {
        return array(
            self::STATUS_NOT_PROCESS => 'Chưa xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_PROCESSED => 'Đã xử lý',
            self::STATUS_SUCCESS => 'Hoàn thành',
            self::STATUS_ERROR => 'Lỗi',
        );
    }

    private static function _getTimeQueue($now)
    {
        return $now - 300;
    }

    private static function _getResultValue($result, $response)
    {
        return json_encode($response);
    }

    private static function _updateResult($queue_notify_info, $response)
    {
        $error_message = 'Lỗi không xác định';
        $now = time();
        $model = self::findBySql("SELECT * FROM queue_notify WHERE id = " . $queue_notify_info['id'] . " AND status = " . self::STATUS_PROCESSING)->one();
        if ($model) {
            $model->status = $response['status'];
            $model->number_process = 0;
            $model->result = self::_getResultValue($model->result, $response);
            $model->time_updated = time();

            if ($model->validate() && $model->save()) {
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật queue';
            }
        } else {
            $error_message = 'Queue không hợp lệ';
        }
        return array('error_message' => $error_message);
    }

    private static function _updateProcessing($queue_notify_info)
    {
        $error_message = 'Lỗi không xác định';
        $now = time();
        $model = self::findBySql("SELECT * FROM queue_notify WHERE id = " . $queue_notify_info['id'] . " AND time_start <= $now AND (time_end > $now OR time_end = 0) AND (status = " . self::STATUS_NOT_PROCESS . " OR status = " . self::STATUS_PROCESSED . " OR (status = " . self::STATUS_PROCESSING . " AND time_queue < " . self::_getTimeQueue($now) . ")) ")->one();
        if ($model) {
            $sql = "UPDATE queue_notify SET status = " . self::STATUS_PROCESSING . ", time_queue = $now, time_updated = $now "
                . "WHERE id = " . $queue_notify_info['id'] . " "
                . "AND time_start <= $now "
                . "AND (time_end > $now OR time_end = 0) "
                . "AND (status = " . self::STATUS_NOT_PROCESS . " OR status = " . self::STATUS_PROCESSED . " OR (status = " . self::STATUS_PROCESSING . " AND time_queue < " . self::_getTimeQueue($now) . ")) ";
            $connection = $model->getDb();
            $command = $connection->createCommand($sql);
            $update = $command->execute();
            if ($update) {
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật queue';
            }
        } else {
            $error_message = 'Queue không hợp lệ';
        }
        return array('error_message' => $error_message);
    }

    private static function _updateError($queue_notify_info)
    {
        $error_message = 'Lỗi không xác định';
        $now = time();
        $model = self::findBySql("SELECT * FROM queue_notify WHERE id = " . $queue_notify_info['id'] . " AND status = " . self::STATUS_PROCESSING)->one();
        if ($model) {
            $number_process = $model->number_process + 1;
            if ($number_process > 3) {
                $model->status = self::STATUS_ERROR;
            }
            $model->number_process = $number_process;
            $model->time_updated = $now;
            if ($model->validate() && $model->save()) {
                $error_message = '';
            } else {
                $error_message = 'Có lỗi khi cập nhật queue';
            }
        } else {
            $error_message = 'Queue không hợp lệ';
        }
        return array('error_message' => $error_message);
    }

    public static function getCurrentQueueInfo()
    {
        $now = time();
        $queue_notify_info = Tables::selectOneDataTable("queue_notify", "time_start <= $now AND (time_end > $now OR time_end = 0) AND (status = " . self::STATUS_NOT_PROCESS . " OR status = " . self::STATUS_PROCESSED . " OR (status = " . self::STATUS_PROCESSING . " AND time_queue < " . self::_getTimeQueue($now) . ")) ", "id ASC, time_queue ASC ");
        return $queue_notify_info;
    }

    public static function process($queue_notify_info)
    {
        $error_message = 'Lỗi không xác định';
        $response = null;
        $update = self::_updateProcessing($queue_notify_info);
        if ($update['error_message'] == '') {
            $result = self::_call($queue_notify_info);
            if ($result != false) {
                $response['status'] = 4;
                $error_message = '';
                self::_updateResult($queue_notify_info, $response);
            } else {
                $error_message = 'Timeout hoặc kết quả xử lý không hợp lệ';
                self::_updateError($queue_notify_info);
            }
        } else {
            $error_message = $update['error_message'];
        }
        return array('error_message' => $error_message, 'response' => $response);
    }

    private static function _call($queue_notify_info)
    {
        if ($queue_notify_info['type'] == self::TYPE_EMAIL) {
            if (trim($queue_notify_info['target']) != '') {
                $targets = explode(',', $queue_notify_info['target']);
                $files = array();
                if (trim($queue_notify_info['files']) != '') {
                    $files = explode(',', $queue_notify_info['files']);
                }
                return self::_sendEmail($queue_notify_info['source'], $targets, $queue_notify_info['name'], Strings::strip($queue_notify_info['content']), $files);
            }
        } elseif ($queue_notify_info['type'] == self::TYPE_SMS) {
            $targets = explode(',', $queue_notify_info['target']);
            return self::_sendSMS($targets, $queue_notify_info['content']);
        }
        return false;
    }

    private static function _sendEmail($from_email, $targets, $title, $content, $files)
    {
        $to_email = $targets[0];
        unset($targets[0]);
        SendMailBussiness::sendByContent($to_email, $from_email, $title, $content, $targets);
        return true;
    }

    private static function _sendSMS($to_mobiles, $content)
    {
        return true;
    }

    public static function writeLog($data)
    {
        $file_path = ROOT_PATH . DS . 'data' . DS . 'logs' . DS . 'queue_notify_error' . DS . date('Y-m-d') . '.txt';
        $file = fopen($file_path, 'a');
        if ($file) {
            fwrite($file, '[' . date('H:i:s') . ']' . $data . "\n");
            fclose($file);
            return true;
        }
        return false;
    }
}

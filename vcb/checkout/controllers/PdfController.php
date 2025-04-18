<?php


namespace checkout\controllers;

use checkout\components\MerchantCheckoutController;
use Yii;
use yii\web\Controller;
use common\models\form\PdfDownloadForm;

class PdfController extends MerchantCheckoutController
{
    public $layout = 'version_1_1';

    /** TEST **/
     // https://vietcombank.nganluong.vn/vi/checkout/version_1_0/view-bill?token=MTQyMDUw
     // https://vietcombank.nganluong.vn/vi/checkout/version_1_0/view-bill?token=MTQyMDUz
     // G01.839.108.000-240415-0422, G01.839.108.000-240417-0184
     //
    public function actionDownload()
    {
        $model = new PdfDownloadForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $links     = explode("|", trim($model->links));
            $filenames = explode("|", trim($model->filenames));

            if (count($links) !== count($filenames)) {
                Yii::$app->session->setFlash('error', 'Số lượng links và tên file không khớp.');
                return $this->redirect(['pdf/download']);
            }

            // Tạo thư mục chứa file PDF nếu chưa tồn tại
//            $pdfDir = 'C:/Downloads/all_files/';
            $pdfDir = ROOT_PATH . DS . 'data' . DS . 'document' . DS . 'temp_receipts' . DS;
            if (!file_exists($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }

//            $zipFile = 'C:/Downloads/all_files.zip';
            $zipFile = $pdfDir . 'all_files_' . time() . '.zip';
            // Xóa file ZIP cũ nếu đã tồn tại
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }


            // Tạo file ZIP
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) !== TRUE) {
                Yii::$app->session->setFlash('error', 'Không thể tạo file ZIP.');
                return $this->redirect(['pdf/download']);
            }

            foreach ($links as $index => $link) {
                $link     = trim($link);
                $filename = trim($filenames[$index]);

                if (!empty($link) && !empty($filename)) {
                    $outputFile = $pdfDir . $filename . '.pdf';

                    // Lấy nội dung của trang web
                    $htmlContent = file_get_contents($link);

                    // Lưu nội dung HTML vào file PDF
                    file_put_contents($outputFile, $htmlContent);

                    // Thêm file vào ZIP
                    if (!$zip->addFile($outputFile, $filename . '.pdf')) {
                        Yii::$app->session->setFlash('error', 'Không thể thêm file vào ZIP: ' . $outputFile);
                    }
                }
            }

            $zip->close();

            // Xóa các file PDF sau khi nén vào ZIP (tùy chọn)
            foreach (glob($pdfDir . '*.pdf') as $pdfFile) {
                unlink($pdfFile);
            }

            // Gửi file ZIP cho người dùng
            return Yii::$app->response->sendFile($zipFile)->send();
        }

        return $this->render('download', [
            'model' => $model,
        ]);
    }

    function getContentUsingCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua xác minh SSL (không an toàn)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Bỏ qua xác minh SSL (không an toàn)
        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
        return $output;
    }

}

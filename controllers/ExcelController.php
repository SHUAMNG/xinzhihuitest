<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UploadForm;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\web\Response;
use ZipArchive;

class ExcelController extends Controller
{

    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->excelFile = UploadedFile::getInstance($model, 'excelFile');

            if ($model->validate()) {
                $tempFilePath = Yii::getAlias('@runtime/' . $model->excelFile->baseName . '.' . $model->excelFile->extension);
                $model->excelFile->saveAs($tempFilePath);

                $spreadsheet = IOFactory::load($tempFilePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestDataRow();
                $highestColumn = $worksheet->getHighestDataColumn();

                $translations = [];

                for ($row = 2; $row <= $highestRow; ++$row) {
                    $key = $worksheet->getCell('A' . $row)->getValue();
                    for ($col = 'B'; $col <= $highestColumn; ++$col) {
                        $language = $worksheet->getCell($col . '1')->getValue();
                        $translation = $worksheet->getCell($col . $row)->getValue();
                        $translations[$language][$key] = $translation;
                    }
                }

                $zip = new ZipArchive();
                $zipFileName = Yii::getAlias('@runtime/generated-js-' . time() . '.zip');
                $zip->open($zipFileName, ZipArchive::CREATE);

                foreach ($translations as $language => $translationPairs) {
                    $jsContent = "window.languageMap = " . json_encode($translationPairs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";";
                    $jsFileName = Yii::getAlias('@runtime/' . "{$language}.js");
                    file_put_contents($jsFileName, $jsContent);
                    $zip->addFile($jsFileName, "{$language}.js");
                }

                $zip->close();

                unlink($tempFilePath);

                // Send the ZIP file to the user
                return Yii::$app->response->sendFile($zipFileName, 'translations.zip', [
                    'mimeType' => 'application/zip',
                    'inline' => false,
                ])->on(Response::EVENT_AFTER_SEND, function ($event) {
                    unlink($event->data);
                }, $zipFileName);
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}

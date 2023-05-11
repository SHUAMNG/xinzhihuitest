<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UploadForm */
/* @var $form ActiveForm */
?>

<div class="excel-upload">

    <h1>Upload Excel File</h1>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'excelFile')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Upload', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div><!-- excel-upload -->
<?php $this->registerJs('
    function isValidFile(file) {
        var allowedExtensions = ["xls", "xlsx"];
        var allowedMimeTypes = ["application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];
        var fileExtension = file.name.split(".").pop().toLowerCase();
        var fileType = file.type;
        
        return allowedExtensions.includes(fileExtension) && allowedMimeTypes.includes(fileType);
    }

    $("#uploadform-excelfile").on("change", function () {
        var fileInput = this;
        if (fileInput.files && fileInput.files[0]) {
            if (!isValidFile(fileInput.files[0])) {
                alert("Custom error: Only files with these extensions are allowed: xls, xlsx.");
                fileInput.value = "";
                $("#file-info").html("");
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#file-info").html("<p><strong>File name:</strong> " + fileInput.files[0].name + "</p><p><strong>File size:</strong> " + fileInput.files[0].size + " bytes</p><p><strong>File type:</strong> " + fileInput.files[0].type + "</p>");
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });
    
    
'); ?>

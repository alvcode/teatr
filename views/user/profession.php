<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Службы и должности';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($addCategory, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm'])
                        ->label("Служба <span class='text-danger'>*</span>") ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>
            </div>
            
            <?php 
            echo "<pre>";
            print_r($prof);
            ?>
            
        </div>
    </div>
</div>


<script>
window.onload = function () {
    
    var csrfParam = $('meta[name="csrf-param"]').attr("content");
    var csrfToken = $('meta[name="csrf-token"]').attr("content");

    

}
</script>


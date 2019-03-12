<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Залы и мероприятия';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            
            <?= $this->render('../templates/_flash') ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <h4><b>Добавить мероприятие</b></h4>
                    
                </div>
                <div class="col-lg-6">
                    <h4><b>Добавить зал</b></h4>
                    
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($roomModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm'])
                        ->label("Название зала <span class='text-danger'>*</span>") ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                    
                    <div class="mrg-top45">
                        <ul class="list-group proff-list">
                            <?php foreach ($rooms as $key => $value): ?>
                            <li data-room="<?= $value['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center"><?= $value['name'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php /* echo "<pre>"; print_r($rooms); */ ?>
                    </div>
                    
                </div>
            </div>

            
        </div>
    </div>

</div>

<script>
window.onload = function () {
    
var csrfParam = $('meta[name="csrf-param"]').attr("content");
var csrfToken = $('meta[name="csrf-token"]').attr("content");

    
}
</script>
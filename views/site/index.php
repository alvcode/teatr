<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'CRM Театриум';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
    <div class="row">
        <div class="col-lg-8 site-login-form">
            <h1><?= Html::encode($this->title) ?></h1>

            <p>Пожалуйста, введите логин и пароль для входа в программу:</p>

            <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'email', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="form-group">
                    <div class="col-lg-11">
                        <?= Html::submitButton('Войти', ['class' => 'btn btn-sm btn-info', 'name' => 'login-button']) ?>
                    </div>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

<!--</div>-->


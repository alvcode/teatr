<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Новый пароль';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
    <div class="row">
        <div class="col-lg-8 site-login-form">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="alert alert-danger">
                <p>Пожалуйста, придумайте себе пароль для входа в программу!</p>
                <p>Пароль должен содержать не менее 8 символов</p>
            </div>
            
            <div class="form-group">
                <label class="control-label" for="new-password">Новый пароль</label>
                <input class="form-control" id="new-password" type="password">
            </div>
            
            <div class="form-group">
                <label class="control-label" for="repeat-password">Повторите пароль</label>
                <input class="form-control" id="repeat-password" type="password">
            </div>
            
            <div class="form-group">
                <div class="btn btn-sm btn-info" id="submit-change-password">Сохранить</div>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'edit-password-form']); ?>

                <?= $form->field($user, 'password', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->hiddenInput()->label('') ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

<!--</div>-->

<script>
    window.onload = function () {
        
        $('#submit-change-password').click(function(){
            var passwordOne = $('#new-password').val();
            var passwordTwo = $('#repeat-password').val();
//            alert(passwordOne);
            if(passwordOne !== passwordTwo){
                showNotifications('Пароли не совпадают', 4000, NOTIF_RED);
                return false;
            }
            if(passwordOne == '' || passwordTwo == ''){
                showNotifications('Вы ничего не ввели', 4000, NOTIF_RED);
                return false;
            }
            if(passwordOne.length < 8 || passwordTwo.length < 8){
                showNotifications('Пароль должен содержать не менее 8 символов', 4000, NOTIF_RED);
                return false;
            }
            
            $('#user-password').val(passwordOne);
            $('#edit-password-form').submit();
        });
        
    }
</script>

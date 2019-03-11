<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Страница пользователя';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                          <li class="breadcrumb-item text-info"><a href="/user/">Сотрудники</a></li>
                          <li class="breadcrumb-item active" aria-current="page"><?= $user->name ?> <?= $user->surname ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
            
            <?= $this->render('templates/_flash') ?>
            
            <?php
                $form = ActiveForm::begin([
                            'options' => ['id' => 'new-user-form']
                ]);
                ?>

                <?= $form->field($user, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

                <?= $form->field($user, 'surname', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

                <?= $form->field($user, 'email', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

                <?=
                    $form->field($user, 'number', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'p--number form-control', 'inputmode' => 'numeric', 'pattern' => '\+7?[\(][0-9]{3}[\)]{0,1}\s?\d{3}[-]{0,1}\d{4}'])
                ?>

                <?= $form->field($user, 'password', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput()
                        ->label("Пароль (если требуется сменить)") ?>

                <?=
                $form->field($user, 'user_role')->dropDownList(\yii\helpers\ArrayHelper::map($roleList, 'name', 'description'), [
                    'prompt' => 'Выберите роль',
                    'options' => [
                        $roleUser ? $roleUser->name : "0" => ['Selected' => true]
                    ]

                ])->label("Роль")
                ?>
            
                <?=
                $form->field($profModel, 'prof_id', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->dropDownList(\yii\helpers\ArrayHelper::map($categories, 'id', 'name'), [
                    'prompt' => 'Должность',
                ])->label("Должность")
                ?>
            
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                </div>

            <?php ActiveForm::end(); ?>

            <?php 
//            echo "<pre>";
//            print_r($user);
            
            ?>
            
        </div>
    </div>
</div>


<script>
    window.onload = function () {

    // Маска ввода для номера телефона
    $(".p--number").mask("+7(999) 999-9999", {clearIfNotMatch: true});
    $(".p--number").click(function () {
        if ($(this).val().length > 4) {
            return false;
        } else {
            $(this).val("+7");
        }
    });

    }
</script>


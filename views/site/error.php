<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>

<div class="panel-background">
    <br><br>
    <div class="container">
        <div class="row">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="row faq--row">
            <div class="col-12">
                <div class="alert alert-danger">
                    <?= nl2br(Html::encode($message)) ?>
                </div>
                <p>
                    Вышеуказанная ошибка произошла, когда веб-сервер обрабатывал ваш запрос.
                </p>
                <p>
                    Пожалуйста, напишите разработчику на <span class="text-danger">alvcode@ya.ru</span>, если считаете, что это ошибка сервера. Спасибо.
                </p>
                <p>
                    <a href="/panel/index" class="btn btn-sm btn-success">Вернуться на главную</a>
                </p>
            </div>
        </div>
    </div>
    <br><br>
</div>

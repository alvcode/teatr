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
        <div class="row text-white">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="row text-white">
            <div class="col-12">
                <div class="alert alert-danger">
                    <?= nl2br(Html::encode($message)) ?>
                </div>
                <p>
                    Вышеуказанная ошибка произошла, когда веб-сервер обрабатывал ваш запрос.
                </p>
                <p>
                    <a href="/panel/index" class="btn btn-sm btn-success">Вернуться на главную</a>
                </p>
            </div>
        </div>
    </div>
    <br><br>
</div>

<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Ошибки табеля';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    // echo "<pre>";
    // var_dump($categories);
?>
<!--<div class="site-login">-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            
            <?= $this->render('../templates/_flash') ?>
            
            <div>
                <?php if($t_to_errors): ?>
                    <h4>Мероприятия, в которых не заполнено время окончания:</h4>
                    
                    <?php foreach ($t_to_errors as $key => $value): ?>
                        <div class="alert alert-info" role="alert">
                            <?= $value['date'] ?>, <?= $value['time'] ?> - (<?= $value['type'] ?>) <?= $value['name'] ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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


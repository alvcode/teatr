<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Статистика и отчеты';
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
            
            <div>
                <h4>Табель</h4>
                <div class="row">
                    <div class="col-3">
                        <select id="timesheet-prof-select" class="form-control-sm form-control ml-1">
                            <?php foreach ($categories as $key => $value): ?>
                                <option value="<?= $value['id'] ?>" <?= (isset($sort['act']) && $sort['act'] == 'sortProf' && $sort['val'] == $value['id']) ? "selected" : "" ?>><?= $value['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2">
                        <input class="form-control-sm form-control" id="timesheet-time-from" placeholder="Дата от...">
                    </div>
                    <div class="col-2">
                        <input class="form-control-sm form-control" id="timesheet-time-to" placeholder="Дата до...">
                    </div>
                    <div class="col-2">
                        <div id="timesheet-submit" class="btn btn-sm btn-info">Выгрузить в Excel</div>
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
        
        $('#timesheet-time-from').datepicker({
            dateFormat:'dd.mm.yy',
            monthNames : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            dayNamesMin : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        });
        $('#timesheet-time-to').datepicker({
            dateFormat:'dd.mm.yy',
            monthNames : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            dayNamesMin : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        });
        
        $('#timesheet-submit').click(function(){
            var dateFrom = $('#timesheet-time-from').val();
            var dateTo = $('#timesheet-time-to').val();
            if(dateFrom == ''){
                showNotifications('Не заполнена дата от...', 2000, NOTIF_RED);
                return false;
            }
            if(dateTo == ''){
                showNotifications('Не заполнена дата до...', 2000, NOTIF_RED);
                return false;
            }
//           alert($('#timesheet-prof-select').val()); 
        });
        
        
    }
</script>


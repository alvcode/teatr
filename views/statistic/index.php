<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Статистика и отчеты';
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
                <h4>Табель</h4>
                <div class="row">
                    <div class="col-4">
                        <select id="timesheet-prof-select" class="form-control-sm form-control ml-1">
                            <?php foreach ($professions as $key => $value): ?>
                                <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-group mrg-top15 ml-1">
                            <input id="timesheet-ignore-error" type="checkbox">
                            <label class="form-check-label" for="timesheet-ignore-error">Не показывать ошибку, если в мероприятии отсутствует "Время до"</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <select id="timesheet-prof-cat-select" class="form-control-sm form-control ml-1" disabled="true">
                            <?php foreach ($categories as $key => $value): ?>
                                <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4 noselect">
                        <div class="form-group">
                            <i class="fas fa-toggle-off toggle-icon"></i>
                            <span class="stat--toggle-timesheet-text">Выбор профессии</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2">
                        <input class="form-control-sm form-control" id="timesheet-time-from" placeholder="Дата от...">
                    </div>
                    <div class="col-2">
                        <input class="form-control-sm form-control" id="timesheet-time-to" placeholder="Дата до...">
                    </div>
                    <div class="col-2">
                        <a href="#" id="timesheet-submit" class="btn btn-sm btn-info" target="_blank">Выгрузить в Excel</a>
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
            dateFormat:'dd-mm-yy',
            monthNames : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            dayNamesMin : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
            firstDay: 1
        });
        $('#timesheet-time-to').datepicker({
            dateFormat:'dd-mm-yy',
            monthNames : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            dayNamesMin : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
            firstDay: 1
        });
        
        var timesheetMode = 'prof';
        $('#timesheet-submit').click(function(e){
//            e.preventDefault(); 
            var dateFrom = $('#timesheet-time-from').val();
            var dateTo = $('#timesheet-time-to').val();
            if(timesheetMode == 'prof'){
                var profId = $('#timesheet-prof-select').val();
            }else if(timesheetMode == 'profCat'){
                var profId = $('#timesheet-prof-cat-select').val();
            }
            
            if($('#timesheet-ignore-error').prop('checked')){
                var error = 1;
            }else{
                var error = 0;
            }
//            alert(error);
//            return false;
            if(dateFrom == ''){
                showNotifications('Не заполнена дата от...', 2000, NOTIF_RED);
                return false;
            }
            if(dateTo == ''){
                showNotifications('Не заполнена дата до...', 2000, NOTIF_RED);
                return false;
            }
            $('#timesheet-submit').attr('href', '/statistic/timesheet?from=' +dateFrom +'&to=' +dateTo +'&'+ timesheetMode +'=' +profId +"&time_error=" +error);
        });
        
        $('.toggle-icon').click(function(){
            if(this.classList.contains('fa-toggle-off')){
                this.classList.remove('fa-toggle-off');
                this.classList.add('fa-toggle-on');
                document.getElementById('timesheet-prof-cat-select').removeAttribute('disabled');
                document.getElementById('timesheet-prof-select').setAttribute('disabled', true);
                $('.stat--toggle-timesheet-text').html('Выбор службы');
                timesheetMode = 'profCat';
            }else{
                this.classList.remove('fa-toggle-on');
                this.classList.add('fa-toggle-off');
                document.getElementById('timesheet-prof-select').removeAttribute('disabled');
                document.getElementById('timesheet-prof-cat-select').setAttribute('disabled', true);
                $('.stat--toggle-timesheet-text').html('Выбор профессии');
                timesheetMode = 'prof';
            }
        })
        
        
    }
</script>


<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Teatrium';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            <div>
                <div class="alert alert-info" role="alert">
                    <h6>С возвращением, <?= Yii::$app->user->identity->name ?>!</h6>
                </div>
            </div>
            <div>
                Узнай, как разместить иконку этой страницы у себя в телефоне на главном экране: 
                <span class="badge badge-pill badge-warning cursor-pointer" id="instr-android">Android</span> / 
                <span class="badge badge-pill badge-warning cursor-pointer" id="instr-iphone">iPhone</span>
            </div>
            
            <?= $this->render('../templates/_flash') ?>
            
            <div class="row mrg-top15">
                <div class="col-sm-6">
                    <div class="card bg-light mb-3">
                    <div class="card-header"><h5>Ваши ближайшие мероприятия на 10 дней</h5></div>
                    <div class="card-body">
                        <p class="card-text">
                        <table class="table table-striped">
                        <tbody>
                            <?php if($nearSchedule): ?>
                            <?php foreach($nearSchedule as $key => $val): ?>
                                    <tr>
                                        <th>
                                            <span class="badge badge-pill badge-info"><?= $val['date'] ?></span>
                                            <span class="badge badge-pill badge-info">
                                                <?= $val['time_from'] ?>
                                                <?php if($val['time_to']): ?>
                                                    - <?= $val['time_to'] ?>
                                                <?php endif; ?>
                                            </span>
                                            <?= $val['eventType']['name'] ?>
                                            <?php if(isset($val['event'])): ?>
                                                (<?= $val['event']['name'] ?>)
                                            <?php endif; ?>
                                        </th>
                                    </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-danger">Вы не стоите в расписании ближайшие 10 дней</span>
                            <?php endif; ?>
                        </tbody>
                        </table>
                        </p>
                    </div>
                    </div>
                </div>
                <div class="col-sm-6">
                <div class="card bg-light mb-3">
                    <div class="card-header"><h5>Учет отработанного времени</h5></div>
                    <div class="card-body">
                        <p class="card-text">
                            <div class="form-group">
                                <input class="form-control-sm form-control" id="timesheet-time-from" placeholder="Дата от...">
                            </div>
                            <div class="form-group">
                                <input class="form-control-sm form-control" id="timesheet-time-to" placeholder="Дата до...">
                            </div>
                            <div class="form-group">
                                <div id="load-timesheet-stat" class="btn btn-sm btn-success">Загрузить данные</div>
                            </div>
                        <table class="table table-striped">
                        <tbody>
                            
                        </tbody>
                        </table>
                        </p>
                    </div>
                    </div>
                </div>
            </div>
            

            
        </div>
    </div>

</div>

<!-- Modal android --> 
<div class="modal fade" id="androidInstrModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Как добавить страницу на главный экран Android-устройства</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal iOS --> 
<div class="modal fade" id="iosInstrModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Как добавить страницу на главный экран iOS-устройства</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Закрыть</button>
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
        });
        $('#timesheet-time-to').datepicker({
            dateFormat:'dd-mm-yy',
            monthNames : ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            dayNamesMin : ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        });

        $('#instr-android').click(function(){
            $('#androidInstrModal').modal('show');
        });
        $('#instr-iphone').click(function(){
            $('#iosInstrModal').modal('show');
        });

        $('#load-timesheet-stat').click(function(){
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
            goPreloader();
            var data = {
                trigger: 'load-timesheet-stat',
                from: dateFrom,
                to: dateTo
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/panel/index',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if(result.result == 'ok'){
                        console.log(result.response);
                    }else if(result.result == 'error'){
                        showNotifications(result.response, 6000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
       
        
        
    }
</script>


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
                    <h4><b>Добавить мероприятия</b></h4>
                    
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($eventTypeModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm'])
                        ->label("Новый тип мероприятия <span class='text-danger'>*</span>") ?>
                    
                    <?= $form->field($eventTypeModel, 'timesheet_hour')->checkbox() ?>
                    
                    <?= $form->field($eventTypeModel, 'timesheet_count')->checkbox() ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                    <br>
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($eventsModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm'])
                        ->label("Новый спектакль <span class='text-danger'>*</span>") ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                    
                    <?php /* echo "<pre>"; print_r($eventType); */ ?>
                    <div class="mrg-top45">
                        <ul class="list-group mrg-top30">
                            <li class="list-group-item">
                                <b>Типы мероприятий</b>
                            </li>
                            <li class="list-group-item">
                                <ul class="list-group proff-list">
                                    <?php foreach ($eventType as $key => $value): ?>
                                        <li data-timesheet-count="<?= $value['timesheet_count'] ?>" data-timesheet-hour="<?= $value['timesheet_hour'] ?>" data-eventtype="<?= $value['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center event-type-li">
                                            <div class="proff-name">
                                                <?= $value['name'] ?>
                                                <span class="small">
                                                    <?= $value['timesheet_hour']?"(табель по часам)":"" ?>
                                                    <?= $value['timesheet_count']?"(табель по выходам)":"" ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="badge badge-info badge-pill edit-event-type cursor-pointer">Ред.</span>
                                                <span class="badge badge-danger badge-pill delete-event-type cursor-pointer">Удалить</span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mrg-top45">
                        <ul class="list-group mrg-top30">
                            <li class="list-group-item">
                                <b>Спектакли</b>
                            </li>
                            <li class="list-group-item">
                                <ul class="list-group proff-list">
                                    <?php foreach ($events as $key => $value): ?>
                                        <li data-event="<?= $value['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center event-li">
                                            <div class="proff-name"><?= $value['name'] ?></div>
                                            <div>
                                                <span class="badge badge-danger badge-pill delete-event cursor-pointer">Удалить</span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
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
                            <li data-room="<?= $value['id'] ?>" class="list-group-item room-item d-flex justify-content-between align-items-center">
                                <div class="room-name"><?= $value['name'] ?></div>
                                <div>
                                    <span class="badge badge-info badge-pill edit-room-name cursor-pointer">Изменить</span>
                                    <span class="badge badge-danger badge-pill delete-room cursor-pointer">Удалить</span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php /* echo "<pre>"; print_r($rooms); */ ?>
                    </div>
                    
                </div>
            </div>

            
        </div>
    </div>

</div>

<!-- Modal delete room -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Помещение будет удалено из системы
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-room-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete event type -->
<div class="modal fade" id="deleteEventTypeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Этот тип мероприятия будет удален
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-event-type-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete event -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Этот спектакль будет удален
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-event-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal timesheet event -->
<div class="modal fade" id="timesheetEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Изменить настройку расчета табеля</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4>Тип мероприятия: <span>Спектакль</span></h4>
                <div class="form-group">
                    <div class="checkbox">
                    <label>
                        <input type="checkbox">
                        Участвует в расчете табелей по часам
                    </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="timesheet-event-submit" type="button" class="btn btn-sm btn-success">Применить</button>
            </div>
        </div>
    </div>
</div>

<script>
window.onload = function () {
    
var csrfParam = $('meta[name="csrf-param"]').attr("content");
var csrfToken = $('meta[name="csrf-token"]').attr("content");

var actionRoom = false;
$('.edit-room-name').click(function(){
    actionRoom = this.parentNode.parentNode.dataset.room;
    var roomName = this.parentNode.parentNode.getElementsByClassName('room-name')[0].innerHTML;
    var roomNameObj = this.parentNode.parentNode.getElementsByClassName('room-name')[0];
    var createInput = document.createElement('input');
    createInput.value = roomName;
    var createOk = document.createElement('div');
    createOk.className = 'btn btn-sm btn-success edit-room-submit';
    createOk.innerHTML = 'ok';
    roomNameObj.innerHTML = '';
    roomNameObj.append(createInput);
    roomNameObj.append(createOk);
});

$('body').on('click', '.edit-room-submit', function(){
    goPreloader();
    var roomName = this.parentNode.getElementsByTagName('input')[0].value;
    var self = this;
    var data = {
        trigger: 'rename-room',
        roomId: actionRoom,
        roomName: roomName,
    };
    data[csrfParam] = csrfToken;
    $.ajax({
        type: "POST",
        url: '/panel/room-event',
        data: data,
        success: function(data){
           if(data == 1){
               self.parentNode.innerHTML = roomName;
               showNotifications("Зал успешно переименован", 3000, NOTIF_GREEN);
            }else if(data == 0){
               showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
            }
            stopPreloader();
        },
        error: function () {
           showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
           stopPreloader();
       }
    });
});

$('.delete-room').click(function(){
    actionRoom = this.parentNode.parentNode.dataset.room;
    $('#deleteRoomModal').modal('show');
});

$('#delete-room-submit').click(function(){
    goPreloader();
    var data = {
        trigger: 'remove-room',
        roomId: actionRoom,
    };
    data[csrfParam] = csrfToken;
    $.ajax({
        type: "POST",
        url: '/panel/room-event',
        data: data,
        success: function(data){
           if(data == 1){
              $('.room-item[data-room='+ actionRoom +']').remove();
              $('#deleteRoomModal').modal('hide');
               showNotifications("Зал успешно удален", 3000, NOTIF_GREEN);
            }else if(data == 0){
               showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
            }
            stopPreloader();
        },
        error: function () {
           showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
           stopPreloader();
       }
    });
});

var actionEventType = false;
$('.delete-event-type').click(function(){
   actionEventType = this.parentNode.parentNode.dataset.eventtype;
   $('#deleteEventTypeModal').modal('show');
});

$('#delete-event-type-submit').click(function(){
    goPreloader();
    var data = {
        trigger: 'remove-event-type',
        eventTypeId: actionEventType,
    };
    data[csrfParam] = csrfToken;
    $.ajax({
        type: "POST",
        url: '/panel/room-event',
        data: data,
        success: function(data){
           if(data == 1){
              $('.event-type-li[data-eventtype='+ actionEventType +']').remove();
              $('#deleteEventTypeModal').modal('hide');
               showNotifications("Тип мероприятия успешно удален", 3000, NOTIF_GREEN);
            }else if(data == 0){
               showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
            }
            stopPreloader();
        },
        error: function () {
           showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
           stopPreloader();
       }
    });
});

var actionEvent = false;
$('.delete-event').click(function(){
    actionEvent = this.parentNode.parentNode.dataset.event;
   $('#deleteEventModal').modal('show');
});

$('#delete-event-submit').click(function(){
    goPreloader();
    var data = {
        trigger: 'remove-event',
        eventId: actionEvent,
    };
    data[csrfParam] = csrfToken;
    $.ajax({
        type: "POST",
        url: '/panel/room-event',
        data: data,
        success: function(data){
           if(data == 1){
              $('.event-li[data-event='+ actionEvent +']').remove();
              $('#deleteEventModal').modal('hide');
               showNotifications("Спектакль успешно удален", 3000, NOTIF_GREEN);
            }else if(data == 0){
               showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
            }
            stopPreloader();
        },
        error: function () {
           showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
           stopPreloader();
       }
    });
});

$('.edit-event-type').click(function(){
    $('#timesheetEventModal').modal('show');
});

    
}
</script>
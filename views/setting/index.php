<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Настройки приложения';
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
            
            <div class="card border-info">
                <div class="card-header"><h5 class="card-title">Какие залы должны отображаться в сводном расписании</h5></div>
                <div class="card-body text-info">
                    <div id="schedule-one-rooms-container">
                        <?php if ($scheduleOneRooms && $rooms): ?>
                            <?php foreach ($rooms as $key => $value): ?>
                                <?php if (in_array($value['id'], $scheduleOneRooms)): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1 schedule-one-item">
                                        <?= $value['name'] ?> <span class="badge badge-danger delete-schedule-one-room"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-schedule-one-room" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Какие типы мероприятий отображаются в расписании актеров?</h5></div>
                <div class="card-body text-info">
                    <div id="schedule-two-event-type-container">
                        <?php if ($scheduleTwoEventType && $eventType): ?>
                            <?php foreach ($eventType as $key => $value): ?>
                                <?php if (in_array($value['id'], $scheduleTwoEventType)): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1 schedule-two-event-type-item">
                                        <?= $value['name'] ?> <span class="badge badge-danger delete-schedule-two-event-type"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-schedule-two-type-event" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Какие типы мероприятий относятся к спектаклю?</h5></div>
                <div class="card-body text-info">
                    <div id="spectacle-event-container">
                        <?php if ($spectacleEvent && $eventType): ?>
                            <?php foreach ($eventType as $key => $value): ?>
                                <?php if (in_array($value['id'], $spectacleEvent)): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1 spectacle-event-type-item">
                                        <?= $value['name'] ?> <span class="badge badge-danger delete-spectacle-event"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-spectacle-event" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Какие службы относятся к актерам?</h5></div>
                <div class="card-body text-info">
                    <div id="actors-prof-cat-container">
                        <?php if ($actorsProfCat && $actorsCat): ?>
                            <?php foreach ($actorsCat as $key => $value): ?>
                                <?php if (in_array($value['id'], $actorsProfCat)): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1 actor-cat-item">
                                        <?= $value['name'] ?> <span class="badge badge-danger delete-actor-cat"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-actors-cat-event" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>

        </div>
    </div>
    <br>
</div>

<!-- Modal schedule one rooms -->
<div class="modal fade" id="scheduleOneRoomModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Добавить зал</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="schedule-one-rooms-select">
                    <?php foreach ($rooms as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-schedule-one-room-submit" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal schedule one room delete -->
<div class="modal fade" id="scheduleOneRoomDeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удалить зал?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Зал будет удален из конфигурации. Все добавленные мероприятия для него
                больше не отобразятся в сводном расписании. Вы сможете это вернуть, добавив
                его заново
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-schedule-one-room-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal schedule two event type -->
<div class="modal fade" id="scheduleTwoEventTypeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Добавить тип мероприятия</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="schedule-two-event-type-select">
                    <?php foreach ($eventType as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-schedule-two-event-type-submit" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal schedule two event type delete -->
<div class="modal fade" id="scheduleTwoEventTypeDeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удалить тип мероприятия?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Тип мероприятия будет удален из конфигурации. Он больше не отобразится
                в расписании актеров. Вы сможете это вернуть, добавив его заново
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-schedule-two-event-type-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal spectacle event -->
<div class="modal fade" id="spectacleEventTypeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Добавить тип мероприятия</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="spectacle-event-select">
                    <?php foreach ($eventType as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-spectacle-event-submit" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete spectacle event -->
<div class="modal fade" id="deleteSpectacleEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удалить службу?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Служба будет удалена из конфигурации. Это может сильно повлиять на работу приложения,
                если вы не поставите замену удаляемому мероприятию
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-spectacle-event-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal actors cat -->
<div class="modal fade" id="actorsCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Добавить службу относящуюся к актерам</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="actors-cat-select">
                    <?php foreach ($actorsCat as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-actors-cat-submit" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal actors cat delete -->
<div class="modal fade" id="actorsCatDeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удалить службу?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Служба будет удалена из конфигурации. Сотрудники, которые относились к данной службе
                больше не будут актерами в системе. Вы сможете это вернуть, добавив ее заново
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-actor-cat-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        $('#add-schedule-one-room').click(function () {
            $('#scheduleOneRoomModal').modal('show');
        });

        $('#add-schedule-one-room-submit').click(function () {
            var room = $('#schedule-one-rooms-select').val();
            var roomName = $('#schedule-one-rooms-select').find(':selected').html();
            var roomsArr = {};
            var oldRoomsObj = document.getElementsByClassName('schedule-one-item');
            var z = 0;
            for (var i = 0; i < oldRoomsObj.length; i++) {
                if (room == oldRoomsObj[i].dataset.id) {
                    showNotifications('Этот зал уже добавлен в список', 3000, NOTIF_RED);
                    return false;
                }
                roomsArr[z] = oldRoomsObj[i].dataset.id;
                z++;
            }
            roomsArr[z] = room;
            goPreloader();
            var data = {
                trigger: 'add-simple-config',
                configName: 'schedule_one_rooms',
                configValue: roomsArr,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        addConfigButton(room, 'schedule-one-item', roomName, 'delete-schedule-one-room', 'schedule-one-rooms-container');
                        $('#scheduleOneRoomModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });

        var scheduleOneRoomDeleted = false;
        $('body').on('click', '.delete-schedule-one-room', function () {
            $('#scheduleOneRoomDeleteModal').modal('show');
            scheduleOneRoomDeleted = this.parentNode.dataset.id;
        });

        $('#delete-schedule-one-room-submit').click(function () {
            goPreloader();
            var data = {
                trigger: 'delete-simple-config',
                configName: 'schedule_one_rooms',
                configValue: scheduleOneRoomDeleted,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        deleteConfigButton('schedule-one-item', scheduleOneRoomDeleted);
                        $('#scheduleOneRoomDeleteModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        
        $('#add-schedule-two-type-event').click(function(){
            $('#scheduleTwoEventTypeModal').modal('show');
        });
        
        $('#add-schedule-two-event-type-submit').click(function(){
            var eventType = $('#schedule-two-event-type-select').val();
            var eventTypeName = $('#schedule-two-event-type-select').find(':selected').html();
            var eventTypeArr = {};
            var oldEventType = document.getElementsByClassName('schedule-two-event-type-item');
            var z = 0;
            for (var i = 0; i < oldEventType.length; i++) {
                if (eventType == oldEventType[i].dataset.id) {
                    showNotifications('Этот тип мероприятия уже добавлен в список', 3000, NOTIF_RED);
                    return false;
                }
                eventTypeArr[z] = oldEventType[i].dataset.id;
                z++;
            }
            eventTypeArr[z] = eventType;
            goPreloader();
            var data = {
                trigger: 'add-simple-config',
                configName: 'schedule_two_event_type',
                configValue: eventTypeArr,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        addConfigButton(eventType, 'schedule-two-event-type-item', eventTypeName, 'delete-schedule-two-event-type', 'schedule-two-event-type-container');
                        $('#scheduleTwoEventTypeModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        var scheduleTwoEventTypeDeleted = false;
        $('body').on('click', '.delete-schedule-two-event-type', function () {
            $('#scheduleTwoEventTypeDeleteModal').modal('show');
            scheduleTwoEventTypeDeleted = this.parentNode.dataset.id;
        });
        
        $('#delete-schedule-two-event-type-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'delete-simple-config',
                configName: 'schedule_two_event_type',
                configValue: scheduleTwoEventTypeDeleted,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        deleteConfigButton('schedule-two-event-type-item', scheduleTwoEventTypeDeleted);
                        $('#scheduleTwoEventTypeDeleteModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        $('#add-spectacle-event').click(function(){
            $('#spectacleEventTypeModal').modal('show');
        });
        
        $('#add-spectacle-event-submit').click(function(){
            var eventType = $('#spectacle-event-select').val();
            var eventTypeName = $('#spectacle-event-select').find(':selected').html();
            var eventTypeArr = {};
            var oldEventType = document.getElementsByClassName('spectacle-event-type-item');
            var z = 0;
            for (var i = 0; i < oldEventType.length; i++) {
                if (eventType == oldEventType[i].dataset.id) {
                    showNotifications('Этот тип мероприятия уже добавлен в список', 3000, NOTIF_RED);
                    return false;
                }
                eventTypeArr[z] = oldEventType[i].dataset.id;
                z++;
            }
            eventTypeArr[z] = eventType;
            goPreloader();
            var data = {
                trigger: 'add-simple-config',
                configName: 'spectacle_event',
                configValue: eventTypeArr,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        addConfigButton(eventType, 'spectacle-event-type-item', eventTypeName, 'delete-spectacle-event', 'spectacle-event-container');
                        $('#spectacleEventTypeModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        var spectacleEventDeleted = false;
        $('body').on('click', '.delete-spectacle-event', function () {
            $('#deleteSpectacleEventModal').modal('show');
            spectacleEventDeleted = this.parentNode.dataset.id;
        });
        
        $('#delete-spectacle-event-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'delete-simple-config',
                configName: 'spectacle_event',
                configValue: spectacleEventDeleted,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        deleteConfigButton('spectacle-event-type-item', spectacleEventDeleted);
                        $('#deleteSpectacleEventModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        
        $('#add-actors-cat-event').click(function(){
            $('#actorsCatModal').modal('show');
        });
        
        $('#add-actors-cat-submit').click(function(){
            var actorCat = $('#actors-cat-select').val();
            var actorCatName = $('#actors-cat-select').find(':selected').html();
            var actorCatArr = {};
            var oldActorCat = document.getElementsByClassName('actor-cat-item');
            var z = 0;
            for (var i = 0; i < oldActorCat.length; i++) {
                if (actorCat == oldActorCat[i].dataset.id) {
                    showNotifications('Эта служба уже добавлена в список', 3000, NOTIF_RED);
                    return false;
                }
                actorCatArr[z] = oldActorCat[i].dataset.id;
                z++;
            }
            actorCatArr[z] = actorCat;
            goPreloader();
            var data = {
                trigger: 'add-simple-config',
                configName: 'actors_prof_cat',
                configValue: actorCatArr,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        addConfigButton(actorCat, 'actor-cat-item', actorCatName, 'delete-actor-cat', 'actors-prof-cat-container');
                        $('#actorsCatModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        var actorCatDeleted = false;
        $('body').on('click', '.delete-actor-cat', function () {
            $('#actorsCatDeleteModal').modal('show');
            actorCatDeleted = this.parentNode.dataset.id;
        });
        
        $('#delete-actor-cat-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'delete-simple-config',
                configName: 'actors_prof_cat',
                configValue: actorCatDeleted,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        deleteConfigButton('actor-cat-item', actorCatDeleted);
                        $('#actorsCatDeleteModal').modal('hide');
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        
        
        // Добавляем кнопку конфигурации
        function addConfigButton(dataId, className, buttonHtml, deleteClass, container){
            var createButton = document.createElement('button');
            createButton.dataset.id = dataId;
            createButton.setAttribute('type', 'button');
            createButton.className = "btn btn-sm btn-info "+ className +" mrg-left5";
            createButton.innerHTML = buttonHtml +" <span class='badge badge-danger "+ deleteClass +"'><i class='fas fa-times'></i></span> ";

            document.getElementById(container).append(createButton);
        }
        
        // Удаляем кнопку конфигурации по dataset.id
        function deleteConfigButton(itemsClass, id){
            var items = document.getElementsByClassName(itemsClass);
            for (var i = 0; i < items.length; i++) {
                if (id == items[i].dataset.id) {
                    items[i].remove();
                }
            }
        }


    }
</script>

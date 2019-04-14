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
                        <?php if ($scheduleOneRooms && $scheduleOneRooms['value'] && $rooms): ?>
                            <?php foreach ($rooms as $key => $value): ?>
                                <?php if (in_array($value['id'], $scheduleOneRooms['value'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info schedule-one-item">
                                        <?= $value['name'] ?> <span class="badge badge-danger delete-schedule-one-room"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-schedule-one-room" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>

        </div>
    </div>

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
                trigger: 'schedule-one-add-room',
                room: roomsArr,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        var createButton = document.createElement('button');
                        createButton.dataset.id = room;
                        createButton.setAttribute('type', 'button');
                        createButton.className = "btn btn-sm btn-info schedule-one-item mrg-left5";
                        createButton.innerHTML = roomName +" <span class='badge badge-danger delete-schedule-one-room'><i class='fas fa-times'></i></span> ";
                        
                        document.getElementById('schedule-one-rooms-container').append(createButton);
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
                trigger: 'schedule-one-delete-room',
                room: scheduleOneRoomDeleted,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        var oldRoomsObj = document.getElementsByClassName('schedule-one-item');
                        for (var i = 0; i < oldRoomsObj.length; i++) {
                            if (scheduleOneRoomDeleted == oldRoomsObj[i].dataset.id) {
                                oldRoomsObj[i].remove();
                            }
                        }
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


    }
</script>

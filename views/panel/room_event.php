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
                    <div class="card border-info">
                        <div class="card-header"><h5 class="card-title">Добавить тип мероприятия</h5></div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin() ?>
                            <?=
                                    $form->field($eventTypeModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                    ->textInput(['class' => 'form-control form-control-sm'])
                                    ->label("Новый тип мероприятия <span class='text-danger'>*</span>")
                            ?>

                           <div class="form-group">
                                <div class="col-lg-offset-1 col-lg-11">
                                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                                </div>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                    <br>
                    <div class="card border-info">
                        <div class="card-header"><h5 class="card-title">Добавить спектакль (<span class="text-danger">! кавычки в названии не ставить</span>)</h5></div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin() ?>

                            <?=
                                    $form->field($eventsModel, 'category_id', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                    ->dropDownList(
                                            yii\helpers\ArrayHelper::map($eventCategories, 'id', 'name'), ['class' => 'form-control form-control-sm']
                                    )
                            ?>

                            <?=
                                    $form->field($eventsModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                    ->textInput(['class' => 'form-control form-control-sm'])
                                    ->label("Название <span class='text-danger'>*</span>")
                                    ->hint('Первый символ названия может быть английской или русской буквой, либо символы: ? . ( !', ['class' => 'hint-block'])
                            ?>

                            <?=
                                    $form->field($eventsModel, 'other_name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                    ->textInput(['class' => 'form-control form-control-sm'])
                                    ->hint('Дополнительная информация, будет отображаться рядом с названием', ['class' => 'hint-block'])
                            ?>

                            <div class="form-group">
                                <div class="col-lg-offset-1 col-lg-11">
                                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                                </div>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>
                    <hr>


                    <?php /* echo "<pre>"; print_r($eventType); */ ?>
                    <div class="mrg-top45">
                        <ul class="list-group mrg-top30">
                            <li class="list-group-item">
                                <h4><b>Типы мероприятий</b></h4>
                            </li>
                            <li class="list-group-item">
                                <ul class="list-group proff-list">
                                    <?php foreach ($eventType as $key => $value): ?>
                                        <li data-eventtype="<?= $value['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center event-type-li">
                                            <div class="event-type-name">
                                                <?= $value['name'] ?>
                                            </div>
                                            <div>
                                                <span class="badge badge-danger badge-pill delete-event-type cursor-pointer">Удалить</span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <?php // echo "<pre>"; print_r($events);   ?>
                    <div class="mrg-top45">
                        <ul class="list-group mrg-top30">
                            <li class="list-group-item">
                                <h4><b>Спектакли</b></h4>
                            </li>
                            <li class="list-group-item">
                                <ul class="list-group proff-list">
                                    <?php foreach ($events as $key => $value): ?>
                                        <li class="list-group-item">
                                            <b><?= $value['name'] ?></b>
                                        </li>
                                        <?php if ($value['events']): ?>
                                            <li class="list-group-item">
                                                <ul class="list-group proff-list">
                                                    <?php foreach ($events[$key]['events'] as $keyE => $valueE): ?>
                                                        <li data-event="<?= $valueE['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center event-li">
                                                            <div>
                                                                <div class="proff-name">
                                                                    <?= $valueE['name'] ?>
                                                                </div>
                                                                <div class="other-name">
                                                                    <?= $valueE['other_name'] ? $valueE['other_name']: "" ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <span class="badge badge-info badge-pill edit-event-other-name cursor-pointer">Ред.</span>
                                                                <span class="badge badge-danger badge-pill delete-event cursor-pointer">Удалить</span>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-info">
                        <div class="card-header"><h5 class="card-title">Добавить зал</h5></div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin() ?>
                    <?=
                            $form->field($roomModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                            ->textInput(['class' => 'form-control form-control-sm'])
                            ->label("Название зала <span class='text-danger'>*</span>")
                    ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                        </div>
                    </div>

                    <h4 class="mrg-top45"><b>Залы</b></h4>
                    <div>
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
                ВНИМАНИЕ! Помещение будет удалено из системы! Все мероприятия,
                которые были когда-либо заполнены для него, будут утеряны
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


<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        var actionRoom = false;
        $('.edit-room-name').click(function () {
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

        $('body').on('click', '.edit-room-submit', function () {
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
                success: function (data) {
                    if (data == 1) {
                        self.parentNode.innerHTML = roomName;
                        showNotifications("Зал успешно переименован", 3000, NOTIF_GREEN);
                    } else if (data == 0) {
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

        $('.delete-room').click(function () {
            actionRoom = this.parentNode.parentNode.dataset.room;
            $('#deleteRoomModal').modal('show');
        });

        $('#delete-room-submit').click(function () {
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
                success: function (data) {
                    if (data == 1) {
                        $('.room-item[data-room=' + actionRoom + ']').remove();
                        $('#deleteRoomModal').modal('hide');
                        showNotifications("Зал успешно удален", 3000, NOTIF_GREEN);
                    } else if (data == 0) {
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
        $('.delete-event-type').click(function () {
            actionEventType = this.parentNode.parentNode.dataset.eventtype;
            $('#deleteEventTypeModal').modal('show');
        });

        $('#delete-event-type-submit').click(function () {
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
                success: function (data) {
                    if (data == 1) {
                        $('.event-type-li[data-eventtype=' + actionEventType + ']').remove();
                        $('#deleteEventTypeModal').modal('hide');
                        showNotifications("Тип мероприятия успешно удален", 3000, NOTIF_GREEN);
                    } else if (data == 0) {
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
        $('.delete-event').click(function () {
            actionEvent = this.parentNode.parentNode.dataset.event;
            $('#deleteEventModal').modal('show');
        });

        $('#delete-event-submit').click(function () {
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
                success: function (data) {
                    if (data == 1) {
                        $('.event-li[data-event=' + actionEvent + ']').remove();
                        $('#deleteEventModal').modal('hide');
                        showNotifications("Спектакль успешно удален", 3000, NOTIF_GREEN);
                    } else if (data == 0) {
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

        
        var otherNameEvent = false;
        $('.edit-event-other-name').click(function(){
            otherNameEvent = this.parentNode.parentNode.dataset.event;
            var otherName = this.parentNode.parentNode.getElementsByClassName('other-name')[0];
            var createInput = document.createElement('input');
            createInput.value = otherName.innerHTML.trim();
            otherName.innerHTML = '';
            var createOk = document.createElement('div');
            createOk.className = 'btn btn-sm btn-success edit-other-name-submit';
            createOk.innerHTML = 'ok';
            otherName.append(createInput);
            otherName.append(createOk);
        });
        
        $('body').on('click', '.edit-other-name-submit', function(){
            var otherName = this.parentNode.querySelector('input');
//            var otherName = $(this).parent().find('input');
            var self = this;
            var data = {
                trigger: 'edit-other-name',
                eventId: otherNameEvent,
                otherName: otherName.value,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/panel/room-event',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        self.parentNode.innerHTML = otherName.value;
                        showNotifications("Поле успешно изменено!", 2000, NOTIF_GREEN);
                    } else if (data == 0) {
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


    }
</script>
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
                <div class="card-header"><h5 class="card-title">Залы, которые отображаются в сводном расписании</h5></div>
                <div class="card-body text-info">
                    <div id="schedule-one-rooms-container">
                        <?php if (isset($allConfig['schedule_one_rooms']) && $rooms): ?>
                            <?php foreach ($rooms as $key => $value): ?>
                                <?php if (in_array($value['id'], $allConfig['schedule_one_rooms'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1">
                                        <?= $value['name'] ?> <span data-name="schedule_one_rooms" class="badge badge-danger delete-config"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-config-1" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Типы мероприятий, которые отображаются в расписании актеров</h5></div>
                <div class="card-body text-info">
                    <div id="schedule-two-event-type-container">
                        <?php if (isset($allConfig['schedule_two_event_type']) && $eventType): ?>
                            <?php foreach ($eventType as $key => $value): ?>
                                <?php if (in_array($value['id'], $allConfig['schedule_two_event_type'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1">
                                        <?= $value['name'] ?> <span data-name="schedule_two_event_type" class="badge badge-danger delete-config"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-config-2" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Тип мероприятия, относящийся к спектаклю</h5></div>
                <div class="card-body text-info">
                    <div id="spectacle-event-container">
                        <?php if (isset($allConfig['spectacle_event']) && $eventType): ?>
                            <?php foreach ($eventType as $key => $value): ?>
                                <?php if (in_array($value['id'], $allConfig['spectacle_event'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1">
                                        <?= $value['name'] ?> <span data-name="spectacle_event" class="badge badge-danger delete-config"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-config-3" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Служба актеров</h5></div>
                <div class="card-body text-info">
                    <div id="actors-prof-cat-container">
                        <?php if (isset($allConfig['actors_prof_cat']) && $actorsCat): ?>
                            <?php foreach ($actorsCat as $key => $value): ?>
                                <?php if (in_array($value['id'], $allConfig['actors_prof_cat'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1">
                                        <?= $value['name'] ?> <span data-name="actors_prof_cat" class="badge badge-danger delete-config"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-config-4" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Службы, которые необходимо скрывать из расписания</h5></div>
                <div class="card-body text-info">
                    <div id="actors-prof-cat-container">
                        <?php if (isset($allConfig['hide_prof_cat']) && $actorsCat): ?>
                            <?php foreach ($actorsCat as $key => $value): ?>
                                <?php if (in_array($value['id'], $allConfig['hide_prof_cat'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1">
                                        <?= $value['name'] ?> <span data-name="hide_prof_cat" class="badge badge-danger delete-config"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-config-5" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
            <div class="card border-info mt-2">
                <div class="card-header"><h5 class="card-title">Службы, чьи фамилии требуется отображать в расписании</h5></div>
                <div class="card-body text-info">
                    <div id="actors-prof-cat-container">
                        <?php if (isset($allConfig['show_in_schedule_prof_cat']) && $actorsCat): ?>
                            <?php foreach ($actorsCat as $key => $value): ?>
                                <?php if (in_array($value['id'], $allConfig['show_in_schedule_prof_cat'])): ?>
                                    <button data-id="<?= $value['id'] ?>" type="button" class="btn btn-sm btn-info mt-1">
                                        <?= $value['name'] ?> <span data-name="show_in_schedule_prof_cat" class="badge badge-danger delete-config"><i class="fas fa-times"></i></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div id="add-config-6" class="btn btn-sm btn-outline-success mrg-top15"><i class="fas fa-plus"></i> Добавить</div>
                </div>
            </div>
            
        </div>
    </div>
    <br>
</div>

<!-- Modal rooms list -->
<div class="modal fade" id="roomsListModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Список залов</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="rooms-list-select">
                    <?php foreach ($rooms as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-room" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal event type -->
<div class="modal fade" id="eventTypeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Типы мероприятий</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="event-type-select">
                    <?php foreach ($eventType as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-event-type" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal prof cat -->
<div class="modal fade" id="profCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Список служб</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="prof-cat-select">
                    <?php foreach ($actorsCat as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-prof-cat" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal delete config -->
<div class="modal fade" id="deleteConfigModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удалить?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Настройка будет удалена
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-config-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>



<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
        var configName = false;
        var configValue = false;
        
        // Config 1
        $('#add-config-1').click(function () {
            configName = 'schedule_one_rooms';
            $('#roomsListModal').modal('show');
        });
        
        // Config 2
        $('#add-config-2').click(function(){
            configName = 'schedule_two_event_type';
            $('#eventTypeModal').modal('show');
        });
        
        // Config 3
        $('#add-config-3').click(function(){
           configName = 'spectacle_event';
           $('#eventTypeModal').modal('show');
        });
        
        // Config 4
        $('#add-config-4').click(function(){
            configName = 'actors_prof_cat';
            $('#profCatModal').modal('show');
        });
        
        // Config 5
        $('#add-config-5').click(function(){
            configName = 'hide_prof_cat';
            $('#profCatModal').modal('show');
        });
        
        // Config 6
        $('#add-config-6').click(function(){
            configName = 'show_in_schedule_prof_cat';
            $('#profCatModal').modal('show');
        });
        
        
        // ROOMS
        $('#add-room').click(function(){
            var room = $('#rooms-list-select').val();
            addConfigAjax(configName, room);
        });
        // EVENTS TYPE
        $('#add-event-type').click(function(){
            var eventType = $('#event-type-select').val();
            addConfigAjax(configName, eventType);
        });
        // PROF CATEGORIES
        $('#add-prof-cat').click(function(){
           var profCat = $('#prof-cat-select').val();
           addConfigAjax(configName, profCat);
        });
        
        
        
        
        
        // Универсальное удаление для всех
        $('.delete-config').click(function(){
            configName = this.dataset.name;
            configValue = this.parentNode.dataset.id;
            $('#deleteConfigModal').modal('show');
        });
        
        $('#delete-config-submit').click(function(){
            removeConfigAjax(configName, configValue);
        });
        
        // Добавляет конфиг
        function addConfigAjax(configName, configValue){
            goPreloader();
            var data = {
                trigger: 'add-simple-config',
                configName: configName,
                configValue: configValue,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        location.reload(true);
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
        };
        
        // Удаляет конфиг
        function removeConfigAjax(configName, configValue){
            goPreloader();
            var data = {
                trigger: 'delete-simple-config',
                configName: configName,
                configValue: configValue,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/setting/index',
                data: data,
                success: function (data) {
                    console.log(data);
                    if (data == 1) {
                        location.reload(true);
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
        }


    }
</script>

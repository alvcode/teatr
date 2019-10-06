<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Расписание на неделю';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    @media print {
        .board--top-sidebar{
            display: none;
        }
        .board--left-sidebar{
            display: none;
        }
        .board-content{
            margin-left: 0;
        }
        .three--schedule-content{
            width: 100%;
        }
        .three--title-row .room{
            min-width: 0;
        }
        .three--date-row .room{
            min-width: 0;
        }
        .arrow-left, .arrow-right{
            display: none;
        }
        .badge-info{
            color: #000;
            background-color: #fff;
        }
        #excel-download{
            display: none;
        }
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>

            <?= $this->render('../templates/_flash') ?>

        </div>
    </div>
    
    <?php
     //echo yii\helpers\VarDumper::dumpAsString($users, 10, true);
    ?>

    <div class="three--schedule-container">

        <div id="three--schedule-content">
            <div class="schedule-controls">
                <div>
                    <div id="control-name" class="name"></div>
                    <div class="arrow-left"><i id="month-left" class="fas fa-arrow-circle-left cursor-pointer" aria-hidden="true"></i></div>
                    <div class="arrow-right"><i id="month-right" class="fas fa-arrow-circle-right cursor-pointer" aria-hidden="true"></i></div>
                </div>
                <div>
                    <div id="room-setting" class="btn btn-sm btn-info">Настройка залов</div>
                </div>
            </div>
            <div class="three--title-row mrg-top15">
                <div class="date">Дата</div>
                <?php foreach ($rooms as $key => $value): ?>
                    <div data-room="<?= $value['id'] ?>" class="room"><?= $value['name'] ?></div>
                <?php endforeach; ?>
            </div>
            <div id="three--schedule-items"></div>
        </div>
        <br>
        
        <!--<a href="/schedule/excel" id="excel-download" target="_blank" class="btn btn-sm btn-info">Выгрузить в Excel</a>-->
        <a href="/schedule/word" id="word-download" target="_blank" class="btn btn-sm btn-info">Выгрузить в Word</a>
        <div id="generate-link" class="btn btn-sm btn-info">Сгенерировать ссылку</div>
    </div>



</div>
<br>

<!-- Modal add event -->
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Добавить мероприятие</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th scope="row">Дата</th>
                            <td id="add--date"></td>
                        </tr>
                        <tr>
                            <th scope="row">Время начала</th>
                            <td>
                                <div>
                                    <input type="checkbox" class="" id="add--all-day">
                                    <label class="form-check-label noselect cursor-pointer" for="add--all-day">Весь день</label>
                                </div>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control form-control-sm" id="add--time_from">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-outline-danger clean-input" type="button" id="button-addon2"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Время окончания
                                <i class="fas fa-exclamation-circle my-tooltip" data-toggle="tooltip" data-placement="right" title="Можно не указывать, если окончание мероприятия неизвестно, но тогда не будут работать подсказки, предупреждающие о пересечениях времени."></i>
                            </th>
                            <td>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control form-control-sm" id="add--time_to">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-outline-danger clean-input" type="button" id="button-addon2"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Примечание
                                <i class="fas fa-exclamation-circle my-tooltip" data-toggle="tooltip" data-placement="right" title="Дополнительная информация. Будет отображаться в расписании. До 1000 символов"></i>
                            </th>
                            <td>
                                <div class="input-group mb-3">
                                    <textarea type="text" class="form-control form-control-sm" id="add--add-info"></textarea>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Тип мероприятия</th>
                            <td id="add--event_type">
                                <div class="form-group">
                                    <select id="select-event-type" class="form-control form-control-sm">
                                        <?php foreach ($eventType as $key => $value): ?>
                                            <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Мероприятие</th>
                            <td id="add--event">
                                 <div>
                                    <input type="checkbox" class="" id="add--modified-event">
                                    <label class="form-check-label noselect cursor-pointer" for="add--modified-event">Измененное!</label>
                                </div>
                                <div>
                                    <input type="checkbox" class="" id="add--without-event">
                                    <label class="form-check-label noselect cursor-pointer" for="add--without-event">Без мероприятия</label>
                                </div>
                                <div id="add-div-category" class="form-group mrg-top15">
                                    <select id="select-event-category" class="form-control form-control-sm">
                                        <?php foreach ($eventCategories as $key => $value): ?>
                                            <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="add-div-event" class="form-group">
                                    <select id="select-event" class="form-control form-control-sm">
                                        <?php foreach ($events as $key => $value): ?>
                                            <option data-category="<?= $value['category_id'] ?>" data-other-name="<?= $value['other_name'] ?>" value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <input type="checkbox" class="" id="add--without-intersect">
                                    <label class="form-check-label noselect cursor-pointer" for="add--without-intersect">Не проверять на пересечения</label>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-event-submit" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="three--right-more">
    <div class="col-12">
        <span id="three--right-more-close">&times;</span>
    </div>
    <div class="col-12 mrg-top15">
        <div class="three--right-more-content">
            <div class="alert alert-info" id="three--right-more-meta"></div>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th scope="row">Время начала</th>
                        <td>
                            <div>
                                <input type="checkbox" class="" id="edit--all-day">
                                <label class="form-check-label noselect cursor-pointer" for="edit--all-day">Весь день</label>
                            </div>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control form-control-sm" id="edit--time_from">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-danger clean-input" type="button" id="button-addon2"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Время окончания
                            <i class="fas fa-exclamation-circle my-tooltip" data-toggle="tooltip" data-placement="right" title="Можно не указывать, если окончание мероприятия неизвестно, но тогда не будут работать подсказки, предупреждающие о пересечениях времени."></i>
                        </th>
                        <td>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control form-control-sm" id="edit--time_to">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-danger clean-input" type="button" id="button-addon2"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Примечание
                            <i class="fas fa-exclamation-circle my-tooltip" data-toggle="tooltip" data-placement="right" title="Дополнительная информация. Будет отображаться в расписании. До 1000 символов"></i>
                        </th>
                        <td>
                            <div class="input-group mb-3">
                                <textarea type="text" class="form-control form-control-sm" id="edit--add-info"></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Тип мероприятия</th>
                        <td id="add--event_type">
                            <div class="form-group">
                                <select id="select-edit-event-type" class="form-control form-control-sm">
                                    <?php foreach ($eventType as $key => $value): ?>
                                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Мероприятие
                        </th>
                        <td>
                            <div>
                                <input type="checkbox" class="" id="edit--without-event">
                                <label class="form-check-label noselect cursor-pointer" for="edit--without-event">Без мероприятия</label>
                            </div>
                            <div class="form-group mrg-top15">
                                <select id="select-edit-event-category" class="form-control form-control-sm">
                                    <?php foreach ($eventCategories as $key => $value): ?>
                                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select id="select-edit-event" class="form-control form-control-sm">
                                    <?php foreach ($events as $key => $value): ?>
                                        <option data-category="<?= $value['category_id'] ?>" data-other-name="<?= $value['other_name'] ?>" value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div>
                <input type="checkbox" class="" id="edit--modified-event">
                <label class="form-check-label noselect cursor-pointer" for="edit--modified-event">Измененное!</label>
            </div>
            <div>
                <input type="checkbox" class="" id="edit--without-intersect">
                <label class="form-check-label noselect cursor-pointer" for="edit--without-intersect">Не проверять на пересечения</label>
            </div>
            <div>
                <input type="checkbox" class="" id="edit--is-all">
                <label class="form-check-label noselect cursor-pointer" for="edit--is-all">Все</label>
            </div>
            <div class="three--copy-event">
                <h5 class="text-info">Копировать запись на другой день</h5>
                <div class="form-group mb-3">
                    <label class="control-label">Зал</label>
                    <select id="copy--select-room" class="form-control form-control-sm">
                        <?php foreach ($rooms as $key => $value): ?>
                            <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="control-label">Дата</label>
                    <select id="copy--select-date" class="form-control form-control-sm">
                        
                    </select>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="copy--checkbox-move-users">
                    <label class="form-check-label noselect cursor-pointer" for="copy--checkbox-move-users">Перенести сотрудников</label>
                </div>
                <div class="three--copy-buttons">
                    <div id="copy--save-copy" class="btn btn-sm btn-success">Копировать</div>
                    <div id="copy--cancel-copy" class="btn btn-sm btn-danger">Отмена</div>
                </div>
            </div>
            <div class="three--right-save-button mrg-top15">
                <div id="save--event-time" class="btn btn-sm btn-success">Сохранить</div>
                <div id="copy--event" class="btn btn-sm btn-info">Копировать</div>
                <div id="delete--event" class="btn btn-sm btn-danger">Удалить мероприятие</div>
            </div>
            <hr>
            <div class="mrg-top15">
                <div id="add-prof-categories" class="btn btn-sm btn-success">Добавить службу <i class="fas fa-plus-circle"></i></div>
            </div>
            <div class="text-center mrg-top15" id="prof-cat-right-button-container"></div>
            <div style="display: none;" id="add-user-in-schedule-container" class="text-center mrg-top15"><div class="btn btn-sm btn-outline-info" id="add-user-in-schedule-button"><i class="fas fa-plus-circle"></i></div></div>
            <div class="mrg-top15" id="user-in-event-right-button-container"></div>
        </div>
    </div>
</div>


<!-- Modal prof categories --> 
<div class="modal fade" id="profCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Выбор служб</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center noselect">
                <?php foreach ($profCategories as $key => $value): ?>
                    <div data-id="<?= $value['id'] ?>" class="cursor-pointer add-prof-cat-item"><?= $value['name'] ?></div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-prof-cat-submit" type="button" class="btn btn-sm btn-success">Применить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal users list -->
<div class="modal fade" id="usersListModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Список сотрудников</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="search-cast" class="btn btn-sm btn-info">Найти состав</div>
            <div class="modal-body">
                <div class="user-modal-container">
                    <?php foreach ($users as $key => $value): ?>
                    <div class="user-modal-liter-container">
                            <div style="font-weight: 700;" class="text-danger liter-key"><?= $key ?></div>
                            <?php foreach($value as $keyV => $valueV): ?>
                                <div class="actor-list-item noselect" data-prof-cat="<?= $valueV['userProfessionJoinProf']['prof']['proff_cat_id'] ?>" data-id="<?= $valueV['id'] ?>"><?= $valueV['surname'] ?> <?= $valueV['name'] ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-user-list-submit" type="button" class="btn btn-sm btn-success">Применить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete prof cat --> 
<div class="modal fade" id="deleteProfCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удаление службы</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Внимание! С данного мероприятия будет удалена служба и сотрудники
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-prof-cat-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete in schedule --> 
<div class="modal fade" id="deleteInScheduleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удаление из мероприятия</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Сотрудник будет удален из данного мероприятия
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-in-schedule-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete event --> 
<div class="modal fade" id="deleteEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удаление мероприятия</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Внимание! Мероприятие и все сотрудники в нем будут удалены без возможности восстановления
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-event-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal generate link --> 
<div class="modal fade" id="generateLinkModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ссылка для просмотра данного расписания</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <a href="#" class="generate-link-container" id="generate-link-container" target="_blank"></a>
                <div style="display:block;" id="copy-link-button" class="btn btn-sm btn-info mrg-top15">Двойной клик для копирования</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal room setting --> 
<div class="modal fade" id="roomSettingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Настройка залов</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php foreach ($rooms as $key => $value): ?>
                    <div data-room="<?= $value['id'] ?>" class="room-setting-item cursor-pointer"><?= $value['name'] ?></div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="room-setting-submit" type="button" class="btn btn-sm btn-success">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
        var config = false; // Храним все настройки приложения
        var roomSetting = false; // Храним настройки отображения залов

        Object.defineProperty(Array.prototype, 'includes', {
            value: function (searchElement, fromIndex) {

                if (this == null) {
                    throw new TypeError('"this" is null or not defined');
                }
                var o = Object(this);
                var len = o.length >>> 0;
                if (len === 0) {
                    return false;
                }
                var n = fromIndex | 0;
                var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

                function sameValueZero(x, y) {
                    return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
                }
                while (k < len) {
                    if (sameValueZero(o[k], searchElement)) {
                        return true;
                    }
                    k++;
                }
                return false;
            }
        });
        
        Object.defineProperty(Array.prototype, 'remove', {
            value: function (value) {
                return this.splice(value, 1);
//                var idx = this.indexOf(value);
//                if (idx != -1) {
//                    // Второй параметр - число элементов, которые необходимо удалить
//                    return this.splice(idx, 1);
//                }
                return false;
            }
        });

        Date.prototype.setDay = function (dayCount) {
            if (this.getDay() == 0) {
                this.setDate(this.getDate() - 6);
            } else if (dayCount > this.getDay()) {
                this.setDate(this.getDate() + (dayCount - this.getDay()));
            } else {
                this.setDate(this.getDate() - (this.getDay() - dayCount));
            }
            return true;
        };
        
        $("#add--time_from, #edit--time_from, #add--time_to, #edit--time_to").mask("99:99", {clearIfNotMatch: true});
//        $('#add--time_from, #edit--time_from').bootstrapMaterialDatePicker({
//            date: false,
//            shortTime: false,
//            format: 'HH:mm'
//        });
//        $('#add--time_to, #edit--time_to').bootstrapMaterialDatePicker({
//            date: false,
//            shortTime: false,
//            format: 'HH:mm'
//        });

        var rooms = document.querySelector('.three--title-row').getElementsByClassName('room');

        // ====== Плагин календаря
        var monthName = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        //    var monthNameTwo = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var monthNameDec = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        var nowDate = new Date();

        var scheduleData = false;
        var datePeriod = {};
        var addNowDate = {}; // Выбранная дата
        var addNowRoom = false; // выбранный зал
        
//        document.getElementById('excel-download').setAttribute('href', '/schedule/excel?from=' +datePeriod[0].day);

        var weekNumber = ['1', '2', '3', '4', '5', '6', '0'];

        renderCalendar(nowDate);

        $('#month-right').click(function () {
            nowDate.setDay(1);
            nowDate.setDate(nowDate.getDate() + 7);
            renderCalendar(nowDate);
            loadSchedule(datePeriod);
            updateCopyField();
        });
        $('#month-left').click(function () {
            nowDate.setDay(1);
            nowDate.setDate(nowDate.getDate() - 7);
            renderCalendar(nowDate);
            loadSchedule(datePeriod);
            updateCopyField();
        });
        
        $('body').on('dblclick', '.room-cell', function (e) {
            if (!e.target.classList.contains('room-cell')) {
                return false;
            }
            addNowDate.day = this.parentNode.dataset.day;
            addNowDate.month = this.parentNode.dataset.month;
            addNowDate.year = this.parentNode.dataset.year;
            addNowRoom = this.dataset.room;

            $('#add--date').html(normalizeDate(addNowDate.day + "." + addNowDate.month + "." + addNowDate.year));
            $('#addEventModal').modal('show');
        });
        
        // Отображаем только те спектакли, категория которых выбрана
        function eventCatSort() {
            var eventCategory = $('#select-event-category').val();
            var events = document.getElementById('select-event');
            var z = 0;
            for (var i = 0; i < events.options.length; i++) {
                if (events.options[i].dataset.category == eventCategory) {
                    events.options[i].style.display = 'block';
                    if (z === 0) {
                        events.options[i].selected = true;
                        z++;
                    }
                } else {
                    events.options[i].style.display = 'none';
                }
            }
            var editEventCategory = $('#select-edit-event-category').val();
            var editEvents = document.getElementById('select-edit-event');
            var k = 0;
            for (var i = 0; i < editEvents.options.length; i++) {
                if (editEvents.options[i].dataset.category == editEventCategory) {
                    editEvents.options[i].style.display = 'block';
                    if (k === 0) {
                        editEvents.options[i].selected = true;
                        k++;
                    }
                } else {
                    editEvents.options[i].style.display = 'none';
                }
            }
        }
        eventCatSort();
        
        var withoutEvent = 0;
        $('#add--without-event').click(function(){
            if($(this).prop('checked')){
                withoutEvent = 1;
                $('#select-event-category').prop('disabled', true);
                $('#select-event').prop('disabled', true);
            }else{
                withoutEvent = 0;
                $('#select-event-category').prop('disabled', false);
                $('#select-event').prop('disabled', false);
            }
        });
        
        var modifiedEvent = 0;
        $('#add--modified-event').click(function(){
            if($(this).prop('checked')){
                modifiedEvent = 1;
            }else{
                modifiedEvent = 0;
            }
        });

        var withoutIntersect = 0;
        $('#add--without-intersect').click(function(){
            if($(this).prop('checked')){
                withoutIntersect = 1;
            }else{
                withoutIntersect = 0;
            }
        });

        $('#select-event-category, #select-edit-event-category').change(function () {
            eventCatSort();
        });
        
        // Мероприятие на ВЕСЬ ДЕНЬ
        var addAllDay = 0;
        $('#add--all-day').click(function(){
            if($(this).prop('checked')){
                addAllDay = 1;
                $('#add--time_from').prop('disabled', true);
                $('#add--time_to').prop('disabled', true);
                $('#add--time_from').val('00:00');
                $('#add--time_to').val('24:00');
            }else{
                addAllDay = 0;
                $('#add--time_from').prop('disabled', false);
                $('#add--time_to').prop('disabled', false);
                $('#add--time_from').val('');
                $('#add--time_to').val('');
            }
        });
        
        // Добавляем мероприятие через стандартное добавление
        $('#add-event-submit').click(function () {
            var timeFrom = $('#add--time_from').val();
            var timeTo = $('#add--time_to').val();
            var eventType = $('#select-event-type').val();
            var eventCategory = $('#select-event-category').val();
            var event = $('#select-event').val();
            var addInfo = $('#add--add-info').val();
//            console.log(timeToMinute(timeFrom), timeToMinute(timeTo));
//            return false;
            if (!timeFrom || timeFrom == '') {
                showNotifications("Не выбрано время начала мероприятия", 3000, NOTIF_RED);
                return false;
            }
            if(withoutIntersect == 0){
                if (!checkTimesInterval(timeToMinute(timeFrom), timeToMinute(timeTo), addNowDate, addNowRoom)) {
                    showNotifications("Добавляемое мероприятие пересекается с другими в этот день", 3000, NOTIF_RED);
                    return false;
                }
            }
//            return false;
            goPreloader();
            var data = {
                trigger: 'add-schedule',
                date: addNowDate,
                room: addNowRoom,
                timeFrom: timeFrom,
                timeTo: timeTo,
                addInfo: addInfo,
                eventType: eventType,
                eventCategory: eventCategory,
                event: event,
                withoutEvent: withoutEvent,
                modifiedEvent: modifiedEvent,
                allDay: addAllDay
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if (result.response == 'ok') {
                        scheduleData[scheduleData.length] = result.result;
                        addEventInCalendar(generateCellData(result.result));
                        $('#addEventModal').modal('hide');
                        $('.clean-input').click();
                    } else if (result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });

        function renderCalendar(dateObj) {
            $('#three--schedule-items').empty();
            dateObj.setDay(1);
            document.getElementById('control-name').innerHTML = dateObj.getDate() + " " + monthNameDec[dateObj.getMonth()];
            datePeriod[0] = {};
            datePeriod[0].day = dateObj.getDate();
            datePeriod[0].month = (dateObj.getMonth() + 1);
            datePeriod[0].year = dateObj.getFullYear();

            for (var i = 0; i < 7; i++) {
                dateObj.setFullYear(dateObj.getFullYear());
                dateObj.setMonth(dateObj.getMonth());
                if (i > 0) {
                    dateObj.setDate(dateObj.getDate() + 1);
                }
                document.getElementById('three--schedule-items').append(returnScheduleRow(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), dateObj.getDay(), rooms));
            }
            document.getElementById('control-name').innerHTML += " - " + dateObj.getDate() + " " + monthNameDec[dateObj.getMonth()];
            datePeriod[1] = {};
            datePeriod[1].day = dateObj.getDate();
            datePeriod[1].month = (dateObj.getMonth() + 1);
            datePeriod[1].year = dateObj.getFullYear();
            
//            document.getElementById('excel-download').setAttribute('href', '/schedule/excel-one?from=' +datePeriod[0].year +"-" +datePeriod[0].month +"-" +datePeriod[0].day +"&to="+datePeriod[1].year +"-" +datePeriod[1].month +"-" +datePeriod[1].day);
            document.getElementById('word-download').setAttribute('href', '/schedule/word?from=' +datePeriod[0].year +"-" +datePeriod[0].month +"-" +datePeriod[0].day +"&to="+datePeriod[1].year +"-" +datePeriod[1].month +"-" +datePeriod[1].day);
            return true;
        };
        
        function returnScheduleRow(year, month, day, week, rooms) {
            var createContainer = document.createElement('div');
            createContainer.dataset.day = day;
            createContainer.dataset.month = month;
            createContainer.dataset.year = year;
            createContainer.className = 'three--date-row';

            var createDate = document.createElement('div');
            createDate.className = 'date';
            createDate.innerHTML = normalizeDate(day + "." + month + "." + year) + "<br>" + weekdayName[week];
            if (week == 6 || week == 0) {
                createDate.style.color = 'red';
            }

            createContainer.append(createDate);

            for (var i = 0; i < rooms.length; i++) {
                var createRoom = document.createElement('div');
                createRoom.className = 'room room-cell';
                createRoom.dataset.room = rooms[i].dataset.room;
                createContainer.append(createRoom);
            }
            return createContainer;
        }
        ;

        /**
         * Проверяет, не пересекается ли время мероприятия с другими мероприятиями
         * @param {integer} exclude - id мероприятия, которое не должно учавствовать в проверке (используем при изменении, т.к тогда эта же запись будет участвовать и не пропускать)
         * @returns {boolean}
         */
        function checkTimesInterval(timeFrom, timeTo, date, room, exclude) {
            if (!exclude)
                exclude = 0;
            var dateRows = document.getElementsByClassName('three--date-row');
            for (var i = 0; i < dateRows.length; i++) {
                if (+date.day == +dateRows[i].dataset.day && +date.month == +dateRows[i].dataset.month && +date.year == +dateRows[i].dataset.year) {
                    var roomsCell = dateRows[i].getElementsByClassName('room-cell');
                    for (var z = 0; z < roomsCell.length; z++) {
                        if (+roomsCell[z].dataset.room == +room) {
                            var eventsCell = roomsCell[z].getElementsByClassName('event-cell');
                            if (eventsCell.length) {
                                for (var k = 0; k < eventsCell.length; k++) {
                                    if (+exclude != +eventsCell[k].dataset.id) {
                                        if (+eventsCell[k].dataset.timeFrom == +timeFrom) {
                                            return false;
                                        }
                                        if (eventsCell[k].dataset.timeTo !== undefined && +timeTo
                                                && ((+timeFrom >= +eventsCell[k].dataset.timeFrom && +timeFrom < +eventsCell[k].dataset.timeTo)
                                                        || (+timeTo > +eventsCell[k].dataset.timeFrom && +timeTo <= +eventsCell[k].dataset.timeTo)
                                                        || (+timeFrom <= +eventsCell[k].dataset.timeFrom && +timeTo >= +eventsCell[k].dataset.timeTo))) {
                                            return false;
                                        }
                                        if (eventsCell[k].dataset.timeTo === undefined && +timeTo
                                                && (+eventsCell[k].dataset.timeFrom > +timeFrom && +eventsCell[k].dataset.timeFrom < +timeTo)) {
                                            return false;
                                        }
                                        if (eventsCell[k].dataset.timeTo !== undefined && !timeTo
                                                && (+timeFrom > +eventsCell[k].dataset.timeFrom && +timeFrom < +eventsCell[k].dataset.timeTo)) {
                                            return false;
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }
            return true;
        }

        /**
         * Генерирует объект с параметрами для добавления в расписание.
         * 
         * @param {object} result
         * @returns {object}
         */
        function generateCellData(result) {
            var dateT = new Date(result.date);
            var cellData = {
                id: result.id,
                date: {
                    day: dateT.getDate(),
                    month: dateT.getMonth(),
                    year: dateT.getFullYear()
                },
                room: result.room_id,
                eventType: result.eventType.name,
                eventTypeId: result.eventType.id,
                eventName: (result.event !== null ? result.event.name : ''),
                eventOtherName: (result.event !== null && result.event.other_name !== null ? result.event.other_name : ''),
                timeFrom: result.time_from,
                timeTo: (result.time_to !== null ? result.time_to : ''),
                profCat: result.profCat,
                is_modified: result.is_modified,
                users: result.allUsersInEvent,
                addInfo: result.add_info,
                is_all: result.is_all
            };
            return cellData;
        }
        ;

        /**
         * Добавляет мероприятие в календарь
         * @param {object} params
         */
        function addEventInCalendar(params) {
            var dateRows = document.getElementsByClassName('three--date-row');
            for (var i = 0; i < dateRows.length; i++) {
                if (params.date.day == dateRows[i].dataset.day && params.date.month == dateRows[i].dataset.month && params.date.year == dateRows[i].dataset.year) {
                    var roomsCell = dateRows[i].getElementsByClassName('room-cell');
                    for (var z = 0; z < roomsCell.length; z++) {
                        if (roomsCell[z].dataset.room == params.room) {
                            var createContainer = document.createElement('div');
                            createContainer.className = 'event-cell noselect';
                            if(+params.is_modified > 0){
                                createContainer.classList.add('text-danger', 'font-weight-bold');
                            }
                            createContainer.dataset.id = params.id;
                            createContainer.dataset.timeFrom = params.timeFrom;
                            if (params.timeTo && params.timeTo != '') {
                                createContainer.dataset.timeTo = params.timeTo;
                            }
                            var createBudgie = document.createElement('span');
                            createBudgie.className = 'badge badge-pill badge-info';
                            if(+params.timeFrom == 0 && params.timeTo && +params.timeTo == 1440){
                                createBudgie.innerHTML = 'Весь день';
                            }else{
                                createBudgie.innerHTML = minuteToTime(params.timeFrom) + (params.timeTo && params.timeTo != '' ? " - " + minuteToTime(params.timeTo) : "");
                            }

                            var createEventType = document.createElement('span');
                            createEventType.className = 'type';
                            createEventType.dataset.id = params.eventTypeId;
                            createEventType.innerHTML = "(" + params.eventType + ")";

                            var createEventName = document.createElement('span');
                            createEventName.className = 'name';
                            createEventName.innerHTML = (params.eventName && params.eventName != '' ? "\"" +params.eventName +"\"":"") + (params.eventOtherName && params.eventOtherName != '' ? " (" + params.eventOtherName + ")" : "");

                            createContainer.append(createBudgie);
                            createContainer.append(createEventType);
                            createContainer.append(createEventName);
                            
                            var adminListArr = [];
                            var createAdminList = document.createElement('div');
                            createAdminList.className = 'three--user-admin-list';
                            for(var key in params.users){
                                if(!config.actors_prof_cat.includes(params.users[key].userWithProf.userProfession.prof.proff_cat_id)){
                                    adminListArr[adminListArr.length] = params.users[key].userWithProf.surname +(params.users[key].userWithProf.show_full_name == 1?" " + params.users[key].userWithProf.name:"");
                                }
                            }
                            if(adminListArr.length){
                                createAdminList.innerHTML = adminListArr.join(', ');
                            }
                            createContainer.append(createAdminList);
                            
                            if(params.addInfo){
                                var createAddInfo = document.createElement('div');
                                createAddInfo.className = 'three--add-info-block';
                                createAddInfo.innerHTML = "(" +params.addInfo +")";
                                createContainer.append(createAddInfo);
                            }
                            
                            // Если стоит флаг is_all, то отображаем слово ВСЕ, иначе- фамилии
                            var userListArr = [];
                            var createUserList = document.createElement('div');
                            createUserList.className = 'three--user-actors-list';
                            if(+params.is_all > 0){
                                createUserList.innerHTML = '(ВСЕ)';
                            }else{
                                for(var key in params.users){
                                    if(config.actors_prof_cat.includes(params.users[key].userWithProf.userProfession.prof.proff_cat_id)){
                                        userListArr[userListArr.length] = params.users[key].userWithProf.surname +(params.users[key].userWithProf.show_full_name == 1?" " + params.users[key].userWithProf.name:"");
                                    }
                                }
                            }
                            if(userListArr.length){
                                createUserList.innerHTML = userListArr.join(', ');
                            }
                            createContainer.append(createUserList);
                            
                            var createProfCat = document.createElement('div');
                            createProfCat.className = 'three--prof-cat-cell';
                            if (params.profCat && params.profCat.length) {
                                var profCatArr = [];
                                for (var k = 0; k < params.profCat.length; k++) {
                                    // Используем конфиг для скрытия служб
                                    if(!config.hide_prof_cat.includes(params.profCat[k].profCat.id)){
                                        profCatArr[profCatArr.length] = params.profCat[k].profCat.alias;
                                    }
                                }
                                createProfCat.innerHTML = profCatArr.join(', ');
                            }
                            createContainer.append(createProfCat);
                            
                            createContainer.append(returnHR());

                            var eventsCell = roomsCell[z].getElementsByClassName('event-cell');
                            if (!eventsCell.length) {
                                roomsCell[z].append(createContainer);
                                return true;
                            } else {
                                var p = false;
                                for (var k = 0; k < eventsCell.length; k++) {
                                    if (!p && +params.timeFrom <= +eventsCell[k].dataset.timeFrom) {
                                        roomsCell[z].insertBefore(createContainer, eventsCell[k]);
                                        return true;
                                    }
                                    if (p && +params.timeFrom <= +eventsCell[k].dataset.timeFrom &&
                                            +params.timeFrom > +p.dataset.timeFrom) {
                                        roomsCell[z].insertBefore(createContainer, eventsCell[k]);
                                        return true;
                                    }
                                    p = eventsCell[k];
                                }
                                if (p && +params.timeFrom > +p.dataset.timeFrom) {
                                    roomsCell[z].append(createContainer);
                                    return true;
                                } else if (p && +params.timeFrom <= +p.dataset.timeFrom) {
                                    roomsCell[z].insertBefore(createContainer, p);
                                    return true;
                                }
                            }

                        }
                    }
                }
            }
        }
        
        function updateUserListInEvent(users, eventId){
            var eventCells = document.getElementsByClassName('event-cell');
            for(var i = 0; i < eventCells.length; i++){
                if(+eventCells[i].dataset.id == +eventId){
                    var userListArr = [];
                    var adminListArr = [];
                    var createUserList = document.createElement('div');
                    var userListDiv = eventCells[i].getElementsByClassName('three--user-actors-list')[0];
                    var adminListDiv = eventCells[i].getElementsByClassName('three--user-admin-list')[0];
                    userListDiv.innerHTML = '';
                    adminListDiv.innerHTML = '';
                    for(var key in users){
                        if(+users[key].userWithProf.userProfession.prof.proff_cat_id == 8){
                            userListArr[userListArr.length] = users[key].userWithProf.surname +(users[key].userWithProf.show_full_name == 1?" " + users[key].userWithProf.name:"");
                        }else if(['5', '16', '11', '14'].includes(users[key].userWithProf.userProfession.prof.proff_cat_id)){
                            adminListArr[adminListArr.length] = users[key].userWithProf.surname +(users[key].userWithProf.show_full_name == 1?" " + users[key].userWithProf.name:"");
                        }
                    }
                    if(userListArr.length){
                        userListDiv.innerHTML = "(" +userListArr.join(', ') +")";
                    }
                    if(adminListArr.length){
                        adminListDiv.innerHTML = adminListArr.join(', ');
                    }
                }
            }
        }


        /**
         * Загружает расписание на неделю и рендерит в нужные ячейки
         * @param {obj} period
         */
        function loadSchedule(period) {
            goPreloader();
            var data = {
                trigger: 'load-schedule',
                period: period,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    scheduleData = result.schedule;
                    config = result.config;
                    roomSetting = result.room_setting;
                    applyRoomSetting(roomSetting);
                    console.log(result);
                    for (var key in scheduleData) {
                        var dateT = new Date(scheduleData[key].date);
                            addEventInCalendar(generateCellData(scheduleData[key]));
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        }
        loadSchedule(datePeriod);
        
        // Мероприятие на ВЕСЬ ДЕНЬ при редактировании
        var editAllDay = 0;
        $('#edit--all-day').click(function(){
            if($(this).prop('checked')){
                editAllDay = 1;
                $('#edit--time_from').prop('disabled', true);
                $('#edit--time_to').prop('disabled', true);
                $('#edit--time_from').val('00:00');
                $('#edit--time_to').val('24:00');
            }else{
                editAllDay = 0;
                $('#edit--time_from').prop('disabled', false);
                $('#edit--time_to').prop('disabled', false);
                for (var key in scheduleData) {
                    if (scheduleData[key].id == editEventId) {
                        $('#edit--time_from').val(minuteToTime(scheduleData[key].time_from));
                        if(scheduleData[key].time_to){
                            $('#edit--time_to').val(minuteToTime(scheduleData[key].time_to));
                        }else{
                            $('#edit--time_to').val('');
                        }
                        
                    }
                }
            }
        });

        // Редактирование мероприятия
        var editEventId = false;
        var editEventDate = false;
        var editEventRoom = false;
        var editModifiedEvent = 0;
        $('body').on('click', '.event-cell', function (e) {
            this.getElementsByClassName('badge')[0].classList.remove('badge-info');
            this.getElementsByClassName('badge')[0].classList.add('badge-success');
            $('#edit--time_from').val('');
            $('#edit--time_to').val('');
            editWithoutIntersect = 0;
            $('#edit--without-intersect').prop('checked', false);
            $('.three--right-save-button').slideDown(200);
            $('.three--copy-event').slideUp(200);
            $('#prof-cat-right-button-container').empty();
            $('#add-user-in-schedule-container').css({'display': 'none'});
            $('#user-in-event-right-button-container').empty();
            editEventId = this.dataset.id;
            for (var key in scheduleData) {
                if (scheduleData[key].id == editEventId) {
                    if(+scheduleData[key].time_from == 0 && scheduleData[key].time_to && +scheduleData[key].time_to == 1440){
                        editAllDay = 1;
                        $('#edit--all-day').prop('checked', true);
                        $('#edit--time_from').prop('disabled', true);
                        $('#edit--time_to').prop('disabled', true);
                    }else{
                        editAllDay = 0;
                        $('#edit--all-day').prop('checked', false);
                        $('#edit--time_from').prop('disabled', false);
                        $('#edit--time_to').prop('disabled', false);
                    }
                    $('#edit--time_from').val(normalizeTime(minuteToTime(scheduleData[key].time_from)));
                    if (scheduleData[key].time_to) {
                        $('#edit--time_to').val(normalizeTime(minuteToTime(scheduleData[key].time_to)));
                    }
                    $('#edit--add-info').val(scheduleData[key].add_info);
                    var dateT = new Date(scheduleData[key].date);
                    editEventDate = {day: dateT.getDate(), month: dateT.getMonth(), year: dateT.getFullYear()};
                    editEventRoom = scheduleData[key].room_id;

                    $('#three--right-more-meta').html(normalizeDate(dateT.getDate() + "." + dateT.getMonth() + "." + dateT.getFullYear()) +
                            ", " + minuteToTime(scheduleData[key].time_from) +
                            " / " + (scheduleData[key].event !== null ? scheduleData[key].event.name : '') +" (" + scheduleData[key].eventType.name + ")");
                    
                    $('#select-edit-event-type').val(scheduleData[key].eventType.id);
                    
                    if(scheduleData[key].event != null){
                        $('#select-edit-event-category').val(scheduleData[key].event.category_id);
                        $('#select-edit-event').val(scheduleData[key].event.id);
                        $('#edit--without-event').prop('checked', false);
                        $('#select-edit-event-category').attr('disabled', false);
                        $('#select-edit-event').prop('disabled', false);
                    }else{
                        $('#edit--without-event').prop('checked', true);
                        $('#select-edit-event-category').attr('disabled', true);
                        $('#select-edit-event').prop('disabled', true);
                    }
                    
                    if(scheduleData[key].profCat){
                        addRightProfCatButton(scheduleData[key].profCat);
                    }
                    if(+scheduleData[key].is_modified > 0){
                        editModifiedEvent = 1;
                        $('#edit--modified-event').prop('checked', true);
                    }else{
                        editModifiedEvent = 0;
                        $('#edit--modified-event').prop('checked', false);
                    }
                    if(+scheduleData[key].is_all > 0){
                        $('#edit--is-all').prop('checked', true);
                    }else{
                        $('#edit--is-all').prop('checked', false);
                    }
                }
            }
            loadUserInEvent(editEventId);
            $('.three--right-more').removeClass('zoomOutRight').addClass('zoomInRight animated').css({'display': 'block'});
        });
        
        $('#edit--without-event').click(function(){
            if($(this).prop('checked')){
                $('#select-edit-event-category').prop('disabled', true);
                $('#select-edit-event').prop('disabled', true);
            }else{
                $('#select-edit-event-category').prop('disabled', false);
                $('#select-edit-event').prop('disabled', false);
            }
        });

        $('#three--right-more-close').click(function () {
            $('.three--right-more').removeClass('zoomInRight').addClass('zoomOutRight');
            $('.three--right-save-button').slideDown(200);
            $('.three--copy-event').slideUp(200);
            $('#prof-cat-right-button-container').empty();
            $('#add-user-in-schedule-container').css({'display': 'none'});
            $('#user-in-event-right-button-container').empty();
            editWithoutIntersect = 0;
            $('#edit--without-intersect').prop('checked', false);
        });

        // Загружает всех пользователей на выбранном мероприятии
        var usersInEvent = false;
        function loadUserInEvent(eventId) {
            goPreloader();
            var data = {
                trigger: 'load-user-in-schedule',
                event: eventId,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    usersInEvent = JSON.parse(data);
                    console.log(usersInEvent);

                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        }

        $('#add-prof-categories').click(function () {
            $('#profCatModal').modal('show');
        });

        // Выделяем службы для добавления в мероприятие
        var selectedProfCat = [];
        $('.add-prof-cat-item').click(function () {
            if (this.classList.contains('selected')) {
                this.classList.remove('selected');
                var idx = selectedProfCat.indexOf(this.dataset.id);
                selectedProfCat.splice(idx, 1);
            } else {
                this.classList.add('selected');
                selectedProfCat[selectedProfCat.length] = this.dataset.id;
            }
//            console.log(selectedProfCat);
        });

        $('#profCatModal').on('hidden.bs.modal', function (e) {
            $('.add-prof-cat-item').removeClass('selected');
            selectedProfCat = [];
        });
        
        //  Добавление новых служб к мероприятию
        $('#add-prof-cat-submit').click(function(){
            for (var key in scheduleData) {
                if (scheduleData[key].id == editEventId) {
                    if(scheduleData[key].profCat){
                        for(var keyProf in scheduleData[key].profCat){
                            for(var k in selectedProfCat){
                                if(+selectedProfCat[k] === +scheduleData[key].profCat[keyProf].profCat.id){
                                    showNotifications('Кажется, одна из добавляемых служб уже заявлена на мероприятие', 3000, NOTIF_RED);
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
            goPreloader();
            var data = {
                trigger: 'add-prof-cat-in-event',
                event: editEventId,
                profCat: selectedProfCat
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if(result.response == 'ok'){
                        for (var key in scheduleData) {
                            if(+scheduleData[key].id == +result.result.id){
                                var eventCells = document.getElementsByClassName('event-cell');
                                for(var i = 0; i < eventCells.length; i++){
                                    if(+eventCells[i].dataset.id == +result.result.id){
                                        eventCells[i].remove();
                                    }
                                }
                                scheduleData[key].profCat = result.result.profCat;
//                                var dateT = new Date(scheduleData[key].date);
//                                var cellData = {
//                                    id: scheduleData[key].id,
//                                    date: {
//                                        day: dateT.getDate(),
//                                        month: dateT.getMonth(),
//                                        year: dateT.getFullYear()
//                                    },
//                                    room: scheduleData[key].room_id,
//                                    eventType: scheduleData[key].eventType.name,
//                                    eventTypeId: scheduleData[key].eventType.id,
//                                    eventName: (scheduleData[key].event !== null ? scheduleData[key].event.name : ''),
//                                    eventOtherName: (scheduleData[key].event !== null && scheduleData[key].event.other_name !== null ? scheduleData[key].event.other_name : ''),
//                                    timeFrom: scheduleData[key].time_from,
//                                    timeTo: (scheduleData[key].time_to !== null ? scheduleData[key].time_to : ''),
//                                    profCat: scheduleData[key].profCat
//                                };
                                addEventInCalendar(generateCellData(scheduleData[key]));
                                $('#prof-cat-right-button-container').empty();
                                addRightProfCatButton(scheduleData[key].profCat);
                                $('#profCatModal').modal('hide');
                            }
                        }
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        var selectedShowProfCat = false;
        $('#prof-cat-right-button-container').on('click', 'div', function(){
            $('#user-in-event-right-button-container').empty();
            $('#add-user-in-schedule-container').css({'display': 'block'});
            $('#user-in-event-right-button-container').css({'display': 'block'});
            selectedShowProfCat = this.dataset.id;
            $('#prof-cat-right-button-container div').removeClass('btn-info');
            $('#prof-cat-right-button-container div').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-info');
//            console.log(usersInEvent);
//alert(selectedShowProfCat);
            for(var i = 0; i < usersInEvent.length; i++){
                if(+usersInEvent[i].userWithProf.userProfession.prof.proff_cat_id == +selectedShowProfCat){
//                    alert('k');
                    var createContainer = document.createElement('div');
                    createContainer.className = 'cursor-pointer';
                    createContainer.innerHTML = usersInEvent[i].userWithProf.surname +" " +usersInEvent[i].userWithProf.name +" <span class='badge badge-pill badge-danger three--remove-in-schedule'><i class='fas fa-times'></i></span>";
                    createContainer.dataset.userInSchedule = usersInEvent[i].id;
                    createContainer.dataset.userId = usersInEvent[i].userWithProf.id;
                    document.getElementById('user-in-event-right-button-container').append(createContainer);
                }
            }
        });
        
        // Удаление службы с мероприятия
        var deletedProfCat = false;
        $('body').on('click', '.three--remove-prof-cat-button', function(){
            deletedProfCat = this.parentNode.dataset.id;
            $('#deleteProfCatModal').modal('show');
        });
        
        $('#delete-prof-cat-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'delete-prof-cat',
                profCat: deletedProfCat,
                eventSchedule: editEventId,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        for (var key in scheduleData) {
                            if(+scheduleData[key].id == +result.result.id){
                                var eventCells = document.getElementsByClassName('event-cell');
                                for(var i = 0; i < eventCells.length; i++){
                                    if(+eventCells[i].dataset.id == +result.result.id){
                                        eventCells[i].remove();
                                    }
                                }
                                scheduleData[key].profCat = result.result.profCat;
                                scheduleData[key].allUsersInEvent = result.result.allUsersInEvent;
                                var dateT = new Date(scheduleData[key].date);
                                var cellData = {
                                    id: scheduleData[key].id,
                                    date: {
                                        day: dateT.getDate(),
                                        month: dateT.getMonth(),
                                        year: dateT.getFullYear()
                                    },
                                    room: scheduleData[key].room_id,
                                    eventType: scheduleData[key].eventType.name,
                                    eventTypeId: scheduleData[key].eventType.id,
                                    eventName: (scheduleData[key].event !== null ? scheduleData[key].event.name : ''),
                                    eventOtherName: (scheduleData[key].event !== null && scheduleData[key].event.other_name !== null ? scheduleData[key].event.other_name : ''),
                                    timeFrom: scheduleData[key].time_from,
                                    timeTo: (scheduleData[key].time_to !== null ? scheduleData[key].time_to : ''),
                                    profCat: scheduleData[key].profCat,
                                    users: scheduleData[key].allUsersInEvent
                                };
                                usersInEvent = result.result.allUsersInEvent;
//                                for(var i = 0; i < usersInEvent.length; i++){
//                                    if(+usersInEvent[i].userWithProf.userProfession.prof.proff_cat_id == +deletedProfCat){
//                                        usersInEvent.splice(i, 1);
//                                    }
//                                }
                                console.log(usersInEvent);
                                addEventInCalendar(cellData);
                                $('#prof-cat-right-button-container').empty();
                                addRightProfCatButton(scheduleData[key].profCat);
                                $('#add-user-in-schedule-container').css({'display': 'none'});
                                $('#user-in-event-right-button-container').css({'display': 'none'});
                                $('#profCatModal').modal('hide');
                            }
                        }
                        $('#deleteProfCatModal').modal('hide');
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        $('#add-user-in-schedule-button').click(function(){
            // Для актеров доступен функционал поиска состава
            for (var key in scheduleData) {
                if (+scheduleData[key].id == +editEventId) {
                    if(scheduleData[key].event != null && config.actors_prof_cat.includes(selectedShowProfCat)){
                        document.getElementById('search-cast').style.display = 'block';
                    }else{
                        document.getElementById('search-cast').style.display = 'none';
                    }
                }
            }
            var literContainers = document.getElementsByClassName('user-modal-liter-container');
            $('.user-modal-liter-container').css({'display': 'block'});
            for(var i = 0; i < literContainers.length; i++){
                var usersList = literContainers[i].getElementsByTagName('div');
                for(var z = 0; z < usersList.length; z++){
                    if(+selectedShowProfCat == +usersList[z].dataset.profCat){
                        usersList[z].style.display = 'block';
                    }else{
                        if(!usersList[z].classList.contains('liter-key')){
                            usersList[z].style.display = 'none';
                        }
                    }
                }
            }
            for(var i = 0; i < literContainers.length; i++){
                var usersList = literContainers[i].getElementsByTagName('div');
                var k = 1;
                for(var z = 0; z < usersList.length; z++){
                    if(usersList[z].style.display == 'none'){
                        k++;
                    }
                }
                if(k == usersList.length){
                    literContainers[i].style.display = 'none';
                }
            }
            var allUsersList = document.getElementsByClassName('actor-list-item');
            for(var z = 0; z < allUsersList.length; z++){
                allUsersList[z].classList.remove('set');
            }
            for(var i = 0; i < usersInEvent.length; i++){
                for(var z = 0; z < allUsersList.length; z++){
                    if(+usersInEvent[i].userWithProf.id == allUsersList[z].dataset.id){
                        allUsersList[z].classList.add('set');
                    }
                }
            }
            $('#usersListModal').modal('show');
        });
        
        // Выделение юзеров в списке юзеров
        var selectedUsers = [];
        $('.actor-list-item').click(function () {
            var actorId = this.dataset.id;
            if(this.classList.contains('selected')){
                this.classList.remove('selected');
                var idx = selectedUsers.indexOf(actorId);
                selectedUsers.splice(idx, 1);
            }else{
                this.classList.add('selected');
                selectedUsers[selectedUsers.length] = actorId;
            }
            console.log(selectedUsers);
        });
        
        $('#usersListModal').on('hide.bs.modal', function (e) {
            selectedUsers = [];
            $('.actor-list-item').removeClass('selected');
        });
        
        $('#add-user-list-submit').click(function(){
            if(selectedUsers.length === 0){
                showNotifications('Не выбран ни один сотрудник', 3500, NOTIF_RED);
                return false;
            }
            for(var i = 0; i < usersInEvent.length; i++){
                if(selectedUsers.includes(usersInEvent[i].userWithProf.id)){
                    showNotifications('Похоже, что один из добавляемых сотрудников уже стоит в этом мероприятии', 3500, NOTIF_RED);
                    return false;
                }
            }
            goPreloader();
            var data = {
                trigger: 'add-user-in-schedule',
                profCat: selectedShowProfCat,
                eventSchedule: editEventId,
                users: selectedUsers
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        usersInEvent = result.result;
                        $('#user-in-event-right-button-container').empty();
                        $('#user-in-event-right-button-container').css({'display': 'block'});
                        for(var i = 0; i < usersInEvent.length; i++){
                            if(+usersInEvent[i].userWithProf.userProfession.prof.proff_cat_id === +selectedShowProfCat){
                                var createContainer = document.createElement('div');
                                createContainer.className = 'cursor-pointer';
                                createContainer.innerHTML = usersInEvent[i].userWithProf.name +" " +usersInEvent[i].userWithProf.surname +" <span class='badge badge-pill badge-danger three--remove-in-schedule'><i class='fas fa-times'></i></span>";
                                createContainer.dataset.userInSchedule = usersInEvent[i].id;
                                createContainer.dataset.userId = usersInEvent[i].userWithProf.id;
                                document.getElementById('user-in-event-right-button-container').append(createContainer);
                            }
                        }
                        updateUserListInEvent(usersInEvent, result.event_schedule);
                        $('#usersListModal').modal('hide');
                    }else if(result.result == 'intersect'){
                        var textNotification = '';
                        for(var key in result.data){
                            if(result.data[key].time_to){
                                textNotification += "Конфликт. "+ result.data[key].user_name +" " + result.data[key].surname +" уже стоит на \n\
                                    "+ (result.data[key].name?result.data[key].name:"другом мероприятии") +" с "+ minuteToTime(result.data[key].time_from) +" до "+ minuteToTime(result.data[key].time_to);
                            }else{
                                textNotification += "Конфликт. Данный сотрудник уже стоит на \n\
                                    "+ (result.data[key].name?result.data[key].name:"другом мероприятии") +" в "+ minuteToTime(result.data[key].time_from);
                            }
                        }
                        showNotifications(textNotification, 7000, NOTIF_RED);
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        $('#search-cast').click(function(){
            goPreloader();
            var searchEvent = false;
            for (var key in scheduleData) {
                if (+scheduleData[key].id == +editEventId) {
                    searchEvent = scheduleData[key].event.id;
                }
            }
            var data = {
                trigger: 'search-cast',
                month: (nowDate.getMonth()) +1,
                year: nowDate.getFullYear(),
                event: searchEvent
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if(result.response == 'ok'){
                        var usersList = document.getElementsByClassName('actor-list-item');
                        selectedUsers = [];
                        for(var i = 0; i < usersList.length; i++){
                            usersList[i].classList.remove('selected');
                        }
                        for(var i = 0; i < result.data.length; i++){
                            for(var z = 0; z < usersList.length; z++){
                                if(+usersList[z].dataset.id == +result.data[i]){
                                    usersList[z].classList.add('selected');
                                    selectedUsers[selectedUsers.length] = result.data[i];
                                }
                            }
                        }
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        var deletedUserInSchedule = false;
        $('#user-in-event-right-button-container').on('click', '.three--remove-in-schedule', function(){
            deletedUserInSchedule = this.parentNode.dataset.userInSchedule;
            $('#deleteInScheduleModal').modal('show');
        });
        
        $('#delete-in-schedule-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'delete-in-schedule',
                userInSchedule: deletedUserInSchedule
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if(result.response == 'ok'){
                        for(var i = 0; i < usersInEvent.length; i++){
                            if(+usersInEvent[i].id == +deletedUserInSchedule){
                                usersInEvent.splice(i, 1);
                            }
                        }
                        $('#user-in-event-right-button-container').empty();
                        for(var i = 0; i < usersInEvent.length; i++){
                            if(+usersInEvent[i].userWithProf.userProfession.prof.proff_cat_id === +selectedShowProfCat){
                                var createContainer = document.createElement('div');
                                createContainer.className = 'cursor-pointer';
                                createContainer.innerHTML = usersInEvent[i].userWithProf.name +" " +usersInEvent[i].userWithProf.surname +" <span class='badge badge-pill badge-danger three--remove-in-schedule'><i class='fas fa-times'></i></span>";
                                createContainer.dataset.userInSchedule = usersInEvent[i].id;
                                createContainer.dataset.userId = usersInEvent[i].userWithProf.id;
                                document.getElementById('user-in-event-right-button-container').append(createContainer);
                            }
                        }
                        updateUserListInEvent(result.result, result.event_schedule);
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                    $('#deleteInScheduleModal').modal('hide');
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        // Игнорировать проверку на пересечения
        var editWithoutIntersect = 0;
        $('#edit--without-intersect').click(function(){
            if($(this).prop('checked')){
                editWithoutIntersect = 1;
            }else{
                editWithoutIntersect = 0;
            }
        });
        
        // Отображать слово ВСЕ, вместо фамилий актеров
        var isAll = 0;
        $('#edit--is-all').click(function(){
            if($(this).prop('checked')){
                isAll = 1;
            }else{
                isAll = 0;
            }
        });
        

        $('#save--event-time').click(function(){
            var newTimeFrom = $('#edit--time_from').val();
            var newTimeTo = $('#edit--time_to').val();
            var addInfo = $('#edit--add-info').val();
            var eventType = $('#select-edit-event-type').val();
            var eventId = $('#select-edit-event').val();
            var editWithoutEvent = false;
            if($('#edit--without-event').prop('checked')){
                editWithoutEvent = 1;
            }else{
                editWithoutEvent = 0;
            }
            if (!newTimeFrom) {
                showNotifications('Кажется вы не указали время начала мероприятия', 7000, NOTIF_RED);
                return false;
            }
            if(editWithoutIntersect == 0){
                if (!checkTimesInterval(timeToMinute(newTimeFrom), timeToMinute(newTimeTo), editEventDate, editEventRoom, editEventId)) {
                    showNotifications("Изменяемое мероприятие пересекается с другими в этот день", 3000, NOTIF_RED);
                    return false;
                }
            }
            if($('#edit--modified-event').prop('checked')){
                editModifiedEvent = 1;
            }else{
                editModifiedEvent = 0;
            }
            goPreloader();
            var data = {
                trigger: 'edit-event',
                id: editEventId,
                timeFrom: newTimeFrom,
                timeTo: newTimeTo,
                addInfo: addInfo,
                eventType: eventType,
                eventId: eventId,
                withoutEvent: editWithoutEvent,
                modifiedEvent: editModifiedEvent,
                isAll: isAll,
                allDay: editAllDay
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        deleteEventInCalendar(editEventId);
                        scheduleData[scheduleData.length] = result.data;
                        addEventInCalendar(generateCellData(result.data));
//                        $('#editEventModal').modal('hide');
                    } else if(result.response == 'intersect'){
                        var textNotification = '';
                        for(var key in result.data){
                            textNotification += "Конфликт! "+ result.data[key].user_name +" " +result.data[key].surname +" стоит на \n\
                                "+ (result.data[key].name?result.data[key].name:"другом мероприятии") +" в это время";
                        }
                        showNotifications(textNotification, 7000, NOTIF_RED);
                    }else if(result.response == 'error'){
                        showNotifications(result.data, 8000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        $('#copy--event').click(function(){
            $('.three--right-save-button').slideUp(200);
            $('.three--copy-event').slideDown(200);
        });
        
        function updateCopyField(){
            var firstDate = new Date(datePeriod[0].year +"-" +datePeriod[0].month +"-" +datePeriod[0].day);
            var lastDate = new Date(datePeriod[1].year +"-" +datePeriod[1].month +"-" +datePeriod[1].day);
            $('#copy--select-date').empty();
            for(var i = 0; i < 14; i++){
                var createOption = document.createElement('option');
                createOption.dataset.day = firstDate.getDate();
                createOption.dataset.month = (firstDate.getMonth() +1);
                createOption.dataset.year = firstDate.getFullYear();
                createOption.innerHTML = firstDate.getDate() +" " +monthNameDec[firstDate.getMonth()] +" " +firstDate.getFullYear();
                document.getElementById('copy--select-date').append(createOption);
                firstDate.setDate(firstDate.getDate()+1);
            }
            var roomsSelect = document.getElementById('copy--select-room');
            for (var i = 0; i < roomsSelect.options.length; i++) {
                if (+roomsSelect.options[i].value == +editEventRoom) {
                    roomsSelect.options[i].selected = true;
                }
            }
        }
        
        updateCopyField();
        
        $('#copy--cancel-copy').click(function(){
            $('.three--right-save-button').slideDown(200);
            $('.three--copy-event').slideUp(200);
        });
        
        // Копирование мероприятия
        $('#copy--save-copy').click(function(){
            var room = $('#copy--select-room').val();
            var addInfo = $('#edit--add-info').val();
            var eventType = $('#select-edit-event-type').val();
            var eventId = $('#select-edit-event').val();
            var copyIsAll = false;
            if($('#edit--is-all').prop('checked')){
                copyIsAll = 1;
            }else{
                copyIsAll = 0;
            }
            var editWithoutEvent = false;
            if($('#edit--without-event').prop('checked')){
                editWithoutEvent = 1;
            }else{
                editWithoutEvent = 0;
            }
            var moveUsers = false;
            var date = {
                day: $('#copy--select-date').find(':selected').attr('data-day'),
                month: (+$('#copy--select-date').find(':selected').attr('data-month') - 1),
                year: $('#copy--select-date').find(':selected').attr('data-year')
            };
            if($('#copy--checkbox-move-users').prop('checked')){
                moveUsers = 1;
            }else{
                moveUsers = 0;
            }
            var newTimeFrom = $('#edit--time_from').val();
            var newTimeTo = $('#edit--time_to').val();
            if (!newTimeFrom) {
                showNotifications('Кажется вы не указали время начала мероприятия', 7000, NOTIF_RED);
                return false;
            }
//            alert(editEventId);
//            return false;
//            if(editWithoutIntersect == 0){
//                if (!checkTimesInterval(timeToMinute(newTimeFrom), timeToMinute(newTimeTo), date, room)) {
//                    showNotifications("Изменяемое мероприятие пересекается с другими в этот день", 3000, NOTIF_RED);
//                    return false;
//                }
//            }
            if($('#edit--modified-event').prop('checked')){
                editModifiedEvent = 1;
            }else{
                editModifiedEvent = 0;
            }
            goPreloader();
            var data = {
                trigger: 'copy-event',
                id: editEventId,
                date: date,
                room: room,
                addInfo: addInfo,
                eventType: eventType,
                eventId: eventId,
                withoutEvent: editWithoutEvent,
                timeFrom: newTimeFrom,
                timeTo: newTimeTo,
                moveUsers: moveUsers,
                modifiedEvent: editModifiedEvent,
                isAll: copyIsAll,
                withoutIntersect: editWithoutIntersect
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        scheduleData[scheduleData.length] = result.result;
                        addEventInCalendar(generateCellData(result.result));
                    }else if(result.response == 'intersect'){
                        var textNotification = '';
                        for(var key in result.data){
                            textNotification += "Конфликт! "+ result.data[key].user_name +" " +result.data[key].surname +" стоит на \n\
                                "+ (result.data[key].name?result.data[key].name:"другом мероприятии") +" в это время";
                        }
                        showNotifications(textNotification, 7000, NOTIF_RED);
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                        
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        // Удаление мероприятия
        $('#delete--event').click(function(){
           $('#deleteEventModal').modal('show');
        });
        
        $('#delete-event-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'delete-event',
                id: editEventId,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        deleteEventInCalendar(editEventId);
                        $('#deleteEventModal').modal('hide');
                        $('.three--right-more').removeClass('zoomInRight').addClass('zoomOutRight');
                        $('#prof-cat-right-button-container').empty();
                        $('#add-user-in-schedule-container').css({'display': 'none'});
                        $('#user-in-event-right-button-container').empty();
                        showNotifications("Мероприятие удалено", 2000, NOTIF_GREEN);
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                        
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });

        $('#generate-link').click(function(){
            // alert(location.hostname);
            // return false;
            goPreloader();
            var data = {
                trigger: 'generate-link',
                period: datePeriod,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        $('#generate-link-container').html(result.result);
                        $('#generate-link-container').attr('href', result.result);
                        $('#generateLinkModal').modal('show');
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 4000, NOTIF_RED);
                    }
                        
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
            // console.log(datePeriod);
        });
        
        // Применяем настройку отображения залов
        function applyRoomSetting(roomSetting){
            if(!roomSetting.length){
                $('.room').css({'display': 'block'});
            }else{
                $('.room').css({'display': 'block'});
                var roomCells = document.getElementsByClassName('room');
                for(var i = 0; i < roomCells.length; i++){
                    if(!roomSetting.includes(roomCells[i].dataset.room)){
                        roomCells[i].style.display = 'none';
                    }
                }
            }
        }
        
        $('#room-setting').click(function(){
            var settingItems = document.getElementsByClassName('room-setting-item');
            if(!roomSetting.length){
                for(var i = 0; i < settingItems.length; i++){
                    settingItems[i].classList.add('selected');
                }
            }else{
                for(var i = 0; i < settingItems.length; i++){
                    if(roomSetting.includes(settingItems[i].dataset.room)){
                        settingItems[i].classList.add('selected');
                    }else{
                        settingItems[i].classList.remove('selected');
                    }
                }
            }
            
            
           $('#roomSettingModal').modal('show'); 
        });
        
        $('.room-setting-item').click(function(){
           if(this.classList.contains('selected')){
               this.classList.remove('selected');
           }else{
               this.classList.add('selected');
           }
        });
        
        $('#room-setting-submit').click(function(){
            var roomIds = [];
            var settingItems = document.getElementsByClassName('room-setting-item');
            for(var i = 0; i < settingItems.length; i++){
                if(settingItems[i].classList.contains('selected')){
                    roomIds[roomIds.length] = settingItems[i].dataset.room
                }
            }
            goPreloader();
            var data = {
                trigger: 'set-room-config',
                period: datePeriod,
                roomIds: roomIds
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/three',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        roomSetting = result.result;
                        applyRoomSetting(roomSetting);
                        $('#roomSettingModal').modal('hide');
                    }else if(result.response == 'error'){
                        showNotifications(result.result, 5000, NOTIF_RED);
                    }
                        
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        // Копирование ссылки для просмотра расписания в буфер обмена
        var copyEmailBtn = document.querySelector('#copy-link-button');
        copyEmailBtn.addEventListener('click', function(event) {
          // Select the email link anchor text
          var emailLink = document.querySelector('#generate-link-container');
          var range = document.createRange();
          range.selectNode(emailLink);
          window.getSelection().addRange(range);

          try {
            // Now that we've selected the anchor text, execute the copy command
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            console.log('Copy email command was ' + msg);
          } catch(err) {
            console.log('Oops, unable to copy');
          }

          // Remove the selections - NOTE: Should use
          // removeRange(range) when it is supported
          window.getSelection().removeAllRanges();
          showNotifications('Ссылка скопирована в буфер обмена', 3000, NOTIF_GREEN);
        });
        
        $('.clean-input').click(function () {
            this.parentNode.parentNode.querySelector('input').value = '';
        });
        // Добавляет кнопки служб в правое меню
        function addRightProfCatButton(obj){
            for(var keyProf in obj){
                var createButton = document.createElement('div');
                createButton.dataset.id = obj[keyProf].profCat.id;
                createButton.className = 'btn btn-sm btn-outline-secondary ml-1';
                createButton.innerHTML = obj[keyProf].profCat.alias + " <span class='badge badge-danger three--remove-prof-cat-button'><i class='fas fa-times'></i></span>";
                document.getElementById('prof-cat-right-button-container').append(createButton);
            }
        }
        
        // Удаление из календаря
        function deleteEventInCalendar(eventId) {
            var eventCell = document.getElementsByClassName('event-cell');
            for (var i = 0; i < eventCell.length; i++) {
                if (eventId == eventCell[i].dataset.id) {
                    eventCell[i].remove();
                    break;
                }
            }
            removeInScheduleData(eventId);
            return true;
        }

        // Удаление из массива данных
        function removeInScheduleData(eventId) {
            for (var key in scheduleData) {
                if (scheduleData[key].id == eventId) {
                    scheduleData.remove(key);
                    break;
                }
            }
            return true;
        }

        /**
         * Переводит дату формата 3.6.2019 в 3.07.2019
         * @param {string} date
         * @returns {String}
         */
        function normalizeDate(date) {
            var splitDate = date.split(".");
            return splitDate[0] + "." + (+splitDate[1] >= 0 && +splitDate[1] < 9 ? "0" + (+splitDate[1] + 1) : (+splitDate[1] + 1)) + "." + splitDate[2];
        }

        /**
         * Переводит минуты во время
         * @param {int} minute
         * @returns {String}
         */
        function minuteToTime(minute) {
            if (minute == 0) {
                return "0:0";
            } else {
                return normalizeTime(returnFloor(minute / 60) + ":" + minute % 60);
            }
        }

        /**
         * Переводит время в минуты
         * @param {string} time
         * @returns {Number}
         */
        function timeToMinute(time) {
            if (!time)
                return false;
            return +time.split(":")[0] * 60 + +time.split(":")[1];
        }

        /**
         * Преобразует время формата 8:0 в 8:00
         * @param {string} time
         * @returns {String}
         */
        function normalizeTime(time) {
            var splitTime = time.split(":");
            return (+splitTime[0] >= 0 && +splitTime[0] < 10 ? "0" + +splitTime[0] : +splitTime[0]) + ":" + (+splitTime[1] >= 0 && +splitTime[1] < 10 ? "0" + +splitTime[1] : +splitTime[1]);
        }

        // Округляет в меньшую сторону
        function returnFloor(val) {
            return Math.floor(val);
        }

        function returnHR() {
            var createBR = document.createElement('hr');
            return createBR;
        }
        

    }
</script>


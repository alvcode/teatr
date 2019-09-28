<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Сводное расписание';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>

            <?= $this->render('../templates/_flash') ?>

        </div>
    </div>
    <div class="one--schedule-container">

        <div id="one--schedule-content">
            <div class="schedule-controls">
                <div id="control-name" class="name"></div>
                <div class="arrow-left"><i id="month-left" class="fas fa-arrow-circle-left cursor-pointer" aria-hidden="true"></i></div>
                <div class="arrow-right"><i id="month-right" class="fas fa-arrow-circle-right cursor-pointer" aria-hidden="true"></i></div>
            </div>
            <div class="one--title-row mrg-top15">
                <div class="date">Дата</div>
                <?php foreach ($rooms as $key => $value): ?>
                    <div data-room="<?= $value['id'] ?>" class="room"><?= $value['name'] ?></div>
                <?php endforeach; ?>
            </div>
            <div id="one--schedule-items"></div>
        </div>
    </div>
</div>
<br>

<?php // echo "<pre>";print_r($events); ?>


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
                                    <label class="form-check-label" for="add--modified-event">Измененное!</label>
                                </div>
                                <div>
                                    <input type="checkbox" class="" id="add--without-event">
                                    <label class="form-check-label" for="add--without-event">Без мероприятия</label>
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

<!-- Modal edit event -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Изменить мероприятие</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th scope="row">Информация</th>
                            <td id="edit--meta"></td>
                        </tr>
                        <tr>
                            <th scope="row">Время начала</th>
                            <td>
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
                    </tbody>
                </table>
                <div>
                    <input type="checkbox" class="" id="edit--modified-event">
                    <label class="form-check-label" for="edit--modified-event">Измененное!</label>
                </div>
                <div class="btn btn-sm btn-danger mrg-top15" id="delete-event">Удалить мероприятие</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="edit-event-submit" type="button" class="btn btn-sm btn-success">Применить</button>
            </div>
        </div>
    </div>
</div>


<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
        if($(window).width() > 1150){
            var fixedTop = $('.one--title-row').clone();
            fixedTop.appendTo('#one--schedule-content');
            fixedTop.css({'width': fixedTop.width(), 'display': 'none'});
            fixedTop.addClass('one--fixed-top');

            $( window ).scroll(function() {
                if($(window).scrollTop() > 300){
                    $('.one--fixed-top').slideDown(200);
                }else{
                    $('.one--fixed-top').slideUp(200);
                }
            });

            $(window).resize(function() {
                $('.one--fixed-top').css({'width': $('.one--title-row:not(.one--fixed-top)').width()});
            });
        }

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

        var addNowDate = {}; // Выбранная дата
        var addNowRoom = false; // выбранный зал
        var scheduleData = false; // Все загруженные записи
        
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

        var rooms = document.querySelector('.one--title-row').getElementsByClassName('room');


        // ====== Плагин календаря
        var monthName = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        //    var monthNameTwo = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var monthNameDec = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        var nowDate = new Date();

        var weekNumber = ['1', '2', '3', '4', '5', '6', '0'];

        renderCalendar(nowDate.getFullYear(), nowDate.getMonth());

        $('#month-right').click(function () {
            nowDate.setMonth(nowDate.getMonth() + 1);
            renderCalendar(nowDate.getFullYear(), nowDate.getMonth());
            loadSchedule(nowDate.getMonth(), nowDate.getFullYear());
        });
        $('#month-left').click(function () {
            nowDate.setMonth(nowDate.getMonth() - 1);
            renderCalendar(nowDate.getFullYear(), nowDate.getMonth());
            loadSchedule(nowDate.getMonth(), nowDate.getFullYear());
        });

        function renderCalendar(year, month) {
            $('#one--schedule-items').empty();
            var date = new Date(year, month, 1);
            var dayCount = dayInMonth(year, month);
            document.getElementById('control-name').innerHTML = monthName[date.getMonth()] + ", " + date.getFullYear();

            for (var i = 1; i <= dayCount; i++) {
                date = new Date(year, month, i);
                document.getElementById('one--schedule-items').append(returnScheduleRow(date.getFullYear(), date.getMonth(), date.getDate(), date.getDay(), rooms));
            }
            return true;
        };

        function returnScheduleRow(year, month, day, week, rooms) {
            var createContainer = document.createElement('div');
            createContainer.dataset.day = day;
            createContainer.dataset.month = month;
            createContainer.dataset.year = year;
            createContainer.className = 'one--date-row';

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


        function dayInMonth(year, month) {
            var date = new Date(year, month, 1);

            for (var i = 1; i <= 33; i++) {
                if (date.getDate() != i) {
                    date.setDate(date.getDate() - 1);
                    return date.getDate();
                    break;
                }
                date.setDate(date.getDate() + 1);
            }
        }
        ;

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
        

        $('#select-event-category').change(function () {
            eventCatSort();
        });

        // Добавляем мероприятие через стандартное добавление
        $('#add-event-submit').click(function () {
            var timeFrom = $('#add--time_from').val();
            var timeTo = $('#add--time_to').val();
            var eventType = $('#select-event-type').val();
            var eventCategory = $('#select-event-category').val();
            var event = $('#select-event').val();
            if (!timeFrom || timeFrom == '') {
                showNotifications("Не выбрано время начала мероприятия", 3000, NOTIF_RED);
                return false;
            }
            if (!checkTimesInterval(timeToMinute(timeFrom), timeToMinute(timeTo), addNowDate, addNowRoom)) {
                showNotifications("Добавляемое мероприятие пересекается с другими в этот день", 3000, NOTIF_RED);
                return false;
            }
            goPreloader();
            var data = {
                trigger: 'add-schedule',
                date: addNowDate,
                room: addNowRoom,
                timeFrom: timeFrom,
                timeTo: timeTo,
                eventType: eventType,
                eventCategory: eventCategory,
                event: event,
                withoutEvent: withoutEvent,
                modifiedEvent: modifiedEvent
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/one',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
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

        /**
         * Проверяет, не пересекается ли время мероприятия с другими мероприятиями
         * @param {integer} exclude - id мероприятия, которое не должно учавствовать в проверке (используем при изменении, т.к тогда эта же запись будет участвовать и не пропускать)
         * @returns {boolean}
         */
        function checkTimesInterval(timeFrom, timeTo, date, room, exclude) {
            if(!exclude) exclude = 0;
            var dateRows = document.getElementsByClassName('one--date-row');
            for (var i = 0; i < dateRows.length; i++) {
                if (date.day == dateRows[i].dataset.day && date.month == dateRows[i].dataset.month && date.year == dateRows[i].dataset.year) {
                    var roomsCell = dateRows[i].getElementsByClassName('room-cell');
                    for (var z = 0; z < roomsCell.length; z++) {
                        if (roomsCell[z].dataset.room == room) {
                            var eventsCell = roomsCell[z].getElementsByClassName('event-cell');
                            if (eventsCell.length) {
                                for (var k = 0; k < eventsCell.length; k++) {
                                    if(+exclude != +eventsCell[k].dataset.id){
                                        if (+eventsCell[k].dataset.timeFrom == +timeFrom) {
                                            return false;
                                        }
                                        if (eventsCell[k].dataset.timeTo !== undefined && timeTo
                                                && ((+timeFrom >= +eventsCell[k].dataset.timeFrom && +timeFrom < +eventsCell[k].dataset.timeTo)
                                                        || (+timeTo > +eventsCell[k].dataset.timeFrom && +timeTo <= +eventsCell[k].dataset.timeTo))) {
                                            return false;
                                        }
                                        if (eventsCell[k].dataset.timeTo === undefined && timeTo
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
                is_modified: result.is_modified
            };
            return cellData;
        }
        ;

        /**
         * Добавляет мероприятие в календарь
         * @param {object} params
         */
        function addEventInCalendar(params) {
            var dateRows = document.getElementsByClassName('one--date-row');
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
                            createBudgie.innerHTML = minuteToTime(params.timeFrom) + (params.timeTo && params.timeTo != '' ? " - " + minuteToTime(params.timeTo) : "");

                            var createEventType = document.createElement('span');
                            createEventType.className = 'type';
                            createEventType.dataset.id = params.eventTypeId;
                            createEventType.innerHTML = "(" + params.eventType + ")";

                            var createEventName = document.createElement('span');
                            createEventName.className = 'name';
                            createEventName.innerHTML = params.eventName + (params.eventOtherName && params.eventOtherName != '' ? " (" + params.eventOtherName + ")" : "");

                            createContainer.append(createBudgie);
                            createContainer.append(createEventType);
                            createContainer.append(createEventName);
                            createContainer.append(returnHR());

                            var eventsCell = roomsCell[z].getElementsByClassName('event-cell');
                            if (!eventsCell.length) {
                                roomsCell[z].append(createContainer);
                                return true;
                            } else {
                                var p = false;
                                for (var k = 0; k < eventsCell.length; k++) {
                                    if(!p && +params.timeFrom < +eventsCell[k].dataset.timeFrom){
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
         * Загружает расписание на месяц и рендерит в нужные ячейки
         * @param {int} month
         * @param {int} year
         */
        function loadSchedule(month, year) {
            goPreloader();
            var data = {
                trigger: 'load-schedule',
                month: (month) + 1,
                year: year,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/one',
                data: data,
                success: function (data) {
                    scheduleData = JSON.parse(data);
                    console.log(scheduleData);
                    for (var key in scheduleData) {
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
                            eventOtherName: (scheduleData[key].event !== null && scheduleData[key].event.other_name !== null? scheduleData[key].event.other_name : ''),
                            timeFrom: scheduleData[key].time_from,
                            timeTo: (scheduleData[key].time_to !== null ? scheduleData[key].time_to : ''),
                            is_modified: scheduleData[key].is_modified
                        };
                        addEventInCalendar(cellData);

                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        }
        loadSchedule(nowDate.getMonth(), nowDate.getFullYear());

        // Редактирование мероприятия
        var editEventId = false;
        var editEventDate = false;
        var editEventRoom = false;
        var editModifiedEvent = 0;
        $('body').on('click', '.event-cell', function (e) {
            $('#edit--time_from').val('');
            $('#edit--time_to').val('');
            editEventId = this.dataset.id;
            for (var key in scheduleData) {
                if (scheduleData[key].id == editEventId) {
                    $('#edit--time_from').val(normalizeTime(minuteToTime(scheduleData[key].time_from)));
                    if (scheduleData[key].time_to) {
                        $('#edit--time_to').val(normalizeTime(minuteToTime(scheduleData[key].time_to)));
                    }
                    var dateT = new Date(scheduleData[key].date);
                    editEventDate = {day: dateT.getDate(), month: dateT.getMonth(), year: dateT.getFullYear()};
                    editEventRoom = scheduleData[key].room_id;
                    
                    $('#edit--meta').html(normalizeDate(dateT.getDate() + "." + dateT.getMonth() + "." + dateT.getFullYear()) +
                            " / " + (scheduleData[key].event !== null ? scheduleData[key].event.name : '') + " (" + scheduleData[key].eventType.name + ")");
                    
                    if(+scheduleData[key].is_modified > 0){
                        editModifiedEvent = 1;
                        $('#edit--modified-event').prop('checked', true);
                    }else{
                        editModifiedEvent = 0;
                        $('#edit--modified-event').prop('checked', false);
                    }
                }
            }

            $('#editEventModal').modal('show');
        });

        $('#edit-event-submit').click(function () {
            var newTimeFrom = $('#edit--time_from').val();
            var newTimeTo = $('#edit--time_to').val();
            if (!newTimeFrom) {
                showNotifications('Кажется вы не указали время начала мероприятия', 7000, NOTIF_RED);
                return false;
            }
            if (!checkTimesInterval(timeToMinute(newTimeFrom), timeToMinute(newTimeTo), editEventDate, editEventRoom, editEventId)) {
                showNotifications("Добавляемое мероприятие пересекается с другими в этот день", 3000, NOTIF_RED);
                return false;
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
                modifiedEvent: editModifiedEvent
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/one',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.response == 'ok'){
                        deleteEventInCalendar(editEventId);
                        scheduleData[scheduleData.length] = result.data;
                        addEventInCalendar(generateCellData(result.data));
                        $('#editEventModal').modal('hide');
                    } else if(result.response == 'intersect'){
                        var textNotification = '';
                        for(var key in result.data){
                            textNotification += "Конфликт! "+ result.data[key].user_name +" " +result.data[key].surname +" стоит на \n\
                                "+ (result.data[key].name?result.data[key].name:"другом мероприятии") +" в это время";
                        }
                        showNotifications(textNotification, 7000, NOTIF_RED);
                    }else if(result.response == 'error'){
                        showNotifications(result.data, 7000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });

        // Удаляет мероприятие
        $('#delete-event').click(function () {
            var isDelete = confirm("Уверены, что хотите удалить мероприятие?");
            if (isDelete) {
                goPreloader();
                var data = {
                    trigger: 'delete-event',
                    id: editEventId
                };
                data[csrfParam] = csrfToken;
                $.ajax({
                    type: "POST",
                    url: '/schedule/one',
                    data: data,
                    success: function (data) {
                        if (data == 1) {
                            deleteEventInCalendar(editEventId);
                            $('#editEventModal').modal('hide');
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
            }
        });

        $("#one--schedule-items").on("DOMNodeInserted", ".event-cell", function () {
            $(this).draggable({helper: "clone", delay: 200, });
        });
        $("#one--schedule-items").on("DOMNodeInserted", ".room-cell", function () {
            $('.room-cell').droppable({
                classes: {
//            "ui-droppable-hover": "ui-state-hover"
                },
                drop: function (event, ui) {
                    for (var key in scheduleData) {
                        if (scheduleData[key].id == ui.draggable[0].dataset.id) {
                            var dateObj = {
                                day: event.target.parentNode.dataset.day,
                                month: event.target.parentNode.dataset.month,
                                year: event.target.parentNode.dataset.year
                            };
                            var data = {
                                trigger: 'add-schedule',
                                date: dateObj,
                                room: event.target.dataset.room,
                                timeFrom: normalizeTime(minuteToTime(scheduleData[key].time_from)),
                                timeTo: (scheduleData[key].time_to !== null ? normalizeTime(minuteToTime(scheduleData[key].time_to)) : ''),
                                eventType: scheduleData[key].eventType.id,
                                event: (scheduleData[key].event !== null ? scheduleData[key].event.id : ""),
                                modifiedEvent: scheduleData[key].is_modified
                            };
                            if (!checkTimesInterval(scheduleData[key].time_from, scheduleData[key].time_to, dateObj, event.target.dataset.room)) {
                                showNotifications("Добавляемое мероприятие пересекается с другими в этот день", 3000, NOTIF_RED);
                                return false;
                            }
                            data[csrfParam] = csrfToken;
                            goPreloader();
                            $.ajax({
                                type: "POST",
                                url: '/schedule/one',
                                data: data,
                                success: function (data) {
                                    if (JSON.parse(data) != 0) {
                                        var result = JSON.parse(data);
                                        console.log(result);
                                        scheduleData[scheduleData.length] = result.result;
                                        var dateT = new Date(result.result.date);
                                        var cellData = {
                                            id: result.result.id,
                                            date: {
                                                day: dateT.getDate(),
                                                month: dateT.getMonth(),
                                                year: dateT.getFullYear()
                                            },
                                            room: result.result.room_id,
                                            eventType: result.result.eventType.name,
                                            eventTypeId: result.result.eventType.id,
                                            eventName: (result.result.event !== null ? result.result.event.name : ''),
                                            eventOtherName: (result.result.event !== null && result.result.event.other_name !== null ? result.result.event.other_name : ''),
                                            timeFrom: result.result.time_from,
                                            timeTo: (result.result.time_to !== null ? result.result.time_to : ''),
                                            is_modified: result.result.is_modified
                                        };
                                        addEventInCalendar(cellData);
                                        $('#addEventModal').modal('hide');
                                    } else {
                                        showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
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
                }
            });
        });
        
        $('.clean-input').click(function () {
            this.parentNode.parentNode.querySelector('input').value = '';
        });


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


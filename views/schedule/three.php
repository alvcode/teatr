<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Расписание на неделю';
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
    
    <div class="three--schedule-container">

        <div id="three--schedule-content">
            <div class="schedule-controls">
                <div id="control-name" class="name"></div>
                <div class="arrow-left"><i id="month-left" class="fas fa-arrow-circle-left cursor-pointer" aria-hidden="true"></i></div>
                <div class="arrow-right"><i id="month-right" class="fas fa-arrow-circle-right cursor-pointer" aria-hidden="true"></i></div>
            </div>
            <div class="three--title-row mrg-top15">
                <div class="date">Дата</div>
                <?php foreach ($rooms as $key => $value): ?>
                    <div data-room="<?= $value['id'] ?>" class="room"><?= $value['name'] ?></div>
                <?php endforeach; ?>
            </div>
            <div id="three--schedule-items"></div>
        </div>
    </div>
    
    

</div>
<br>

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
        </div>
    </div>
</div>


<!-- Modal edit event -->
<!--<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Информация о мероприятии</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="btn btn-sm btn-danger" id="delete-event">Удалить мероприятие</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="edit-event-submit" type="button" class="btn btn-sm btn-success">Применить</button>
            </div>
        </div>
    </div>
</div>-->

<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

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
        
         $('#add--time_from, #edit--time_from').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'HH:mm'
        });
        $('#add--time_to, #edit--time_to').bootstrapMaterialDatePicker({
            date: false,
            shortTime: false,
            format: 'HH:mm'
        });
        
        var rooms = document.querySelector('.three--title-row').getElementsByClassName('room');
        
        // ====== Плагин календаря
        var monthName = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        //    var monthNameTwo = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var monthNameDec = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        var nowDate = new Date();
        
        var scheduleData = false;
        var datePeriod = {};

        var weekNumber = ['1', '2', '3', '4', '5', '6', '0'];

        renderCalendar(nowDate);

        $('#month-right').click(function () {
            nowDate.setDay(1);
            nowDate.setDate(nowDate.getDate() + 7);
            renderCalendar(nowDate);
            loadSchedule(datePeriod);
        });
        $('#month-left').click(function () {
            nowDate.setDay(1);
            nowDate.setDate(nowDate.getDate() - 7);
            renderCalendar(nowDate);
            loadSchedule(datePeriod);
        });

        function renderCalendar(dateObj) {
            $('#three--schedule-items').empty();
            dateObj.setDay(1);
            document.getElementById('control-name').innerHTML = dateObj.getDate() +" " +monthNameDec[dateObj.getMonth()];
            datePeriod[0] = {};
            datePeriod[0].day = dateObj.getDate();
            datePeriod[0].month = (dateObj.getMonth() + 1);
            datePeriod[0].year = dateObj.getFullYear();

            for (var i = 0; i < 7; i++) {
                dateObj.setFullYear(dateObj.getFullYear());
                dateObj.setMonth(dateObj.getMonth());
                if(i > 0){
                    dateObj.setDate(dateObj.getDate() + 1);
                }
                document.getElementById('three--schedule-items').append(returnScheduleRow(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), dateObj.getDay(), rooms));
            }
            document.getElementById('control-name').innerHTML += " - " +dateObj.getDate() +" " +monthNameDec[dateObj.getMonth()];
            datePeriod[1] = {};
            datePeriod[1].day = dateObj.getDate();
            datePeriod[1].month = (dateObj.getMonth() + 1);
            datePeriod[1].year = dateObj.getFullYear();
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
        };
        
        /**
         * Проверяет, не пересекается ли время мероприятия с другими мероприятиями
         * @param {integer} exclude - id мероприятия, которое не должно учавствовать в проверке (используем при изменении, т.к тогда эта же запись будет участвовать и не пропускать)
         * @returns {boolean}
         */
        function checkTimesInterval(timeFrom, timeTo, date, room, exclude) {
            if(!exclude) exclude = 0;
            var dateRows = document.getElementsByClassName('three--date-row');
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
                eventName: result.event.name,
                eventOtherName: (result.event.other_name !== null ? result.event.other_name : ''),
                timeFrom: result.time_from,
                timeTo: (result.time_to !== null ? result.time_to : ''),
            };
            return cellData;
        };
        
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
                            
                            var createProfCat = document.createElement('div');
                            createProfCat.className = 'three--prof-cat-cell';
                            if(params.profCat && params.profCat.length){
                                for(var k = 0; k < params.profCat.length; k++){
                                    var profCatSpan = document.createElement('span');
                                    profCatSpan.innerHTML = params.profCat[k].profCat.alias;
                                    createProfCat.append(profCatSpan);
                                }
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
                                    if(!p && +params.timeFrom < +eventsCell[k].dataset.timeFrom){
                                        roomsCell[z].insertBefore(createContainer, eventsCell[k]);
                                        return true;
                                            }
                                    if (p && +params.timeFrom < +eventsCell[k].dataset.timeFrom &&
                                            +params.timeFrom > +p.dataset.timeFrom) {
                                        roomsCell[z].insertBefore(createContainer, eventsCell[k]);
                                        return true;
                                    }
                                    p = eventsCell[k];
                                }
                                if (p && +params.timeFrom > +p.dataset.timeFrom) {
                                    roomsCell[z].append(createContainer);
                                    return true;
                                } else if (p && +params.timeFrom < +p.dataset.timeFrom) {
                                    roomsCell[z].insertBefore(createContainer, p);
                                    return true;
                                }
                            }

                        }
                    }
                }
            }
        }
        
        
        /**
         * Загружает расписание на месяц и рендерит в нужные ячейки
         * @param {int} month
         * @param {int} year
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
                            eventName: scheduleData[key].event.name,
                            eventOtherName: (scheduleData[key].event.other_name !== null ? scheduleData[key].event.other_name : ''),
                            timeFrom: scheduleData[key].time_from,
                            timeTo: (scheduleData[key].time_to !== null ? scheduleData[key].time_to : ''),
                            profCat : scheduleData[key].profCat
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
        loadSchedule(datePeriod);
        
        // Редактирование мероприятия
        var editEventId = false;
//        var editEventDate = false;
        var editEventRoom = false;
        $('body').on('click', '.event-cell', function (e) {
            editEventId = this.dataset.id;
            for (var key in scheduleData) {
                if (scheduleData[key].id == editEventId) {
                    $('#edit--time_from').val(normalizeTime(minuteToTime(scheduleData[key].time_from)));
                    if (scheduleData[key].time_to) {
                        $('#edit--time_to').val(normalizeTime(minuteToTime(scheduleData[key].time_to)));
                    }
                    var dateT = new Date(scheduleData[key].date);
//                    editEventDate = {day: dateT.getDate(), month: dateT.getMonth(), year: dateT.getFullYear()};
                    editEventRoom = scheduleData[key].room_id;
                    
                    $('#three--right-more-meta').html(normalizeDate(dateT.getDate() + "." + dateT.getMonth() + "." + dateT.getFullYear()) +
                            ", " + minuteToTime(scheduleData[key].time_from) +
                            " / " + scheduleData[key].event.name + " (" + scheduleData[key].eventType.name + ")");
                }
            }
            loadUserInEvent(editEventId);
            $('.three--right-more').removeClass('zoomOutRight').addClass('zoomInRight animated').css({'display': 'block'});
        });
        
        $('#three--right-more-close').click(function () {
            $('.three--right-more').removeClass('zoomInRight').addClass('zoomOutRight');
        });
        
        function loadUserInEvent(eventId){
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
                    var result = JSON.parse(data);
                    console.log(result);
                    
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        }
        
        
        
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


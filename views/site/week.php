<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Расписание на неделю';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>
<div id="from" hidden><?= $from ?></div>
<div id="to" hidden><?= $to ?></div>
<div id="hash" hidden><?= $hash ?></div>

<div class="three--schedule-container">

    <div id="three--schedule-content">
        <div class="schedule-controls">
            <div id="control-name" class="name"></div>
<!--            <div class="arrow-left"><i id="month-left" class="fas fa-arrow-circle-left cursor-pointer" aria-hidden="true"></i></div>
            <div class="arrow-right"><i id="month-right" class="fas fa-arrow-circle-right cursor-pointer" aria-hidden="true"></i></div>-->
        </div>
        <div class="three-ind--title-row mrg-top15">
            <div class="date">Дата</div>
            <?php foreach ($rooms as $key => $value): ?>
                <div data-room="<?= $value['id'] ?>" class="room"><?= $value['name'] ?></div>
            <?php endforeach; ?>
        </div>
        <div id="three--schedule-items"></div>
    </div>
    <br>

</div>

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
        
        var rooms = document.querySelector('.three-ind--title-row').getElementsByClassName('room');

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

        renderCalendar();

        
        
        function renderCalendar() {
            var dateFromSplit = document.getElementById('from').innerHTML.split('-');
            var dateObj = new Date(dateFromSplit[1] +'/' +dateFromSplit[2] +'/' +dateFromSplit[0]);
            $('#three--schedule-items').empty();
//            dateObj.setDay(1);
            document.getElementById('control-name').innerHTML = dateObj.getDate() + " " + monthNameDec[dateObj.getMonth()];
            datePeriod[0] = {};
            datePeriod[0].day = dateObj.getDate();
            datePeriod[0].month = (dateObj.getMonth() + 1);
            datePeriod[0].year = dateObj.getFullYear();

            for (var i = 0; i < 7; i++) {
                dateObj.setFullYear(dateObj.getFullYear());
                dateObj.setMonth(dateObj.getMonth());
                if (i > 0) {
                    dateObj.setDate((dateObj.getDate() + 1));
                }
                document.getElementById('three--schedule-items').appendChild(returnScheduleRow(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), dateObj.getDay(), rooms));
                if(i < 6){
                    document.getElementById('three--schedule-items').appendChild(document.getElementsByClassName('three-ind--title-row')[0].cloneNode(true));
                }
                
            }
            document.getElementById('control-name').innerHTML += " - " + dateObj.getDate() + " " + monthNameDec[dateObj.getMonth()];
//            alert('ok');
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
            createContainer.className = 'three-ind--date-row';

            var createDate = document.createElement('div');
            createDate.className = 'date';
            createDate.innerHTML = normalizeDate(day + "." + month + "." + year) + "<br>" + weekdayName[week];
            if (week == 6 || week == 0) {
                createDate.style.color = 'red';
            }
//alert(week);
            createContainer.appendChild(createDate);

            for (var i = 0; i < rooms.length; i++) {
                var createRoom = document.createElement('div');
                createRoom.className = 'room room-cell';
                createRoom.dataset.room = rooms[i].dataset.room;
                createContainer.appendChild(createRoom);
            }
            return createContainer;
        };
        
        
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
        };
        
        
        /**
         * Добавляет мероприятие в календарь
         * @param {object} params
         */
        function addEventInCalendar(params) {
            var dateRows = document.getElementsByClassName('three-ind--date-row');
            for (var i = 0; i < dateRows.length; i++) {
                if (params.date.day == dateRows[i].dataset.day && params.date.month == dateRows[i].dataset.month && params.date.year == dateRows[i].dataset.year) {
                    var roomsCell = dateRows[i].getElementsByClassName('room-cell');
                    for (var z = 0; z < roomsCell.length; z++) {
                        if (roomsCell[z].dataset.room == params.room) {
                            var createContainer = document.createElement('div');
                            createContainer.className = 'event-cell-ind noselect';
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
                            createEventName.innerHTML = (params.eventName && params.eventName != '' ? "\"" +params.eventName +"\"":"") + (params.eventOtherName && params.eventOtherName != '' ? " (" + params.eventOtherName + ")" : "");

                            createContainer.appendChild(createBudgie);
                            createContainer.appendChild(createEventType);
                            createContainer.appendChild(createEventName);
                            
                            var adminListArr = [];
                            var createAdminList = document.createElement('div');
                            createAdminList.className = 'three--user-admin-list';
                            for(var key in params.users){
                                // Хардкод на prof_cat_id
                                if(+params.users[key].userWithProf.userProfession.prof.proff_cat_id != 8){
                                    adminListArr[adminListArr.length] = params.users[key].userWithProf.surname +(params.users[key].userWithProf.show_full_name == 1?" " + params.users[key].userWithProf.name:"");
                                }
                            }
                            if(adminListArr.length){
                                createAdminList.innerHTML = adminListArr.join(', ');
                            }
                            createContainer.appendChild(createAdminList);
                            
                            if(params.addInfo){
                                var createAddInfo = document.createElement('div');
                                createAddInfo.className = 'three--add-info-block';
                                createAddInfo.innerHTML = "(" +params.addInfo +")";
                                createContainer.appendChild(createAddInfo);
                            }
                            
                            var userListArr = [];
                            var createUserList = document.createElement('div');
                            createUserList.className = 'three--user-actors-list';
                            if(+params.is_all > 0){
                                createUserList.innerHTML = '(ВСЕ)';
                            }else{
                                for(var key in params.users){
                                    // Хардкод на prof_cat_id
                                    if(+params.users[key].userWithProf.userProfession.prof.proff_cat_id == 8){
                                        userListArr[userListArr.length] = params.users[key].userWithProf.surname +(params.users[key].userWithProf.show_full_name == 1?" " + params.users[key].userWithProf.name:"");
                                    }
                                }
                            }
                            
                            if(userListArr.length){
                                createUserList.innerHTML = userListArr.join(', ');
                            }
                            createContainer.appendChild(createUserList);
                            
                            var createProfCat = document.createElement('div');
                            createProfCat.className = 'three--prof-cat-cell';
                            if (params.profCat && params.profCat.length) {
                                var profCatArr = [];
                                for (var k = 0; k < params.profCat.length; k++) {
//                                    var profCatSpan = document.createElement('span');
//                                    profCatSpan.innerHTML = params.profCat[k].profCat.alias;
                                    profCatArr[profCatArr.length] = params.profCat[k].profCat.alias;
                                }
                                createProfCat.innerHTML = profCatArr.join(', ');
                            }
                            createContainer.appendChild(createProfCat);
                            
                            createContainer.appendChild(returnHR());

                            var eventsCell = roomsCell[z].getElementsByClassName('event-cell-ind');
                            if (!eventsCell.length) {
                                roomsCell[z].appendChild(createContainer);
                                return true;
                            } else {
                                var p = false;
                                for (var k = 0; k < eventsCell.length; k++) {
                                    if (!p && +params.timeFrom < +eventsCell[k].dataset.timeFrom) {
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
                                    roomsCell[z].appendChild(createContainer);
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
                from: document.getElementById('from').innerHTML,
                to: document.getElementById('to').innerHTML,
                hash: document.getElementById('hash').innerHTML,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/site/week-schedule',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if(result.result == 'ok'){
                        scheduleData = result.response;
                        for (var key in scheduleData) {
                        var dateT = new Date(scheduleData[key].date);
                            addEventInCalendar(generateCellData(scheduleData[key]));
                        }
                    }else if(result.result == 'error'){
                        showNotifications(result.response, 4000, NOTIF_RED);
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



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
            <div class="one--schedule-controls">
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
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-sm" id="add--time_from">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    Время окончания
                                    <i class="fas fa-exclamation-circle my-tooltip" data-toggle="tooltip" data-placement="right" title="Можно не указывать, если окончание мероприятия неизвестно, но тогда не будут работать подсказки, предупреждающие о пересечениях времени."></i>
                                </th>
                                <td>
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-sm" id="add--time_to">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Тип мероприятия</th>
                                <td id="add--event_type">
                                    <div class="form-group">
                                        <select id="select-event-type" class="form-control form-control-sm">
                                            <?php foreach ($eventType as $key => $value):  ?>
                                                <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Мероприятие</th>
                                <td id="add--event">
                                    <div class="form-group">
                                        <select id="select-event-category" class="form-control form-control-sm">
                                            <?php foreach ($eventCategories as $key => $value):  ?>
                                                <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select id="select-event" class="form-control form-control-sm">
                                            <?php foreach ($events as $key => $value):  ?>
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


<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
        var addNowDate = {}; // Выбранная дата
        var addNowRoom = false; // выбранный зал
        
        $('#add--time_from').bootstrapMaterialDatePicker({
                date: false,
                shortTime: false,
                format: 'HH:mm'
        });
        $('#add--time_to').bootstrapMaterialDatePicker({
                date: false,
                shortTime: false,
                format: 'HH:mm'
        });
        
        var rooms = document.querySelector('.one--title-row').getElementsByClassName('room');


        // ====== Плагин календаря
        var monthName = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        //    var monthNameTwo = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var monthNameDec = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        var nowDate = new Date();

        var weekNumber = ['1', '2', '3', '4', '5', '6', '0'];

        renderCalendar(nowDate.getFullYear(), nowDate.getMonth());
        
        $('#month-right').click(function(){
            nowDate.setMonth(nowDate.getMonth() + 1);
            renderCalendar(nowDate.getFullYear(), nowDate.getMonth());
            loadSchedule(nowDate.getMonth(), nowDate.getFullYear());
        });
        $('#month-left').click(function(){
            nowDate.setMonth(nowDate.getMonth() - 1);
            renderCalendar(nowDate.getFullYear(), nowDate.getMonth());
            loadSchedule(nowDate.getMonth(), nowDate.getFullYear());
        });
        
        function renderCalendar(year, month){
            $('#one--schedule-items').empty();
            var date = new Date(year, month, 1);
            var dayCount = dayInMonth(year, month);
            document.getElementById('control-name').innerHTML = monthName[date.getMonth()] +", "+date.getFullYear();
            
            for(var i = 1; i <= dayCount; i++){
                date = new Date(year, month, i);
                document.getElementById('one--schedule-items').append(returnScheduleRow(date.getFullYear(), date.getMonth(), date.getDate(), date.getDay(), rooms));
            }
            return true;
        };
        
        function returnScheduleRow(year, month, day, week, rooms){
            var createContainer = document.createElement('div');
            createContainer.dataset.day = day;
            createContainer.dataset.month = month;
            createContainer.dataset.year = year;
            createContainer.className = 'one--date-row';
            
            var createDate = document.createElement('div');
            createDate.className = 'date';
            createDate.innerHTML = normalizeDate(day+"."+month+"."+year) + "<br>" +weekdayName[week];
            if(week == 6 || week == 0){
                createDate.style.color = 'red';
            }
            
            createContainer.append(createDate);
            
            for(var i = 0; i < rooms.length; i++){
                var createRoom = document.createElement('div');
                createRoom.className = 'room room-cell';
                createRoom.dataset.room = rooms[i].dataset.room;
                createContainer.append(createRoom);
            }
            return createContainer;
        };


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
        };
        
        $('body').on('dblclick', '.room-cell', function(){
            addNowDate.day = this.parentNode.dataset.day;
            addNowDate.month = this.parentNode.dataset.month;
            addNowDate.year = this.parentNode.dataset.year;
            addNowRoom = this.dataset.room;
            
            $('#add--date').html(normalizeDate(addNowDate.day +"."+addNowDate.month+"."+addNowDate.year));
            $('#addEventModal').modal('show');
        });
        
        // Отображаем только те спектакли, категория которых выбрана
        function eventCatSort(){
            var eventCategory = $('#select-event-category').val();
            var events = document.getElementById('select-event');
            var z = 0;
            for(var i = 0; i < events.options.length; i++){
                if(events.options[i].dataset.category == eventCategory){
                    events.options[i].style.display = 'block';
                    if(z === 0){
                       events.options[i].selected = true;
                       z++;
                    }
                }else{
                    events.options[i].style.display = 'none';
                }
            }
        }
        eventCatSort();
        
        $('#select-event-category').change(function(){
            eventCatSort();
        });
        
        $('#add-event-submit').click(function(){
            var timeFrom = $('#add--time_from').val();
            var timeTo = $('#add--time_to').val();
            var eventType = $('#select-event-type').val();
            var event = $('#select-event').val();
            if(!timeFrom || timeFrom == ''){
                showNotifications("Не выбрано время начала мероприятия", 3000, NOTIF_RED);
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
                event: event
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/one',
                data: data,
                success: function (data) {
                    if(JSON.parse(data) == 1){
                        var cellData = {
                            date: addNowDate,
                            room: addNowRoom,
                            eventType: $('#select-event-type').find(':selected').html(),
                            eventName: $('#select-event').find(':selected').html(),
                            eventOtherName: $('#select-event').find(':selected').attr('data-other-name'),
                            timeFrom: timeToMinute(timeFrom),
                            timeTo: (timeTo && timeTo != ''?timeToMinute(timeTo):'')
                        };
                        addEventInCalendar(cellData);
                        $('#addEventModal').modal('hide');
                    }else{
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
        
        /**
         * Добавляет мероприятие в календарь
         * @param {object} params
         */
        function addEventInCalendar(params){
            var dateRows = document.getElementsByClassName('one--date-row');
            for(var i = 0; i < dateRows.length; i++){
                if(params.date.day == dateRows[i].dataset.day && params.date.month == dateRows[i].dataset.month && params.date.year == dateRows[i].dataset.year){
                    var roomsCell = dateRows[i].getElementsByClassName('room-cell');
                    for(var z = 0; z < roomsCell.length; z++){
                        if(roomsCell[z].dataset.room == params.room){
                            var createContainer = document.createElement('div');
                            createContainer.className = 'event-cell';
                            createContainer.dataset.timeFrom = params.timeFrom;
                            if(params.timeTo && params.timeTo != ''){
                                createContainer.dataset.timeTo = params.timeTo;
                            }
                            var createBudgie = document.createElement('span');
                            createBudgie.className = 'badge badge-pill badge-info';
                            createBudgie.innerHTML = minuteToTime(params.timeFrom)+(params.timeTo && params.timeTo != ''?" - "+minuteToTime(params.timeTo):"");
                            
                            var createEventType = document.createElement('span');
                            createEventType.className = 'type';
                            createEventType.innerHTML = "(" +params.eventType +")";
                            
                            var createEventName = document.createElement('span');
                            createEventName.className = 'name';
                            createEventName.innerHTML = params.eventName +(params.eventOtherName && params.eventOtherName != ''?" (" +params.eventOtherName +")":"");
                            
                            createContainer.append(createBudgie);
                            createContainer.append(createEventType);
                            createContainer.append(createEventName);
                            roomsCell[z].append(createContainer);
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
        function loadSchedule(month, year){
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
                    var result = JSON.parse(data);
                    for(var key in result){
                        var dateT = new Date(result[key].date);
                        var cellData = {
                            date: {
                                day: dateT.getDate(),
                                month: dateT.getMonth(),
                                year: dateT.getFullYear()
                            },
                            room: result[key].room_id,
                            eventType: result[key].eventType.name,
                            eventName: result[key].event.name,
                            eventOtherName: (result[key].event.other_name !== null?result[key].event.other_name:''),
                            timeFrom: result[key].time_from,
                            timeTo: (result[key].time_to !== null?result[key].time_to:''),
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
        

    }
</script>


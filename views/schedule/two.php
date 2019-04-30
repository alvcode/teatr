<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Расписание актеров';
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
    
    <div id="all-events-buttons"></div>
    <div class="schedule-controls">
        <div id="control-name" class="name"></div>
        <div class="arrow-left"><i id="month-left" class="fas fa-arrow-circle-left cursor-pointer" aria-hidden="true"></i></div>
        <div class="arrow-right"><i id="month-right" class="fas fa-arrow-circle-right cursor-pointer" aria-hidden="true"></i></div>
    </div>
    <div id="two--notification-banner"></div>
    <div class="two--table-container">
        <table class="table-striped table-bordered">
            <thead id="two--thead"></thead>
            <tbody></tbody>
        </table>
    </div>

</div>
<br>

<?php // echo "<pre>";print_r($events); ?>



<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");


        $('.two--table-container').css({'height': $(window).height() - 140});

        var scheduleData = false;

        var nowDate = new Date();

        var monthName = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        //    var monthNameTwo = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var monthNameDec = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        $('#month-right').click(function () {
            nowDate.setMonth(nowDate.getMonth() + 1);
            loadSchedule();
        });
        $('#month-left').click(function () {
            nowDate.setMonth(nowDate.getMonth() - 1);
            loadSchedule();
        });

        function loadSchedule() {
            goPreloader();
            var data = {
                trigger: 'load-schedule',
                month: nowDate.getMonth() + 1,
                year: nowDate.getFullYear(),
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    if(data){
                        scheduleData = JSON.parse(data);
                        console.log(scheduleData);
                        document.getElementById('control-name').innerHTML = monthName[nowDate.getMonth()] + ", " + nowDate.getFullYear();
                        renderThead(scheduleData);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        }
        loadSchedule();

        /**
         * Рендерит Thead таблицы
         * @param {object} data
         */
        function renderThead(data) {
            $('#two--thead').empty();
            $('#two--notification-banner').empty();
            $('#all-events-buttons').empty();
            var allEvents = data.allEvents;
            data = data.schedule;
            console.log(allEvents);
            
            var dateContainer = document.createElement('tr');
            dateContainer.className = 'two--thead-date';
            dateContainer.append(document.createElement('th'));

            var eventContainer = document.createElement('tr');
            eventContainer.className = 'two--thead-event';
            eventContainer.append(document.createElement('th'));

            var timeContainer = document.createElement('tr');
            timeContainer.className = 'two--thead-time';
            var createTh = document.createElement('th');
            createTh.innerHTML = "Состав <div class='badge badge-pill badge-info'><i class='fas fa-plus'></i></div>";
            timeContainer.append(createTh);
            
            var z = 0;
            for (var key in data) {
                var dateCell = document.createElement('th');
                var thisDate = new Date(nowDate.getFullYear(), nowDate.getMonth(), key);
                dateCell.innerHTML = key + ' ' + weekdayName[thisDate.getDay()];
                var eventCount = 0; // Счетчик общего кол-ва мероприятий
                for (var keyEvent in data[key]) {
                    var eventCell = document.createElement('th');
                    var timeCount = 0; // Счетчик кол-ва повторов спектакля
                    for (var keyTime in data[key][keyEvent]) {
                        if (timeCount === 0) {
                            eventCell.innerHTML = data[key][keyEvent][keyTime].event.name;
                        }
                        var timeCell = document.createElement('th');
                        timeCell.dataset.event = data[key][keyEvent][keyTime].event.id;
                        timeCell.dataset.timeFrom = data[key][keyEvent][keyTime].time_from;
                        timeCell.dataset.timeTo = data[key][keyEvent][keyTime].time_to;
                        eventCell.dataset.event = data[key][keyEvent][keyTime].event.id;
                        timeCell.innerHTML = minuteToTime(data[key][keyEvent][keyTime].time_from);
                        timeContainer.append(timeCell);
                        timeCount++;
                    }
                    eventCount += timeCount;
                    eventCell.setAttribute('colspan', timeCount);
                    eventContainer.append(eventCell);
                }
                dateCell.setAttribute('colspan', eventCount);
                dateContainer.append(dateCell);
                z++;
            }
            document.getElementById('two--thead').append(dateContainer);
            document.getElementById('two--thead').append(eventContainer);
            document.getElementById('two--thead').append(timeContainer);
            
            if(!z){
                $('#two--thead').empty();
                $('#two--notification-banner').html('<h1>Не задано ни одного мероприятия</h1>');
            }
            
            if(z){
                for(var key in allEvents){
                    var createEventItem = document.createElement('div');
                    createEventItem.className = 'btn btn-sm ml-1 btn-outline-info event-button';
                    createEventItem.dataset.event = allEvents[key].id;
                    createEventItem.innerHTML = allEvents[key].name;
                    document.getElementById('all-events-buttons').append(createEventItem);
                }
                
            }
        };
        
        var selectedEvent = false;
        $('#all-events-buttons').on('click', '.event-button', function(e){
           $('.event-button').removeClass('btn-info').addClass('btn-outline-info');
           $(this).removeClass('btn-outline-info').addClass('btn-info');
           selectedEvent = this.dataset.event;
           $('.two--thead-event > th, .two--thead-time > th').css({'color': 'black'});
           $('th[data-event='+ selectedEvent +']').css({'color': '#F97979'});
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


    }
</script>


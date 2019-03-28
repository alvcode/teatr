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


<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
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
        });
        $('#month-left').click(function(){
            nowDate.setMonth(nowDate.getMonth() - 1);
            renderCalendar(nowDate.getFullYear(), nowDate.getMonth());
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
        }
        
        function returnScheduleRow(year, month, day, week, rooms){
            var createContainer = document.createElement('div');
            createContainer.dataset.day = day;
            createContainer.dataset.month = month;
            createContainer.dataset.year = year;
            createContainer.className = 'one--date-row';
            
            var createDate = document.createElement('div');
            createDate.className = 'date';
            createDate.innerHTML = normalizeDate(day+"."+month+"."+year) + "<br>" +weekdayName[week];
            
            createContainer.append(createDate);
            
            for(var i = 0; i < rooms.length; i++){
                var createRoom = document.createElement('div');
                createRoom.className = 'room room-cell';
                createRoom.dataset.room = rooms[i].dataset.room;
                createContainer.append(createRoom);
            }
            return createContainer;
        }


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
        
        
        $('body').on('dblclick', '.room-cell', function(){
            alert('ok');
        });
        
        
        
        
        
        
        function normalizeDate(date) {
            var splitDate = date.split(".");
            return splitDate[0] + "." + (+splitDate[1] >= 0 && +splitDate[1] < 9 ? "0" + (+splitDate[1] + 1) : (+splitDate[1] + 1)) + "." + splitDate[2];
        }
        

    }
</script>


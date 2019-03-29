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
                                        <select class="form-control form-control-sm">
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
                                        <select class="form-control form-control-sm">
                                            <?php foreach ($events as $key => $value):  ?>
                                                <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
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
        
        var addNowDate = {};
        var addNowRoom = false;
        $('body').on('dblclick', '.room-cell', function(){
            addNowDate.day = this.parentNode.dataset.day;
            addNowDate.month = this.parentNode.dataset.month;
            addNowDate.year = this.parentNode.dataset.year;
            addNowRoom = this.dataset.room;
            
            $('#add--date').html(normalizeDate(addNowDate.day +"."+addNowDate.month+"."+addNowDate.year));
            $('#addEventModal').modal('show');
        });
        
        
        
        
        
        
        function normalizeDate(date) {
            var splitDate = date.split(".");
            return splitDate[0] + "." + (+splitDate[1] >= 0 && +splitDate[1] < 9 ? "0" + (+splitDate[1] + 1) : (+splitDate[1] + 1)) + "." + splitDate[2];
        }
        

    }
</script>


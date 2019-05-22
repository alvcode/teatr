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
            <tbody id="two--tbody" class="cursor-pointer"></tbody>
        </table>
    </div>
    <br>
    <button id="check-fill" class="btn btn-sm btn-info">Выполнить проверку на заполненность</button>

</div>
<br>

<!-- Modal actors list -->
<div class="modal fade" id="actorsListModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Список актеров</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="actor-modal-container">
                    <?php foreach ($actors as $key => $value): ?>
                        <div style="font-weight: 700;" class="text-danger"><?= $key ?></div>
                        <?php foreach($value as $keyV => $valueV): ?>
                            <div class="actor-list-item noselect" data-id="<?= $valueV['id'] ?>"><?= $valueV['name'] ?> <?= $valueV['surname'] ?></div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="add-actor-list-submit" type="button" class="btn btn-sm btn-success">Применить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal check fill -->
<div class="modal fade" id="checkFillModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Проверка расписания на заполненность</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div><div id="check-fill-update" class="btn btn-sm btn-info"><i class="fas fa-sync"></i> Обновить</div></div>
                <div id="check-fill-result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal magic notification -->
<div class="modal fade" id="magicModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Применить автозаполнение расписания?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Для выбранного сотрудника будет автоматически проставлено расписание на все незаполненные дни
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Закрыть</button>
                <button id="magic-schedule-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal last cast -->
<div class="modal fade" id="lastCastModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Для данного мероприятия найден состав. Скопировать?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Весь состав будет скопирован и проставлен на выбранное мероприятие
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Закрыть</button>
                <button id="last-cast-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal actor delete -->
<div class="modal fade" id="actorDeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Удалить актера из состава?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Актер будет удален из состава, все его расписание на выбранное мероприятие тоже будет удалено. Так же, если 
                у актера есть дубли, то они тоже будут очищены
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-actor-cast-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<?php // echo "<pre>";print_r($actors); ?>



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

        $('.two--table-container').css({'height': $(window).height() - 140});

        var selectedEvent = false;
        var activeCells = false;
        var selectedActor = [];
        var selectedActorName = false;
        var castsData = false;
        var understudyMode = 0;
        var scheduleData = false;

        var nowDate = new Date();

        var monthName = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        //    var monthNameTwo = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var monthNameDec = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        $('#month-right').click(function () {
            nowDate.setMonth(nowDate.getMonth() + 1);
            $('#two--tbody').empty();
            selectedEvent = false;
            loadSchedule();
        });
        $('#month-left').click(function () {
            nowDate.setMonth(nowDate.getMonth() - 1);
            $('#two--tbody').empty();
            selectedEvent = false;
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
                    if (data) {
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

            var dateContainer = document.createElement('tr');
            dateContainer.className = 'two--thead-date';
            dateContainer.append(document.createElement('th'));

            var eventContainer = document.createElement('tr');
            eventContainer.className = 'two--thead-event';
            eventContainer.append(document.createElement('th'));

            var timeContainer = document.createElement('tr');
            timeContainer.className = 'two--thead-time';
            var createTh = document.createElement('th');
            createTh.innerHTML = "Состав <div class='badge badge-pill badge-info cursor-pointer f-s10 add-in-cast'><i class='fas fa-plus'></i></div>";
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
                        timeCell.className = 'two--event-cell';
                        timeCell.dataset.event = data[key][keyEvent][keyTime].event.id;
                        timeCell.dataset.schedule = data[key][keyEvent][keyTime].id;
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

            if (!z) {
                $('#two--thead').empty();
                $('#two--notification-banner').html('<h1>Не задано ни одного мероприятия</h1>');
            }

            if (z) {
                for (var key in allEvents) {
                    var createEventItem = document.createElement('div');
                    createEventItem.className = 'btn btn-sm ml-1 btn-outline-info f-s12 event-button';
                    createEventItem.dataset.event = allEvents[key].id;
                    createEventItem.innerHTML = allEvents[key].name;
                    document.getElementById('all-events-buttons').append(createEventItem);
                }

            }
        }
        ;

        // Выделение спектакля
        var lastCastDate = false;
        $('#all-events-buttons').on('click', '.event-button', function (e) {
            $('#two--tbody').empty();
            $('.event-button').removeClass('btn-info').addClass('btn-outline-info');
            $(this).removeClass('btn-outline-info').addClass('btn-info');
            selectedEvent = this.dataset.event;
            $('.two--thead-event > th, .two--thead-time > th').css({'color': 'black'});
            $('th[data-event=' + selectedEvent + ']').css({'color': '#F97979'});
            var headCells = document.getElementsByClassName('two--thead-time')[0].getElementsByTagName('th');
            activeCells = [];
            for (var i = 0; i < headCells.length; i++) {
                if (headCells[i].dataset.event == selectedEvent) {
                    activeCells[activeCells.length] = i;
                }
            }
            console.log(activeCells);
            goPreloader();
            var data = {
                trigger: 'load-casts-in-schedule',
                event: selectedEvent,
                month: nowDate.getMonth() + 1,
                year: nowDate.getFullYear(),
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    castsData = JSON.parse(data);
                    console.log(castsData);
                    if(castsData.result == 'ok'){
                        for (var key in castsData.data.cast) {
                            insertActor(castsData.data.cast[key].name + " " + castsData.data.cast[key].surname, castsData.data.cast[key].id, castsData.data.cast[key].cast_id, 0);
                            if (castsData.data.cast[key].understudy) {
                                for (var keyU in castsData.data.cast[key].understudy) {
                                    insertActor(castsData.data.cast[key].understudy[keyU].name + " " + castsData.data.cast[key].understudy[keyU].surname, castsData.data.cast[key].understudy[keyU].id, castsData.data.cast[key].cast_id, 1);
                                }
                            }
                        }
                        renderSchedule(castsData.data.schedule);
                    }else if(castsData.result == 'last'){
                        $('#lastCastModal').modal('show');
                        lastCastDate = castsData.data;
                    }else if(castsData.result == 'empty'){
                        
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        $('#last-cast-submit').click(function(){
            goPreloader();
            var data = {
                trigger: 'add-last-cast',
                event: selectedEvent,
                month: nowDate.getMonth() + 1,
                year: nowDate.getFullYear(),
                searchMonth: lastCastDate.month,
                searchYear: lastCastDate.year,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    castsData = JSON.parse(data);
                    console.log(castsData);
                    if(castsData.result == 'ok'){
                        for (var key in castsData.data.cast) {
                            insertActor(castsData.data.cast[key].name + " " + castsData.data.cast[key].surname, castsData.data.cast[key].id, castsData.data.cast[key].cast_id, 0);
                            if (castsData.data.cast[key].understudy) {
                                for (var keyU in castsData.data.cast[key].understudy) {
                                    insertActor(castsData.data.cast[key].understudy[keyU].name + " " + castsData.data.cast[key].understudy[keyU].surname, castsData.data.cast[key].understudy[keyU].id, castsData.data.cast[key].cast_id, 1);
                                }
                            }
                        }
                        renderSchedule(castsData.data.schedule);
                    }else if(castsData.result == 'error'){
                        showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    }
                    stopPreloader();
                    $('#lastCastModal').modal('hide');
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });

        $('.two--table-container').on('click', '.add-in-cast', function () {
            if (!selectedEvent) {
                showNotifications('Не выбран спектакль', 3000, NOTIF_RED);
                return false;
            }
            $('#actorsListModal').modal('show');
        });
        
        $('.actor-list-item').click(function () {
            var actorId = this.dataset.id;
            if(this.classList.contains('selected')){
                this.classList.remove('selected');
                var idx = selectedActor.indexOf(actorId);
                selectedActor.splice(idx, 1);
            }else{
                this.classList.add('selected');
                selectedActor[selectedActor.length] = actorId;
            }
            console.log(selectedActor);
        });
        
        $('#add-actor-list-submit').click(function(){
            for(var i = 0; i < selectedActor.length; i++){
                if (!checkRepeatCast(selectedActor[i], understudyMode, understudyParent)) {
                    showNotifications('Этот актер уже есть в списке', 3000, NOTIF_RED);
                    return false;
                }
            }
            goPreloader();
            if (understudyMode) {
                var data = {
                    trigger: 'add-in-understudy',
                    user: selectedActor,
                    cast: understudyParent,
                };
            } else {
                var data = {
                    trigger: 'add-in-cast',
                    event: selectedEvent,
                    user: selectedActor,
                    month: nowDate.getMonth() + 1,
                    year: nowDate.getFullYear(),
                };
            }
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    var actorsList = document.getElementsByClassName('actor-list-item');
                    if (result.result == 'ok') {
                        if (understudyMode) {
                            for(var key in result.data){
                                for(var i = 0; i < actorsList.length; i++){
                                    if(+actorsList[i].dataset.id == +result.data[key].user){
                                        insertActor(actorsList[i].innerHTML, actorsList[i].dataset.id, understudyParent, understudyMode);
                                    }
                                }
                            }
                        } else {
                            for(var key in result.data){
                                for(var i = 0; i < actorsList.length; i++){
                                    if(+actorsList[i].dataset.id == +result.data[key].user){
                                        insertActor(actorsList[i].innerHTML, actorsList[i].dataset.id, result.data[key].cast, understudyMode);
                                    }
                                }
                            }
                        }
                        $('#actorsListModal').modal('hide');
                    }else {
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
        
        $('#actorsListModal').on('hide.bs.modal', function (e) {
            selectedActor = [];
            $('.actor-list-item').removeClass('selected');
        })
        
        // Удаление актера из состава
        var deletedActorInCast = false;
        var deletedActorCastId = false;
        var deletedActorUnderstudy = false;
        $('#two--tbody').on('click', '.two--remove-cast', function () {
            deletedActorUnderstudy = this.parentNode.parentNode.classList.contains('understudy');
            deletedActorInCast = this.parentNode.parentNode.dataset.id;
            deletedActorCastId = this.parentNode.parentNode.dataset.cast;
            $('#actorDeleteModal').modal('show');
        });
        $('#delete-actor-cast-submit').click(function () {
            goPreloader();
            if (deletedActorUnderstudy) {
                var data = {
                    trigger: 'delete-understudy',
                    user: deletedActorInCast,
                    cast: deletedActorCastId,
                };
            } else {
                var data = {
                    trigger: 'delete-actor-in-cast',
                    event: selectedEvent,
                    user: deletedActorInCast,
                    month: nowDate.getMonth() + 1,
                    year: nowDate.getFullYear(),
                };
            }
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        deleteActor(deletedActorInCast, deletedActorCastId, deletedActorUnderstudy);
                        $('#actorDeleteModal').modal('hide');
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
        // Обнуляем режим дублера при закрывании списка актеров
        $('#actorsListModal').on('hide.bs.modal', function (e) {
            understudyMode = 0;
        })
        var understudyParent = false;
        $('#two--tbody').on('click', '.two--add-understudy', function () {
            understudyParent = this.parentNode.parentNode.dataset.cast;
            understudyMode = 1;
            $('#actorsListModal').modal('show');
//            alert('ok');
        });

        $('#two--tbody').on('click', 'tr > td', function () {
            var scheduleId = this.dataset.schedule;
            var userId = this.parentNode.dataset.id;
            var castId = this.parentNode.dataset.cast;
            var self = this;
            var thisIndex = $(this).index();
            if (!activeCells.includes(thisIndex)) {
                showNotifications('Невозможно поставить в другой спектакль', 2000, NOTIF_RED);
                return false;
            }
            var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var rowUser = rows[i].dataset.id;
                for (var z = 0; z < cells.length; z++) {
                    if (+rowUser == +userId && (+thisIndex - 1) == z && cells[z].innerHTML === "+" && cells[z] !== self) {
                        showNotifications('Этот актер уже стоит на этом спектакле', 3000, NOTIF_RED);
                        return false;
                    }
                    if(+cells[z].parentNode.dataset.cast == +castId && (+thisIndex - 1) == z && cells[z].innerHTML === "+" && cells[z] !== self){
                        showNotifications('На эту роль уже поставлен актер', 3000, NOTIF_RED);
                        return false;
                    }
                }
            }
            goPreloader();
            var data = {
                trigger: 'add-user-schedule',
                schedule: scheduleId,
                user: userId,
                cast: castId
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.result == 'ok'){
                        self.innerHTML = '+';
                    }else if(result.result == 'deleted'){
                        self.innerHTML = '';
                    }else if(result.result == 'error'){
                        var textNotification = '';
                        for(var key in result.data){
                            if(result.data[key].time_to){
                                textNotification += "Конфликт. Данный сотрудник уже стоит на \n\
                                    "+ result.data[key].name +" с "+ minuteToTime(result.data[key].time_from) +" до "+ minuteToTime(result.data[key].time_to);
                            }else{
                                textNotification += "Конфликт. Данный сотрудник уже стоит на \n\
                                    "+ result.data[key].name +" в "+ minuteToTime(result.data[key].time_from);
                            }
                        }
                        showNotifications(textNotification, 7000, NOTIF_RED);
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

        $('#check-fill').click(function(){
            $('#checkFillModal').modal('show');
        });
        $('#check-fill-update').click(function(){
            goPreloader();
            var data = {
                trigger: 'check-fill',
                month: nowDate.getMonth() + 1,
                year: nowDate.getFullYear(),
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    $('#check-fill-result').empty();
                    var result = JSON.parse(data);
                    console.log(result);
                    if(result.length){
                        for(var key in result){
                            var dateObj = new Date(result[key].date);
                            var createContainer = document.createElement('div');
                            createContainer.innerHTML = normalizeDate(dateObj.getDate() +"." +dateObj.getMonth() +"." +dateObj.getFullYear())
                                + " на спектакле <b>" + result[key].event_name +"</b> в " + minuteToTime(result[key].time_from)
                                + " не проставлена роль " +result[key].name +" " +result[key].surname;
                            
                            document.getElementById('check-fill-result').append(createContainer);
                        }
                    }else{
                        showNotifications("Расписание полностью заполнено", 3000, NOTIF_GREEN);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        // Автозаполнение роли на основе пустых клеток
        var magicEmptySchedule = [];
//        var magicUnderstudySelected = [];
        var magicUnderstudyMode = false;
        var magicCastId = false;
        var magicUserId = false;
        $('#two--tbody').on('click', '.two--magic-add-schedule', function(){
            magicEmptySchedule = [];
            magicUnderstudyMode = this.parentNode.parentNode.classList.contains('understudy');
            magicCastId = this.parentNode.parentNode.dataset.cast;
            magicUserId = this.parentNode.parentNode.dataset.id;
            var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                if(+rows[i].dataset.cast == +magicCastId){
                    var cells = rows[i].getElementsByTagName('td');
                    for (var z = 0; z < cells.length; z++) {
                        if (activeCells.includes(z + 1)) {
                            magicEmptySchedule[magicEmptySchedule.length] = cells[z].dataset.schedule;
                        }
                    }
                    if(magicEmptySchedule.length) break;
                }
            }
            for (var i = 0; i < rows.length; i++) {
                if(+rows[i].dataset.cast == +magicCastId){
                    var cells = rows[i].getElementsByTagName('td');
                    for (var z = 0; z < cells.length; z++) {
                        if(cells[z].innerHTML == '+'){
                            var idx = magicEmptySchedule.indexOf(cells[z].dataset.schedule);
                            magicEmptySchedule.splice(idx, 1);
                        }
                    }

                }
            }
            console.log(magicEmptySchedule);
            $('#magicModal').modal('show');
//            alert('ok');
        });
        
        $('#magic-schedule-submit').click(function(){
            if(!magicEmptySchedule.length){
                showNotifications("Кажется все дни уже проставлены", 3000, NOTIF_RED);
                $('#magicModal').modal('hide');
                return false;
            }
            goPreloader();
            var data = {
                trigger: 'magic-add-schedule',
                scheduleList: magicEmptySchedule,
                user: magicUserId,
                cast: magicCastId,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/schedule/two',
                data: data,
                success: function (data) {
                    $('#check-fill-result').empty();
                    var result = JSON.parse(data);
                    if(result.result == 'ok'){
                        var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
                        for (var i = 0; i < rows.length; i++) {
                            if(+rows[i].dataset.cast == +magicCastId && +rows[i].dataset.id == +magicUserId){
                                var cells = rows[i].getElementsByTagName('td');
                                for (var z = 0; z < cells.length; z++) {
                                    if (magicEmptySchedule.includes(cells[z].dataset.schedule)) {
                                        cells[z].innerHTML = '+';
                                    }
                                }
                            }
                        }
                    }else if(result.result = 'error'){
                        var textNotification = '';
                        for(var key in result.data){
                            if(result.data[key].time_to){
                                textNotification += "Конфликт. Данный сотрудник уже стоит на \n\
                                    "+ result.data[key].name +" с "+ minuteToTime(result.data[key].time_from) +" до "+ minuteToTime(result.data[key].time_to);
                            }else{
                                textNotification += "Конфликт. Данный сотрудник уже стоит на \n\
                                    "+ result.data[key].name +" в "+ minuteToTime(result.data[key].time_from);
                            }
                        }
                        showNotifications(textNotification, 7000, NOTIF_RED);
                    }
                    $('#magicModal').modal('hide');
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });


        function insertActor(actorName, actorId, castId, understudyMode) {
            var createTr = document.createElement('tr');
            if (understudyMode)
                createTr.className = 'understudy';
            createTr.dataset.id = actorId;
            createTr.dataset.cast = castId;
//            if(!understudyModel) createTr.dataset.cast = castId;
            var createName = document.createElement('th');
            createName.innerHTML = actorName + " <span class='badge badge-pill badge-danger two--remove-cast'><i class='fas fa-times'></i></span> " 
                    + (!understudyMode ? "<span class='badge badge-pill badge-info two--add-understudy'><i class='fas fa-share'></i></span>" : "") + " <span class='badge badge-pill badge-warning two--magic-add-schedule'><i class='fas fa-magic'></i></span>";
            createTr.append(createName);
            var scheduleCells = document.getElementsByClassName('two--event-cell');
            for (var i = 0; i < scheduleCells.length; i++) {
                var createTd = document.createElement('td');
                createTd.dataset.schedule = scheduleCells[i].dataset.schedule;
                createTr.append(createTd);
            }
            ;
            if (understudyMode) {
                var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
                var z = 0;
                for (var i = 0; i < rows.length; i++) {
                    if (z && rows[i].dataset.cast != castId && z.dataset.cast == castId) {
                        document.getElementById('two--tbody').insertBefore(createTr, rows[i]);
                        return true;
                    }
                    z = rows[i];
                }
                if (rows[rows.length - 1].dataset.cast == castId) {
                    document.getElementById('two--tbody').append(createTr);
                }
            } else {
                document.getElementById('two--tbody').append(createTr);
            }
        }
        function deleteActor(actorId, castId, understudy) {
            var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
            if (understudy) {
                for (var i = 0; i < rows.length; i++) {
                    if (rows[i].dataset.id == actorId)
                        rows[i].remove();
                }
            } else {
                $('#two--tbody > tr').remove('[data-cast=' + castId + ']');
            }
        }
        // Проверка на повторное добавление актера в состав
        function checkRepeatCast(actorId, understudy, cast) {
            var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
            if (understudy) {
                for (var i = 0; i < rows.length; i++) {
                    if (+rows[i].dataset.id == +actorId && +cast == +rows[i].dataset.cast) {
                        return false;
                    }
                }
            } else {
                for (var i = 0; i < rows.length; i++) {
                    if (+rows[i].dataset.id == +actorId && !rows[i].classList.contains('understudy')) {
                        return false;
                    }
                }
            }

            return true;
        }

        function renderSchedule(schedule) {
            var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
            for (var key in schedule) {
                for (var i = 0; i < rows.length; i++) {
                    if (+rows[i].dataset.id == +schedule[key].user_id && +rows[i].dataset.cast == +schedule[key].cast_id) {
                        var cells = rows[i].getElementsByTagName('td');
                        for (var z = 0; z < cells.length; z++) {
                            if (+cells[z].dataset.schedule == +schedule[key].schedule_event_id) {
                                cells[z].innerHTML = '+';
                            }
                        }
                    }
                }
            }
        }

        // Выделение ячеек при наведении
        $('#two--tbody').on('mouseenter', 'td', function () {
            var thisIndex = $(this).index();
            var rowIndex = $(this).parent().index();
            var rowCells = this.parentNode.getElementsByTagName('td');
            this.parentNode.querySelector('th').style.backgroundColor = 'rgb(179, 255, 255)';
            for (var i = 0; i < thisIndex; i++) {
                rowCells[i].style.backgroundColor = 'rgb(179, 255, 255)';
            }
            var rows = document.getElementById('two--tbody').getElementsByTagName('tr');
            for (var i = 0; i < rowIndex; i++) {
                var rc = rows[i].getElementsByTagName('td');
                rc[thisIndex - 1].style.backgroundColor = 'rgb(179, 255, 255)';
            }
        });
        $('#two--tbody').on('mouseleave', 'td', function () {
            $('#two--tbody td').css({'background-color': ''});
            $('#two--tbody th').css({'background-color': ''});
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


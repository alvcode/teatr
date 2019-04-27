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

        var data = {
            trigger: 'load-schedule',
            month: 4,
            year: 2019,
        };
        data[csrfParam] = csrfToken;
        $.ajax({
            type: "POST",
            url: '/schedule/two',
            data: data,
            success: function (data) {
                scheduleData = JSON.parse(data);
                console.log(scheduleData);
                renderThead(scheduleData);
            },
            error: function () {
                showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                stopPreloader();
            }
        });
        
        /**
         * Рендерит Thead таблицы
         * @param {object} data
         */
        function renderThead(data){
            $('#two--thead').empty();
            
            var dateContainer = document.createElement('tr');
            dateContainer.className = 'two--thead-date';
            dateContainer.append(document.createElement('th'));
            
            var eventContainer = document.createElement('tr');
            eventContainer.className = 'two--thead-event';
            eventContainer.append(document.createElement('th'));
            
            var timeContainer = document.createElement('tr');
            timeContainer.className = 'two--thead-time';
            var createTh = document.createElement('th');
            createTh.innerHTML = 'Состав';
            timeContainer.append(createTh);
            
            for(var key in data){
                var dateCell = document.createElement('th');
                dateCell.innerHTML = key;
                var eventCount = 0; // Счетчик общего кол-ва мероприятий
                for(var keyEvent in data[key]){
                    var eventCell = document.createElement('th');
                    var timeCount = 0; // Счетчик кол-ва повторов спектакля
                    for(var keyTime in data[key][keyEvent]){
                        if(timeCount === 0){
                            eventCell.innerHTML = data[key][keyEvent][keyTime].event.name;
                        }
                        var timeCell = document.createElement('th');
                        timeCell.innerHTML = data[key][keyEvent][keyTime].time_from;
                        timeContainer.append(timeCell);
                        timeCount++;
                    }
                    eventCount += timeCount;
                    eventCell.setAttribute('colspan', timeCount);
                    eventContainer.append(eventCell);
                }
                dateCell.setAttribute('colspan', eventCount);
                dateContainer.append(dateCell);
            }
            
            document.getElementById('two--thead').append(dateContainer);
            document.getElementById('two--thead').append(eventContainer);
            document.getElementById('two--thead').append(timeContainer);
            
        };


    }
</script>


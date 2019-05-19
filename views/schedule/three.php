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
    
    

</div>
<br>


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


<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Расписание актеров';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .two--container {
  max-width: 100%;
  /*max-height: 100%;*/
  overflow: scroll;
  position: relative;
}

table {
  position: relative;
  border-collapse: collapse;
}

td, th {
  padding: 0.25em;
}

thead th {
  position: -webkit-sticky; /* for Safari */
  position: sticky;
  top: 0;
  background: #000;
  color: #FFF;
}

thead th:first-child {
  left: 0;
  z-index: 1;
}

tbody th {
  position: -webkit-sticky; /* for Safari */
  position: sticky;
  left: 0;
  background: #FFF;
  border-right: 1px solid #CCC;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>

            <?= $this->render('../templates/_flash') ?>

        </div>
    </div>
    
    <div class="two--container">
<table class="table table-striped">
<thead>
<tr>
  <th></th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
  <th>head</th>
</tr>
</thead>
<tbody>
<tr>
  <th>head</th>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
</tr>
<tr>
  <th>head</th>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
  <td>body</td>
</tr>
</tbody>
</table>
</div>
    
</div>
<br>

<?php // echo "<pre>";print_r($events); ?>



<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
        
        $('.two--container').css({'height': $(window).height() - 140});
        
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
                console.log(JSON.parse(data));
            },
            error: function () {
                showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                stopPreloader();
            }
        });
        
        

    }
</script>


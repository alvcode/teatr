<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Залы и мероприятия';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            
            <?= $this->render('../templates/_flash') ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <h4><b>Добавить мероприятие</b></h4>
                    
                </div>
                <div class="col-lg-6">
                    <h4><b>Добавить зал</b></h4>
                    
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($roomModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm'])
                        ->label("Название зала <span class='text-danger'>*</span>") ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                    
                    <div class="mrg-top45">
                        <ul class="list-group proff-list">
                            <?php foreach ($rooms as $key => $value): ?>
                            <li data-room="<?= $value['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="room-name"><?= $value['name'] ?></div>
                                <div>
                                    <span class="badge badge-info badge-pill edit-room-name cursor-pointer">Изменить</span>
                                    <span class="badge badge-danger badge-pill delete-room cursor-pointer">Удалить</span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php /* echo "<pre>"; print_r($rooms); */ ?>
                    </div>
                    
                </div>
            </div>

            
        </div>
    </div>

</div>

<script>
window.onload = function () {
    
var csrfParam = $('meta[name="csrf-param"]').attr("content");
var csrfToken = $('meta[name="csrf-token"]').attr("content");

var actionRoom = false;
$('.edit-room-name').click(function(){
    actionRoom = this.parentNode.parentNode.dataset.room;
    var roomName = this.parentNode.parentNode.getElementsByClassName('room-name')[0].innerHTML;
    var roomNameObj = this.parentNode.parentNode.getElementsByClassName('room-name')[0];
    var createInput = document.createElement('input');
    createInput.value = roomName;
    var createOk = document.createElement('div');
    createOk.className = 'btn btn-sm btn-success edit-room-submit';
    createOk.innerHTML = 'ok';
    roomNameObj.innerHTML = '';
    roomNameObj.append(createInput);
    roomNameObj.append(createOk);
});

$('body').on('click', '.edit-room-submit', function(){
    goPreloader();
    var roomName = this.parentNode.getElementsByTagName('input')[0].value;
    var self = this;
    var data = {
        trigger: 'rename-room',
        roomId: actionRoom,
        roomName: roomName,
    };
    data[csrfParam] = csrfToken;
    $.ajax({
        type: "POST",
        url: '/panel/room-event',
        data: data,
        success: function(data){
           if(data == 1){
               self.parentNode.innerHTML = roomName;
               showNotifications("Зал успешно переименован", 3000, NOTIF_GREEN);
            }else if(data == 0){
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
    
}
</script>
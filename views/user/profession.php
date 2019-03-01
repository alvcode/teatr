<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Службы и должности';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <h4><b>Добавить службу</b></h4>
                    <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($addCategory, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm'])
                        ->label("Служба <span class='text-danger'>*</span>") ?>

                    <div class="form-group">
                        <div class="col-lg-offset-1 col-lg-11">
                            <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>
                <div class="col-lg-6">
                    <?php if($prof): ?>
                    <h4><b>Добавить должность</b></h4>
                        <?php $form = ActiveForm::begin(['id' => 'add-profession']) ?>
                        <?= $form->field($addProfession, 'proff_cat_id')->dropDownList(
                                yii\helpers\ArrayHelper::map($prof,
                                    'id',
                                    'name'), ['class' => 'form-control form-control-sm']
                            )->label("Служба <span class='text-danger'>*</span>") ?>
                    
                        <?= $form->field($addProfession, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                    ->textInput(['class' => 'form-control form-control-sm'])
                                    ->label("Должность <span class='text-danger'>*</span>") ?>
                    
                        <div class="form-group">
                            <div class="col-lg-offset-1 col-lg-11">
                                <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                            </div>
                        </div>
                        <?php ActiveForm::end() ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <?php if($prof): ?>
                        <br><br>
                        <?php foreach ($prof as $key => $value): ?>
                        <ul class="list-group mrg-top30">
                            <li class="list-group-item cat-list active" data-category="<?= $value['id'] ?>"><?= $value['name'] ?></li>
                            <li class="list-group-item">
                                <ul class="list-group proff-list">
                                    <?php foreach ($value['professions'] as $keyProf => $valueProf): ?>
                                        <li class="list-group-item"><?= $valueProf['name'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
          
            
            <?php 
//            echo "<pre>";
//            print_r($prof);
            ?>
            
        </div>
    </div>
</div>


<script>
window.onload = function () {
    
    var csrfParam = $('meta[name="csrf-param"]').attr("content");
    var csrfToken = $('meta[name="csrf-token"]').attr("content");

    // AJAX-обработка добавления должности
    $('#add-profession').on('beforeSubmit', function(){
        goPreloader();
        var professionName = $('#profession-name').val();
        var catId = $('#profession-proff_cat_id').val();
        var data = {
                trigger: 'add-profession',
                catId: catId,
                professionName: professionName,
            };
            data[csrfParam] = csrfToken;
         $.ajax({
             type: "POST",
             url: '/user/profession',
             data: data,
             success: function(data){
                 if(data == 1){
                    var createLi = document.createElement('li');
                    createLi.className = 'list-group-item';
                    createLi.innerHTML = professionName;
                    var categoryList = document.getElementsByClassName('cat-list');
                    for(var i = 0; i < categoryList.length; i++){
                        if(categoryList[i].dataset.category == catId){
                            categoryList[i].parentNode.getElementsByClassName('proff-list')[0].append(createLi);
                        }
                    }
                    showNotifications("Должность успешно добавлена", 3000, NOTIF_GREEN);
                 }else if(data == 0){
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                 }
                 stopPreloader();
             },
         });
      
        return false;
    });

}
</script>


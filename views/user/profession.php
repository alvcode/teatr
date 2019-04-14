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

            <?= $this->render('../templates/_flash') ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-info">
                        <div class="card-header"><h5 class="card-title">Добавить службу</h5></div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin() ?>
                            <?=
                                    $form->field($addCategory, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                    ->textInput(['class' => 'form-control form-control-sm'])
                                    ->label("Служба <span class='text-danger'>*</span>")
                            ?>

                            <div class="form-group">
                                <div class="col-lg-offset-1 col-lg-11">
                                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                                </div>
                            </div>
                            <?php ActiveForm::end() ?>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6">
                    <?php if ($prof): ?>
                        <div class="card border-info">
                            <div class="card-header"><h5 class="card-title">Добавить должность</h5></div>
                            <div class="card-body">
                                <?php $form = ActiveForm::begin(['id' => 'add-profession']) ?>
                                <?=
                                $form->field($addProfession, 'proff_cat_id')->dropDownList(
                                        yii\helpers\ArrayHelper::map($prof, 'id', 'name'), ['class' => 'form-control form-control-sm']
                                )->label("Служба <span class='text-danger'>*</span>")
                                ?>

                                <?=
                                        $form->field($addProfession, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                                        ->textInput(['class' => 'form-control form-control-sm'])
                                        ->label("Должность <span class='text-danger'>*</span>")
                                ?>

                                <div class="form-group">
                                    <div class="col-lg-offset-1 col-lg-11">
                                        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success btn-sm']) ?>
                                    </div>
                                </div>
                                <?php ActiveForm::end() ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?php if ($prof): ?>
                        <br><br>
                        <?php foreach ($prof as $key => $value): ?>
                            <ul class="list-group mrg-top30">
                                <li class="list-group-item d-flex justify-content-between align-items-center cat-list" data-category="<?= $value['id'] ?>">
                                    <div class="cat-name"><h4><b><?= $value['name'] ?></b></h4></div>
                                    <div>
                                        <span class="badge badge-info badge-pill edit-cat-name cursor-pointer">Изменить</span>
                                        <span class="badge badge-danger badge-pill delete-cat cursor-pointer">Удалить</span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <ul class="list-group proff-list">
                                        <?php foreach ($value['professions'] as $keyProf => $valueProf): ?>
                                            <li data-proff="<?= $valueProf['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center prof-li">
                                                <div class="proff-name"><?= $valueProf['name'] ?></div>
                                                <div>
                                                    <span class="badge badge-info badge-pill edit-prof-name cursor-pointer">Изменить</span>
                                                    <span class="badge badge-danger badge-pill delete-prof cursor-pointer">Удалить</span>
                                                </div>
                                                <!-- <div class="btn btn-sm btn-danger">Удалить</div> -->
                                            </li>
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

<!-- Modal delete profession -->
<div class="modal fade" id="deleteProfModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Будет выполнена проверка, если
                кто-то из пользователей имеет данную профессию, то удаление будет невозможно.
                Продолжить?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-prof-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete category -->
<div class="modal fade" id="deleteCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление категории и всех вложенных профессий?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Будет выполнена проверка, если
                кто-то из пользователей имеет хотя бы одну профессию из списка, то удаление будет невозможно.
                Продолжить?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-cat-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>


<script>
    window.onload = function () {

        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        // AJAX-обработка добавления должности
        $('#add-profession').on('beforeSubmit', function () {
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
                success: function (data) {
                    if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    } else {
                        var result = JSON.parse(data);
                        var createLi = document.createElement('li');
                        createLi.className = 'list-group-item d-flex justify-content-between align-items-center prof-li';
                        createLi.dataset.proff = result;

                        var createNameContainer = document.createElement('div');
                        createNameContainer.className = 'proff-name';
                        createNameContainer.innerHTML = professionName;

                        var createActionsContainer = document.createElement('div');
                        var createActionsEdit = document.createElement('span');
                        createActionsEdit.innerHTML = 'Изменить';
                        createActionsEdit.className = 'badge badge-info badge-pill cursor-pointer edit-prof-name';
                        var createActionsDelete = document.createElement('span');
                        createActionsDelete.innerHTML = 'Удалить';
                        createActionsDelete.className = 'badge badge-danger badge-pill cursor-pointer delete-prof';

                        createActionsContainer.append(createActionsEdit);
                        createActionsContainer.append(createActionsDelete);

                        createLi.append(createNameContainer);
                        createLi.append(createActionsContainer);

                        var categoryList = document.getElementsByClassName('cat-list');
                        for (var i = 0; i < categoryList.length; i++) {
                            if (categoryList[i].dataset.category == catId) {
                                categoryList[i].parentNode.getElementsByClassName('proff-list')[0].append(createLi);
                            }
                        }
                        showNotifications("Должность успешно добавлена", 3000, NOTIF_GREEN);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });

            return false;
        });

        var actionProf = false;
        $('body').on('click', '.edit-prof-name', function () {
            actionProf = this.parentNode.parentNode.dataset.proff;
            var profName = this.parentNode.parentNode.getElementsByClassName('proff-name')[0].innerHTML;
            var profNameObj = this.parentNode.parentNode.getElementsByClassName('proff-name')[0];
            var createInput = document.createElement('input');
            createInput.value = profName;
            var createOk = document.createElement('div');
            createOk.className = 'btn btn-sm btn-success edit-prof-submit';
            createOk.innerHTML = 'ok';
            profNameObj.innerHTML = '';
            profNameObj.append(createInput);
            profNameObj.append(createOk);
        });

        $('body').on('click', '.edit-prof-submit', function () {
            goPreloader();
            var profName = this.parentNode.getElementsByTagName('input')[0].value;
            var self = this;
            var data = {
                trigger: 'rename-profession',
                profId: actionProf,
                professionName: profName,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/profession',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        self.parentNode.innerHTML = profName;
                        showNotifications("Должность успешно переименована", 3000, NOTIF_GREEN);
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

        $('body').on('click', '.delete-prof', function () {
            actionProf = this.parentNode.parentNode.dataset.proff;
            $('#deleteProfModal').modal('show');
        });

        $('#delete-prof-submit').click(function () {
            goPreloader();
            var data = {
                trigger: 'remove-profession',
                profId: actionProf,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/profession',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        $('.prof-li[data-proff=' + actionProf + ']').remove();
                        showNotifications("Должность успешно удалена", 3000, NOTIF_GREEN);
                        $('#deleteProfModal').modal('hide');
                    } else if (data == 2) {
                        showNotifications('Похоже, что данная профессия присвоена кому-то из сотрудников', 7000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });

        var actionCat = false;
        $('.edit-cat-name').click(function () {
            actionCat = this.parentNode.parentNode.dataset.category;
            var catName = this.parentNode.parentNode.getElementsByClassName('cat-name')[0].querySelector('b').innerHTML;
            var catNameObj = this.parentNode.parentNode.getElementsByClassName('cat-name')[0];
            var createInput = document.createElement('input');
            createInput.value = catName;
            var createOk = document.createElement('div');
            createOk.className = 'btn btn-sm btn-success edit-cat-submit';
            createOk.innerHTML = 'ok';
            catNameObj.innerHTML = '';
            catNameObj.append(createInput);
            catNameObj.append(createOk);
        });

        $('body').on('click', '.edit-cat-submit', function () {
            goPreloader();
            var catName = this.parentNode.getElementsByTagName('input')[0].value;
            var self = this;
            var data = {
                trigger: 'rename-category',
                catId: actionCat,
                categoryName: catName,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/profession',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        self.parentNode.innerHTML = catName;
                        showNotifications("Категория успешно переименована", 3000, NOTIF_GREEN);
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

        $('.delete-cat').click(function () {
            $('#deleteCatModal').modal('show');
            actionCat = this.parentNode.parentNode.dataset.category;
        });

        $('#delete-cat-submit').click(function () {
            // Запрос на удаление категории с проверкой на присвоение кому-либо
            var data = {
                trigger: 'delete-category',
                catId: actionCat,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/profession',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        $('.cat-list[data-category=' + actionCat + ']').parent().remove();
                        $('#deleteCatModal').modal('hide');
                        showNotifications("Категория успешно удалена", 3000, NOTIF_GREEN);
                    } else if (data == 2) {
                        showNotifications("Похоже, что какая-либо из профессий присвоена сотруднику(ам)", 7000, NOTIF_RED);
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


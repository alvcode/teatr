<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="site-login">-->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            <?php
//            echo "<pre>";
//            print_r($users);
            ?>
            <div>
                <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#newUserModal"><i class="fas fa-plus"></i> Новый пользователь</button>
            </div>

            <div class="mrg-top15 table-responsive-sm">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Имя</th>
                            <th scope="col">Фамилия</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">Телефон</th>
                            <th scope="col">Роль</th>
                            <th scope="col">Создан</th>
                            <th scope="col">Последний визит</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $key => $value): ?>
                            <tr>
                                <th scope="row"><?= $value['id'] ?></th>
                                <td><?= $value['name'] ?></td>
                                <td><?= $value['surname'] ?></td>
                                <td><?= $value['email'] ?></td>
                                <td>+<?= $value['number'] ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php if ($value->role): ?>
                                            <?php echo $value->role->item_name ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= $value['date_register'] ?></td>
                                <td>-</td>
                                <td>
                                    <a class="btn btn-sm btn-success f-s10" href="/user/user-single?id=<?= $value['id'] ?>"><i class="fas fa-edit"></i></a>
                                    <a class="btn btn-sm btn-danger f-s10" href="#"><i class="fas fa-times"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal new user -->
<div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Добавить пользователя</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin([
                            'id' => 'new-user-form'
                ]);
                ?>

                <?= $form->field($userModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

                <?= $form->field($userModel, 'surname', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

                <?= $form->field($userModel, 'email', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

                <?=
                        $form->field($userModel, 'number', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'p--number form-control', 'inputmode' => 'numeric', 'pattern' => '\+7?[\(][0-9]{3}[\)]{0,1}\s?\d{3}[-]{0,1}\d{4}'])
                ?>

                <?= $form->field($userModel, 'password', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->passwordInput() ?>

                <?=
                $form->field($userModel, 'user_role')->dropDownList(\yii\helpers\ArrayHelper::map($roleList, 'name', 'description'), [
                    'prompt' => 'Выберите роль',
                ])->label("Роль")
                ?>

<?php ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" id="users--new-user-return" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="users--new-user-button" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function () {

        // Маска ввода для номера телефона
        $(".p--number").mask("+7(999) 999-9999", {clearIfNotMatch: true});
        $(".p--number").click(function () {
            if ($(this).val().length > 4) {
                return false;
            } else {
                $(this).val("+7");
            }
        });

        $('#users--new-user-button').click(function () {
            var self = $(this);
            self.prop('disabled', true);
            var data = $('#new-user-form').serialize();
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        window.location.reload();
//                    $('#newUserModal').modal('hide');
//                    $('#new-user-form')[0].reset();
                    } else if (data == 0) {

                    }
                    self.prop('disabled', false);
                },
            });
        });

        $('#newUserModal').on('hidden.bs.modal', function (e) {
            $('#new-user-form')[0].reset();
        });


    }
</script>


<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\Formatt;

$this->title = 'Сотрудники';
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
            
            <form class="my-2" id="search-form">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <input class="form-control form-control-sm mr-sm-2" name="search" id="str-search" placeholder="Поиск по имени/фамилии/номеру" aria-label="Search">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <button id="search-submit" class="btn btn-sm btn-outline-success my-2 my-sm-0" type="submit">Найти</button>
                        </div>
                    </div>
                </div>
            </form>
            
            <div id="user--search-result" class="table-responsive-sm">
                <h4>Результат поиска:</h4>
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
                    <tbody id="search-result-tbody">

                    </tbody>
                </table>
            </div>
            
            <div class="mrg-top45 table-responsive-sm">
                <h4>Список сотрудников:</h4>
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
                            <tr class="user-row" data-user="<?= $value['id'] ?>">
                                <th scope="row"><?= $value['id'] ?></th>
                                <td><?= $value['name'] ?></td>
                                <td><?= $value['surname'] ?></td>
                                <td><?= $value['email'] ?></td>
                                <td><?= $value['number']?"+".$value['number']:"-" ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php if ($value->role): ?>
                                            <?php echo $value->role->item_name ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= Formatt::dateMysqlToForm($value['date_register']) ? Formatt::dateMysqlToForm($value['date_register']) : "-" ?></td>
                                <td><?= Formatt::dateMysqlToForm($value['last_login']) ? Formatt::dateMysqlToForm($value['last_login']) : "-" ?></td>
                                <td>
                                    <a class="btn btn-sm btn-success f-s10" href="/user/user-single?id=<?= $value['id'] ?>"><i class="fas fa-edit"></i></a>
                                    <div class="btn btn-sm btn-danger f-s10 delete-user"><i class="fas fa-times"></i></div>
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

                <?= $form->field($userModel, 'password', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->textInput() ?>

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
        
        var csrfParam = $('meta[name="csrf-param"]').attr("content");
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

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
        
        // Удаление юзера
        $('.delete-user').click(function(){
            var userId = this.parentNode.parentNode.dataset.user;
            var self = $(this);
            self.prop('disabled', true);
            var data = {
                trigger: 'delete-user',
                id: userId,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        var userRows = document.getElementsByClassName('user-row');
                        for(var i = 0; i < userRows.length; i++){
                            if(userRows[i].dataset.user == userId){
                                userRows[i].remove();
                            }
                        }
                        showNotifications('Пользователь успешно удален', 3000, NOTIF_GREEN);
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_GREEN);
                    }
                    self.prop('disabled', false);
                },
            });
        });
        
        // Поиск юзера
        $('#search-submit').click(function (e) {
            e.preventDefault();
            goPreloader();
            var searchStr = document.getElementById('str-search').value;
            var data = {
                trigger: 'search-user',
                str: searchStr,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result) {
                        document.getElementById('search-result-tbody').innerHTML = '';
                        for(var key in result){
                            console.log(result);
                            var createTR = document.createElement('tr');
                            createTR.className = 'user-row';
                            createTR.dataset.user = result[key].id;

                            var item1 = document.createElement('th');
                            var item2 = document.createElement('td');
                            var item3 = document.createElement('td');
                            var item4 = document.createElement('td');
                            var item5 = document.createElement('td');
                            var item6 = document.createElement('td');
                            var item7 = document.createElement('td');
                            var item8 = document.createElement('td');
                            var item9 = document.createElement('td');

                            item1.innerHTML = result[key].id;
                            item2.innerHTML = result[key].name;
                            item3.innerHTML = result[key].surname;


                            createTR.appendChild(item1);
                            createTR.appendChild(item2);
                            createTR.appendChild(item3);
                            createTR.appendChild(item4);
                            createTR.appendChild(item5);
                            createTR.appendChild(item6);
                            createTR.appendChild(item7);
                            createTR.appendChild(item8);
                            createTR.appendChild(item9);

                            document.getElementById('search-result-tbody').appendChild(createTR);
                        }
                        
                        document.getElementById('user--search-result').style.display = 'block';
                    } else {
                        document.getElementById('search-result-tbody').innerHTML = '';
                        document.getElementById('user--search-result').style.display = 'none';
                        showNotifications('Поиск не дал результатов', 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
            });
        });


    }
</script>


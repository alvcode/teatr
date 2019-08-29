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
    <div class="hidden" hidden>
        <div class="row timesheet-item">
            <div class="col-5">
                <select class="form-control form-control-sm timesheet-event-type">
                    <?php foreach ($eventType as $key => $value): ?>
                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-5">
                <select class="form-control form-control-sm timesheet-method">
                    <option value="1">Часы</option>
                    <option value="2">Выходы</option>
                </select>
            </div>
            <div class="col-2">
                <div class="btn btn-sm btn-outline-danger timesheet-delete"><i class="fas fa-times"></i></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>

            <?= $this->render('../templates/_flash') ?>
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
                <div class="row justify-content-between">
                    <div class="col-4">
                        <h4>Список сотрудников:</h4>
                    </div>
                    <div class="col-3"></div>
                    <div class="col-5 text-right form-inline">
                        <div class="input-group mb-3">
                            <a href="/user/index?act=sort&val=asc" class="btn btn-sm <?= (isset($sort['act']) && $sort['act'] == 'sort' && $sort['val'] == 'asc') ? "btn-success" : "btn-outline-info" ?> ml-1">По порядку</a>
                            <a href="/user/index?act=sort&val=surname" class="btn btn-sm <?= (isset($sort['act']) && $sort['act'] == 'sort' && $sort['val'] == 'surname') ? "btn-success" : "btn-outline-info" ?> ml-1">По фамилии</a>
                            <form method="get" class="form-inline my-lg-0 mrg-top15">
                                <input type="hidden" name="act" value="sortProf">
                                <select style="width:85%;" name="val" class="form-control-sm form-control mr-sm-2">
                                    <?php foreach ($categories as $key => $value): ?>
                                        <option value="<?= $value['id'] ?>" <?= (isset($sort['act']) && $sort['act'] == 'sortProf' && $sort['val'] == $value['id']) ? "selected" : "" ?>><?= $value['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- <div class="input-group-append"> -->
                                    <button class="btn my-2 my-sm-0 btn-sm <?= (isset($sort['act']) && $sort['act'] == 'sortProf') ? "btn-success" : "btn-outline-info" ?> clean-input" type="submit" id="button-addon2">ok</button>
                                <!-- </div> -->
                            </form>
                        </div>

                    </div>
                </div>
                <table class="table table-sm table-striped mt-2">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Фамилия</th>
                            <th scope="col">Имя</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">Телефон</th>
                            <th scope="col">Роль</th>
                            <th scope="col">Должность</th>
                            <th scope="col">Табель</th>
                            <th scope="col">Создан</th>
                            <th scope="col">Последний визит</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody class="f-s13">
                        <?php foreach ($users as $key => $value): ?>
                            <tr class="user-row" data-user="<?= $value['id'] ?>">
                                <th scope="row"><?= $value['id'] ?></th>
                                <td><?= $value['surname'] ?></td>
                                <td><?= $value['name'] ?></td>
                                <td><?= $value['email'] ?></td>
                                <td><?= $value['number'] ? "+" . $value['number'] : "-" ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?php if ($value->role): ?>
                                            <?php echo $value->role->item_name ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= $value['userProfession']['prof']['name'] ?></td>
                                <td class="cursor-pointer text-secondary get-timesheet"><i class="fas fa-hand-point-up"></i></td>
                                <td><?= Formatt::dateMysqlToForm($value['date_register']) ? Formatt::dateMysqlToForm($value['date_register']) : "-" ?></td>
                                <td><?= Formatt::dateMysqlToForm($value['last_login']) ? Formatt::dateMysqlToForm($value['last_login']) : "-" ?></td>
                                <td>
                                    <a href="/user/login-as?id=<?= $value['id'] ?>" class="btn btn-sm btn-info f-s10"><i class="fas fa-sign-out-alt"></i></a>
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

                <?=
                        $form->field($userModel, 'name', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm', 'id' => 'new-name'])
                ?>

                <?=
                        $form->field($userModel, 'surname', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm', 'id' => 'new-surname'])
                ?>

                <?=
                        $form->field($userModel, 'email', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm', 'id' => 'new-email'])
                ?>

                <?=
                        $form->field($userModel, 'number', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'p--number form-control form-control-sm', 'id' => 'new-number', 'inputmode' => 'numeric', 'pattern' => '\+7?[\(][0-9]{3}[\)]{0,1}\s?\d{3}[-]{0,1}\d{4}'])
                ?>

                <?=
                        $form->field($userModel, 'password', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])
                        ->textInput(['class' => 'form-control form-control-sm', 'id' => 'new-password'])
                ?>

                <?=
                $form->field($userModel, 'user_role')->dropDownList(\yii\helpers\ArrayHelper::map($roleList, 'name', 'description'), [
                    'prompt' => 'Выберите роль',
                    'class' => 'form-control form-control-sm',
                    'id' => 'new-role',
                ])->label("Роль")
                ?>

                <?=
                $form->field($profModel, 'prof_id', ['errorOptions' => ['class' => 'form-text text-danger', 'tag' => 'small']])->dropDownList(\yii\helpers\ArrayHelper::map($categories, 'id', 'name'), [
                    'prompt' => 'Должность',
                    'class' => 'form-control form-control-sm',
                    'id' => 'new-prof',
                ])->label("Должность")
                ?>
                
                <div class="form-group">
                    <label class="control-label">Расчет табеля</label>
                    <div id="add-timesheet-icon" class="btn btn-sm btn-outline-info"><i class="fas fa-plus-circle"></i></div>
                    <div class="timesheet-blocks mrg-top15">
                        
                    </div>
                </div>

<?php ActiveForm::end(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" id="users--new-user-return" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="users--new-user-button" type="button" class="btn btn-sm btn-success">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal delete user -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Пользователь будет навсегда удален из системы. Продолжить?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="delete-user-submit" type="button" class="btn btn-sm btn-success">Продолжить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal timesheet user -->
<div class="modal fade" id="timesheetUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Конфигурация расчета табеля для сотрудника</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="get-timesheet-name text-center font-weight-bold"></div>
                <div class="text-center mrg-top15">
                    <div id="add-timesheet-get" class="btn btn-sm btn-outline-info"><i class="fas fa-plus-circle"></i></div>
                </div>
                <div class="get-timesheet-content mrg-top15"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="timesheet-user-submit" type="button" class="btn btn-sm btn-success">Сохранить</button>
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
        
        $('#add-timesheet-icon').click(function(){
            var cloneItem = document.getElementsByClassName('timesheet-item')[0].cloneNode(true);
            document.getElementsByClassName('timesheet-blocks')[0].append(cloneItem);
        });
        
        $('body').on('click', '.timesheet-delete', function(){
            this.parentNode.parentNode.remove();
        });

        $('#users--new-user-button').click(function (e) {
            e.preventDefault();
            var timesheetItems = document.getElementsByClassName('timesheet-blocks')[0].getElementsByClassName('timesheet-item');
            var timesheetConfig = [];
            for(var i = 0; i < timesheetItems.length; i++){
                var k = timesheetConfig.length;
                timesheetConfig[k] = {};
                timesheetConfig[k].eventType = timesheetItems[i].getElementsByClassName('timesheet-event-type')[0].value;
                timesheetConfig[k].method = timesheetItems[i].getElementsByClassName('timesheet-method')[0].value;
            }
            console.log(timesheetConfig);
            if (!$('#new-password').val()) {
                showNotifications("Не заполнен пароль", 3000, NOTIF_RED);
                return false;
            }
            if (!$('#new-role').val()) {
                showNotifications("Не выбрана роль пользователя", 3000, NOTIF_RED);
                return false;
            }
            if (!$('#new-prof').val()) {
                showNotifications("Не выбрана профессия", 3000, NOTIF_RED);
                return false;
            }
            if(!timesheetConfig.length){
                showNotifications("Не заполнена настройка табелей", 3000, NOTIF_RED);
                return false;
            }
            var data = {
                trigger: 'new-user',
                name: $('#new-name').val(),
                surname: $('#new-surname').val(),
                email: $('#new-email').val(),
                number: $('#new-number').val(),
                password: $('#new-password').val(),
                userRole: $('#new-role').val(),
                profId: $('#new-prof').val(),
                timesheet: timesheetConfig
            };
            data[csrfParam] = csrfToken;
            var self = $(this);
            self.prop('disabled', true);
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.result == 'ok') {
                        window.location.reload();
                    } else if (result.result == 'error') {
                        showNotifications(result.data, 7000, NOTIF_RED);
                    }
                    self.prop('disabled', false);
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    self.prop('disabled', false);
                    stopPreloader();
                }
            });
        });

        $('#newUserModal').on('hidden.bs.modal', function (e) {
            $('#new-user-form')[0].reset();
            $('.timesheet-blocks').empty();
        });

        // Удаление юзера
        var userForDelete = false;
        $('body').on('click', '.delete-user', function () {
            userForDelete = this.parentNode.parentNode.dataset.user;
            $('#deleteUserModal').modal('show');
        });

        $('#delete-user-submit').click(function () {
            goPreloader();
            var data = {
                trigger: 'delete-user',
                id: userForDelete,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    if (data == 1) {
                        var userRows = document.getElementsByClassName('user-row');
                        for (var i = 0; i < userRows.length; i++) {
                            if (userRows[i].dataset.user == userForDelete) {
                                userRows[i].remove();
                            }
                        }
                        $('#deleteUserModal').modal('hide');
                        showNotifications('Пользователь успешно удален', 3000, NOTIF_GREEN);
                    } else if (data == 0) {
                        showNotifications(NOTIF_TEXT_ERROR, 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
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
                        for (var key in result) {
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
                            item4.innerHTML = result[key].email;
                            item5.innerHTML = '+' + result[key].number;
                            item6.innerHTML = "<span class='badge badge-success'>" + result[key].role.item_name + "</span>";
                            item7.innerHTML = result[key].date_register;
                            item8.innerHTML = result[key].last_login;

                            var createLoginAs = document.createElement('a');
                            createLoginAs.setAttribute('href', '/user/login-as?id=' + result[key].id);
                            createLoginAs.className = 'btn btn-sm btn-info f-s10';
                            createLoginAs.innerHTML = '<i class="fas fa-sign-out-alt"></i> ';

                            var createEdit = document.createElement('a');
                            createEdit.className = 'btn btn-sm btn-success f-s10';
                            createEdit.setAttribute('href', '/user/user-single?id=' + result[key].id);
                            createEdit.innerHTML = '<i class="fas fa-edit"></i> ';

                            var createDelete = document.createElement('div');
                            createDelete.className = 'btn btn-sm btn-danger f-s10 delete-user';
                            createDelete.innerHTML = '<i class="fas fa-times"></i> ';

                            item9.appendChild(createLoginAs);
                            item9.appendChild(createEdit);
                            item9.appendChild(createDelete);

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
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        var changeTimesheetUser = false;
        $('.get-timesheet').dblclick(function(){
            goPreloader();
            this.classList.add('text-success');
            changeTimesheetUser = this.parentNode.dataset.user;
            var fullName = this.parentNode.getElementsByTagName('td')[0].innerHTML +" " +this.parentNode.getElementsByTagName('td')[1].innerHTML;
//            alert(fullName); return false;
            var data = {
                trigger: 'get-timesheet',
                userId: changeTimesheetUser,
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.result == 'ok') {
                        console.log(result.data);
                        if(!result.data.length){
                            $('.get-timesheet-name').html(fullName);
                            $('.get-timesheet-content').html("<div class='text-center'>Настройка табеля отсутствует</div>");
                        }else{
                            $('.get-timesheet-name').html(fullName);
                            renderTimesheetConfig(result.data);
                        }
                        $('#timesheetUserModal').modal('show');
                    } else if(result.result == 'error') {
                        showNotifications('Поиск не дал результатов', 3000, NOTIF_RED);
                    }
                    stopPreloader();
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    stopPreloader();
                }
            });
        });
        
        $('#add-timesheet-get').click(function(){
            var cloneItem = document.getElementsByClassName('timesheet-item')[0].cloneNode(true);
            if(!document.getElementsByClassName('get-timesheet-content')[0].getElementsByClassName('timesheet-item').length){
                $('.get-timesheet-content').empty();
            }
            document.getElementsByClassName('get-timesheet-content')[0].append(cloneItem);
        });
        
        $('#timesheet-user-submit').click(function(){
            var timesheetItems = document.getElementsByClassName('get-timesheet-content')[0].getElementsByClassName('timesheet-item');
            var timesheetConfig = [];
            for(var i = 0; i < timesheetItems.length; i++){
                var k = timesheetConfig.length;
                timesheetConfig[k] = {};
                timesheetConfig[k].eventType = timesheetItems[i].getElementsByClassName('timesheet-event-type')[0].value;
                timesheetConfig[k].method = timesheetItems[i].getElementsByClassName('timesheet-method')[0].value;
            }
            if(!timesheetConfig.length){
                showNotifications("Не заполнена настройка табелей", 3000, NOTIF_RED);
                return false;
            }
            console.log(timesheetConfig);
            var data = {
                trigger: 'set-timesheet',
                timesheet: timesheetConfig,
                userId: changeTimesheetUser
            };
            data[csrfParam] = csrfToken;
            $.ajax({
                type: "POST",
                url: '/user/index',
                data: data,
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.result == 'ok') {
                        $('#timesheetUserModal').modal('hide');
                        showNotifications('Настройка табеля изменена', 2000, NOTIF_GREEN);
                    } else if (result.result == 'error') {
                        showNotifications(result.data, 7000, NOTIF_RED);
                    }
                },
                error: function () {
                    showNotifications(NOTIF_TEXT_ERROR, 7000, NOTIF_RED);
                    self.prop('disabled', false);
                    stopPreloader();
                }
            });
        });
        
        function renderTimesheetConfig(data){
            var contentBlock = document.getElementsByClassName('get-timesheet-content')[0];
            for(var i = 0; i < data.length; i++){
                var cloneItem = document.getElementsByClassName('timesheet-item')[0].cloneNode(true);
                var cloneEventsType = cloneItem.getElementsByClassName('timesheet-event-type')[0].getElementsByTagName('option');
                var cloneMethods = cloneItem.getElementsByClassName('timesheet-method')[0].getElementsByTagName('option');
                for(var k = 0; k < cloneEventsType.length; k++){
                    if(+data[i].event_type_id === +cloneEventsType[k].value){
                        cloneEventsType[k].selected = true;
                    }else{
                        cloneEventsType[k].selected = false;
                    }
                }
                for(var z = 0; z < 2; z++){
                    if(+data[i].method === (z + 1)){
                        cloneMethods[z].selected = true;
                    }else{
                        cloneMethods[z].selected = false;
                    }
                }
                contentBlock.append(cloneItem);
            }
        }
        
        $('#timesheetUserModal').on('hidden.bs.modal', function (e) {
            $('.get-timesheet-name').empty();
            $('.get-timesheet-content').empty();
        });


    }
</script>


<?php
/* @var $this yii\web\View */
/* @var $perm */
/* @var $roles */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

$this->params['breadcrumbs'][] = [
    'template' => "<li class=\"active\">{link}</li>\n",
    'label' => 'Роли'
];
$this->title = 'Управление ролями';
?>
<style>
    .main-list-ul{
        font-size: 14px;
    }
    .parent-li{
        margin-top: 18px;
    }
    .parent-li > div{
        font-weight: 700;
    }
    .children-perm{
        margin-left: 20px;
    }
    .children-perm li{
        margin-top: 6px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="main--page-title">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
        </div>
    </div>
    
    <?= $this->render('../templates/_flash') ?>
    
    <div class="row">
        <div class="col-lg-6">
            <form method="post" id="add-role-form" action="">
                <div class="form-group">
                    <label for="new-role">Новая роль</label>
                    <input id="new-role" class="form-control" type="text">
                </div>
                <div class="form-group">
                    <label for="new-role-descr">Описание роли</label>
                    <textarea id="new-role-descr" class="form-control"></textarea>
                </div>
                <button class="btn btn-success add-role">Добавить</button>
                <br><br>
                <ul class="main-list-ul">
                    <?php foreach ($roles as $key => $value): ?>
                        <li class="parent-li">
                            <div><?= $value['name'] ?> (<?= $value['description'] ?>), <a href="#" data-name="<?= $value['name'] ?>" class="delete-role">Удалить</a></div>
                            <?php if ($value['children']): ?>
                                <ul class="children-perm">
                                    <?php foreach ($value['children'] as $keyC => $valueC): ?>
                                        <li data-type="<?= $valueC['type'] ?>" data-name="<?= $valueC['name'] ?>"><b><?= $valueC['type'] === '1' ? "Роль" : "Разрешение" ?>:</b> <?= $valueC['description'] ?> (<?= $valueC['name'] ?>), <a class="delete-child" data-parent="<?= $value['name'] ?>" href="#">Удалить</a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <div style="margin-top: 6px;">
                                <a href="#assign-perm" role="button" data-role="<?= $value['name'] ?>" data-toggle="modal" class="btn btn-danger btn-sm assign-permission">Добавить разрешения</a>
                                <a href="#assign-role" role="button" data-role="<?= $value['name'] ?>" data-toggle="modal" class="btn btn-danger btn-sm assign-role">Присвоить роль</a>
                            </div>
                        </li>
                        <hr>
                    <?php endforeach; ?>
                </ul>
            </form>
        </div>

        <div class="col-lg-6">
            <form method="post" id="add-perm-form" action="">
                <div class="form-group">
                    <label for="new-perm">Новое разрешение</label>
                    <input id="new-perm" class="form-control" type="text">
                </div>
                <div class="form-group">
                    <label for="new-perm-descr">Описание разрешения</label>
                    <textarea id="new-perm-descr" class="form-control"></textarea>
                </div>
                <button class="btn btn-success add-role">Добавить</button>
                <br><br>
                <ul class="main-list-ul">
                    <?php foreach ($perm as $key => $value): ?>
                        <li>
                            <div><?= $value['name'] ?> (<?= $value['description'] ?>), <a href="#" data-name="<?= $value['name'] ?>" class="delete-permission">Удалить</a></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>
        </div>
    </div>

    
<div class="modal fade" id="assign-perm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Присвоить новые разрешения</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" size="15" id="assign-perm-select" multiple>
                        <?php foreach ($perm as $value): ?>
                            <option value="<?= $value['name'] ?>"><?= $value['description'] ?></option>
                        <?php endforeach; ?>
                    </select>
            </div>
            <div class="modal-footer">
                <button type="button" id="users--new-user-return" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="assign-perm-submit" type="button" class="btn btn-sm btn-success">Присвоить</button>
            </div>
        </div>
    </div>
</div>
    
    <!-- Modal new user -->
<div class="modal fade" id="assign-role" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Присвоить роль</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <select class="form-control" size="15" id="assign-role-select" multiple>
                        <?php foreach ($roles as $value): ?>
                            <option value="<?= $value['name'] ?>"><?= $value['description'] ?></option>
                        <?php endforeach; ?>
                    </select>
            </div>
            <div class="modal-footer">
                <button type="button" id="users--new-user-return" class="btn btn-sm btn-danger" data-dismiss="modal">Отмена</button>
                <button id="assign-role-submit" type="button" class="btn btn-sm btn-success">Присвоить</button>
            </div>
        </div>
    </div>
</div>




    <script>
        window.onload = function () {

            var csrfParam = $('meta[name="csrf-param"]').attr("content");
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            // AJAX-обработка добавления роли
            $('#add-role-form').on('submit', function () {
                var data = {
                    trigger: 'add-role',
                    name: document.getElementById('new-role').value,
                    description: document.getElementById('new-role-descr').value,
                };
                data[csrfParam] = csrfToken;
                $.ajax({
                    type: "POST",
                    url: '/user/rbac',
                    data: data,
                    success: function (data) {
                        if (data == 0) {
                            alert('Ошибка');
                        } else if (data == 1) {
                            window.location.reload();
                        }
                    },
                });

                return false;
            });

            // AJAX-обработка добавления разрешения
            $('#add-perm-form').on('submit', function () {
                var data = {
                    trigger: 'add-perm',
                    name: document.getElementById('new-perm').value,
                    description: document.getElementById('new-perm-descr').value,
                };
                data[csrfParam] = csrfToken;
                $.ajax({
                    type: "POST",
                    url: '/user/rbac',
                    data: data,
                    success: function (data) {
                        if (data == 0) {
                            alert('Ошибка');
                        } else if (data == 1) {
                            window.location.reload();
                        }
                    },
                });

                return false;
            });

            // Обработка назначения разрешений
            var roleName = '';
            $('.assign-permission').click(function () {
                if (this.parentNode.parentNode.getElementsByClassName('children-perm')[0] !== undefined) {
                    var childrenList = this.parentNode.parentNode.getElementsByClassName('children-perm')[0].querySelectorAll('li');
                    var childrenArr = [];
                    for (var i = 0; i < childrenList.length; i++) {
                        childrenArr[i] = childrenList[i].dataset.name;
                    }
                    $('#assign-perm-select option:selected').each(function (index) {
                        this.selected = false;
                    });
                    $('#assign-perm-select option').each(function (index) {
                        if (childrenArr.includes($(this).val())) {
                            this.selected = true;
                        }
                    });
                }
                roleName = this.dataset.role;
            });


            var permList = {};
            $('#assign-perm-select').change(function () {
                $('#assign-perm-select option:selected').each(function (index) {
                    permList[index] = $(this).val();
                });
            }).change();


            $('#assign-perm-submit').click(function () {
                var data = {
                    trigger: 'assign-perm',
                    nameRole: roleName,
                    permList: permList,
                };
                data[csrfParam] = csrfToken;
                $.ajax({
                    type: "POST",
                    url: '/user/rbac',
                    data: data,
                    success: function (data) {
                        //                 console.log(JSON.parse(data));
                        if (data == 0) {
                            alert('Ошибка');
                        } else if (data == 1) {
                            window.location.reload();
                        }
                        roleName = '';
                    },
                });
            });


            // Обработка назначения ролей
            var roleList = {};
            $('#assign-role-select').change(function () {
                $('#assign-role-select option:selected').each(function (index) {
                    roleList[index] = $(this).val();
                });
            }).change();

            $('.assign-role').click(function () {
                roleName = this.dataset.role;
                if (this.parentNode.parentNode.getElementsByClassName('children-perm')[0] !== undefined) {
                    var childrenList = this.parentNode.parentNode.getElementsByClassName('children-perm')[0].querySelectorAll('li');
                    var childrenArr = [];
                    for (var i = 0; i < childrenList.length; i++) {
                        childrenArr[i] = childrenList[i].dataset.name;
                    }

                    $('#assign-role-select option').each(function (index) {
                        this.removeAttribute('disabled', 'true');
                        this.selected = false;
                    });
                    $('#assign-role-select option').each(function (index) {
                        if ($(this).val() == roleName) {
                            this.setAttribute('disabled', 'true');
                        }
                        if (childrenArr.includes($(this).val())) {
                            this.selected = true;
                        }
                    });
                }
            });

            $('#assign-role-submit').click(function () {
                var data = {
                    trigger: 'assign-role',
                    nameRole: roleName,
                    roleList: roleList,
                };
                data[csrfParam] = csrfToken;
                $.ajax({
                    type: "POST",
                    url: '/user/rbac',
                    data: data,
                    success: function (data) {
                        console.log(JSON.parse(data));
                        if (data == 0) {
                            alert('Ошибка');
                        } else if (data == 1) {
                            window.location.reload();
                        }
                        roleName = '';
                    },
                });
            });

            $('.delete-child').click(function (e) {
                e.preventDefault();
                var question = confirm('Вы уверены?');
                if (question) {
                    var nameParent = this.dataset.parent;
                    var nameChild = this.parentNode.dataset.name;
                    var typeChild = this.parentNode.dataset.type;
                    var data = {
                        trigger: 'delete-child',
                        nameChild: nameChild,
                        nameParent: nameParent,
                        typeChild: typeChild,
                    };
                    data[csrfParam] = csrfToken;
                    $.ajax({
                        type: "POST",
                        url: '/user/rbac',
                        data: data,
                        success: function (data) {
                            console.log(JSON.parse(data));
                            if (data == 0) {
                                alert('Ошибка');
                            } else if (data == 1) {
                                window.location.reload();
                            }
                        },
                    });
                } else {
                    return false;
                }
            });

            $('.delete-permission').click(function (e) {
                e.preventDefault();
                var question = confirm('Вы уверены?');
                if (question) {
                    var name = this.dataset.name;
                    var data = {
                        trigger: 'delete-perm',
                        name: name,
                    };
                    data[csrfParam] = csrfToken;
                    $.ajax({
                        type: "POST",
                        url: '/user/rbac',
                        data: data,
                        success: function (data) {
                            console.log(JSON.parse(data));
                            if (data == 0) {
                                alert('Ошибка');
                            } else if (data == 1) {
                                window.location.reload();
                            }
                        },
                    });
                } else {
                    return false;
                }
            });

            $('.delete-role').click(function (e) {
                e.preventDefault();
                var question = confirm('Вы уверены?');
                if (question) {
                    var name = this.dataset.name;
                    var data = {
                        trigger: 'delete-role',
                        name: name,
                    };
                    data[csrfParam] = csrfToken;
                    $.ajax({
                        type: "POST",
                        url: '/user/rbac',
                        data: data,
                        success: function (data) {
                            console.log(JSON.parse(data));
                            if (data == 0) {
                                alert('Ошибка');
                            } else if (data == 1) {
                                window.location.reload();
                            }
                        },
                    });
                } else {
                    return false;
                }
            });


        }
    </script>
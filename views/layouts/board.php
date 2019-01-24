<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Menu;
//use yii\bootstrap\Nav;
//use yii\bootstrap\NavBar;
//use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    
    <div id="board-container">
        
        <div class="board--top-sidebar">
            <div class="board--top-sidebar--title">
                <h4><b>Театриум</b></h4>
            </div>
            <div class="board--top-sidebar--humburger">
                <i id="board-humburger" class="fas fa-bars"></i>
            </div>
            <div class="board--top-sidebar--null"></div>
            <div class="board--top-sidebar--account">
                alvcode@ya.ru <i id="board--top-sidebar-angle" class="fas fa-angle-down"></i>
                <div class="board--top-sidebar--account-more" hidden>
                    <a href="/site/logout">Выйти</a>
                </div>
            </div>
        </div>

        <div class="board--left-sidebar">
            <?php echo Menu::widget([
                'items' => [
                    ['label' => 'Главная панель', 'url' => ['panel/index'],
                        'template' => '<a href="{url}"><div><i class="fas fa-desktop"></i></div><div>{label}</div></a>',
                    ],
//                    ['label' => 'Сводное расписание', 'url' => ['schedule/one'],
//                        'template' => '<a href="{url}"><div><i class="fas fa-calendar-alt"></i></div><div>{label}</div></a>',
//                    ],
                    ['label' => 'Сотрудники', 'url' => ['user/index'],
                        'template' => '<a href="{url}"><div><i class="fas fa-users"></i></i></div><div>{label}</div></a>',
                    ],
                    ['label' => 'Управление правами', 'url' => ['user/rbac'],
                        'template' => '<a href="{url}"><div><i class="fas fa-users"></i></i></div><div>{label}</div></a>',
                    ],
                    ['label' => 'Настройки', 'url' => ['users/index'],
                        'template' => '<a href="{url}"><div><i class="fas fa-cog"></i></i></div><div>{label}</div></a>',
                    ],
                ],
            ]); ?>
        </div>

        <div class="board-content">
            <?= $content ?>
        </div>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

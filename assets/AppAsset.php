<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i&amp;subset=cyrillic',
        'css/site.css',
        'css/bootstrap.min.css',
        'css/fontawesome/fontawesome-all.css',
        'css/animate.css',
        'css/croppie.css',
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
        'css/board.css',
        'css/style.css',
    ];
    public $js = [
        'js/jquery_cookie_1.4.1.js',
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js',
        'js/croppie.js',
        'js/jquery.mask.js',
        'js/board.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
//        'yii\bootstrap\BootstrapAsset',
    ];
}

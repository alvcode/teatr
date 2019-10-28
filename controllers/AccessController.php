<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class AccessController extends Controller
{
    public function beforeAction($action) {
        
        if (Yii::$app->getUser()->isGuest){
            return Yii::$app->getResponse()->redirect('/site/index/')->send();
        }
        if((int)Yii::$app->getUser()->identity->modified_password == 0){
            return Yii::$app->getResponse()->redirect('/site/edit-password/')->send();
        }
//        if (!Yii::$app->getUser()->isGuest && Yii::$app->getUser()->identity->confirm_mail == 0) {
//            return Yii::$app->getResponse()->redirect('/site/notconfirm/')->send();
//        }

        return parent::beforeAction($action);
    }
}
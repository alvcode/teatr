<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Room;
use app\models\Config;
use app\models\EventType;
use app\models\ProffCategories;
use yii\filters\AccessControl;

class SettingController extends AccessController
{
    
    public $layout = 'board';
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['visible_config'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(){
        
        /**
         * Добавление нового зала, который должен отображаться в сводном
         * 1 - ОК
         * 2 - Этот зал уже добавлен
         */
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'add-simple-config'){
                $setConfig = Config::setConfig(Yii::$app->request->post('configName'), [Yii::$app->request->post('configValue')]);
                if($setConfig){
                    return 1;
                }
            }
            if(Yii::$app->request->post('trigger') == 'delete-simple-config'){
                if(Config::removeConfig(Yii::$app->request->post('configName'), Yii::$app->request->post('configValue'))){
                    return 1;
                }
            }
            
            return 0;
        }
        
//        $scheduleOneRooms = Config::getConfig('schedule_one_rooms');
        $rooms = Room::find()->where(['is_active' => 1])->asArray()->all();
        
//        $scheduleTwoEventType = Config::getConfig('schedule_two_event_type');
//        $spectacleEvent = Config::getConfig('spectacle_event');
        $eventType = EventType::find()->where(['is_active' => 1])->asArray()->all();
        
//        $actorsProfCat = Config::getConfig('actors_prof_cat');
        $allConfig = Config::getAllConfig();
        $actorsCat = ProffCategories::find()->asArray()->all();
        
        return $this->render('index', [
            'rooms' => $rooms,
//            'scheduleOneRooms' => $scheduleOneRooms,
//            'scheduleTwoEventType' => $scheduleTwoEventType,
            'eventType' => $eventType,
            'actorsCat' => $actorsCat,
            'allConfig' => $allConfig,
//            'actorsProfCat' => $actorsProfCat,
//            'spectacleEvent' => $spectacleEvent,
        ]);
    }
    

}

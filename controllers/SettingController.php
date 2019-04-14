<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Room;
use app\models\Config;

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
            if(Yii::$app->request->post('trigger') == 'schedule-one-add-room'){
                $setConfig = Config::setConfig('schedule_one_rooms', Yii::$app->request->post('room'));
                if($setConfig){
                    return 1;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'schedule-one-delete-room'){
                if(Config::removeConfig('schedule_one_rooms', Yii::$app->request->post('room'))){
                    return 1;
                }
            }
            
            
            return 0;
        }
        $findConfig = Config::find()->where(['name_config' => 'schedule_one_rooms'])->asArray()->one();
        $findConfig['value'] = json_decode(json_decode($findConfig['value']), true);
        $rooms = Room::find()->where(['is_active' => 1])->asArray()->all();
        
        return $this->render('index', [
            'rooms' => $rooms,
            'scheduleOneRooms' => $findConfig,
        ]);
    }
    

}

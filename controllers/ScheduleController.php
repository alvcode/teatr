<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Room;
use app\models\EventType;
use app\models\Events;
use app\models\EventCategories;

class ScheduleController extends AccessController
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
    public function actionOne(){
        $rooms = Room::find()->where(['is_active' => 1])->asArray()->all();
        $eventType = EventType::find()->where(['is_active' => 1])->asArray()->all();
        $events = Events::find()->where(['is_active' => 1])->asArray()->all();
//        $eventCategories = EventCategories::find()->asArray()->all();
        
        return $this->render('one', [
            'rooms' => $rooms,
            'eventType' => $eventType,
            'events' => $events,
//            'eventCategories' => $eventCategories,
        ]);
    }
    

}

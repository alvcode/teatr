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
use app\models\ScheduleEvents;
use app\models\Config;

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
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'add-schedule'){
                $scheduleEvent = new ScheduleEvents();
                $scheduleEvent->event_type_id = Yii::$app->request->post('eventType');
                $scheduleEvent->event_id = Yii::$app->request->post('event');
                $scheduleEvent->room_id = Yii::$app->request->post('room');
                $scheduleEvent->date = date('Y-m-d', mktime(0, 0, 0, Yii::$app->request->post('date')['month'] + 1, Yii::$app->request->post('date')['day'], Yii::$app->request->post('date')['year']));
                $scheduleEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
                    $scheduleEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                }
                if($scheduleEvent->validate() && $scheduleEvent->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'load-schedule'){
                $schedule = ScheduleEvents::find()
                        ->where(['=', 'year(date)', Yii::$app->request->post('year')])
                        ->andWhere(['=', 'month(date)', Yii::$app->request->post('month')])
                        ->with('eventType')->with('event')->asArray()->all();
                return json_encode($schedule);
            }
        }
        $roomConfig = Config::getConfig('schedule_one_rooms');
        $rooms = Room::find()->where(['is_active' => 1, 'id' => $roomConfig])->asArray()->all();
        $eventType = EventType::find()->where(['is_active' => 1])->asArray()->all();
        $events = Events::find()->where(['is_active' => 1])->asArray()->all();
        $eventCategories = EventCategories::find()->asArray()->all();
        
        return $this->render('one', [
            'rooms' => $rooms,
            'eventType' => $eventType,
            'events' => $events,
            'eventCategories' => $eventCategories,
        ]);
    }
    

}

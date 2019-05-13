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
use app\components\ScheduleComponent;
use app\models\User;
use app\models\Casts;
use app\models\CastUnderstudy;
use app\models\UserInSchedule;

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
                    $record = ScheduleEvents::find()
                        ->where(['id' => $scheduleEvent->id])
                        ->with('eventType')->with('event')->asArray()->one();
                return json_encode($record);
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
            
            if(Yii::$app->request->post('trigger') == 'delete-event'){
                $findEvent = ScheduleEvents::findOne(Yii::$app->request->post('id'));
                if(!$findEvent) return 0;
                if($findEvent->delete()) return 1;
            }
            
            if(Yii::$app->request->post('trigger') == 'edit-event'){
                $findEvent = ScheduleEvents::findOne(Yii::$app->request->post('id'));
                $findEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
                    $findEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                }else{
                    $findEvent->time_to = '';
                }
                if($findEvent->validate() && $findEvent->save()){
                    $record = ScheduleEvents::find()
                        ->where(['id' => $findEvent->id])
                        ->with('eventType')->with('event')->asArray()->one();
                return json_encode($record);
                }
            }
            
            return 0;
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
    
    public function actionTwo(){
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'load-schedule'){
                $configEventType = Config::getConfig('schedule_two_event_type');
                $schedule = ScheduleEvents::find()
                        ->where(['=', 'year(date)', Yii::$app->request->post('year')])
                        ->andWhere(['=', 'month(date)', Yii::$app->request->post('month')])
                        ->andWhere(['event_type_id' => $configEventType])
                        ->with('eventType')->with('event')->asArray()->all();
                return json_encode(ScheduleComponent::transformEventsToTwo($schedule));
            }
            
            if(Yii::$app->request->post('trigger') == 'add-in-cast'){
                $newCast = new Casts();
                $newCast->event_id = Yii::$app->request->post('event');
                $newCast->user_id = Yii::$app->request->post('user');
                $newCast->month = Yii::$app->request->post('month');
                $newCast->year = Yii::$app->request->post('year');
                if($newCast->save()) return $newCast->id;
            }
            
            if(Yii::$app->request->post('trigger') == 'load-casts-in-schedule'){
                $result = [];
                $result['cast'] = User::find()->select('user.id, user.name, user.surname, casts.id cast_id, cast_understudy.user_id')
                        ->leftJoin('casts', 'casts.user_id = user.id')->leftJoin('cast_understudy', 'cast_understudy.cast_id = casts.id')
                        ->where([
                            'casts.year' => Yii::$app->request->post('year'), 
                            'casts.month' => Yii::$app->request->post('month'), 
                            'casts.event_id' => Yii::$app->request->post('event'),
                        ])
                        ->asArray()->all();
                $result['schedule'] = UserInSchedule::find()->select('user_in_schedule.*')
                        ->leftJoin('schedule_events', 'schedule_events.id = user_in_schedule.schedule_event_id')
                        ->where(['=', 'year(schedule_events.date)', Yii::$app->request->post('year')])
                        ->andWhere(['=', 'month(schedule_events.date)', Yii::$app->request->post('month')])
                        ->andWhere(['schedule_events.event_id' => Yii::$app->request->post('event')])
                        ->asArray()->all();
                        
                $result['cast'] = ScheduleComponent::joinUnderstudy($result['cast']);
                return json_encode($result);
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-actor-in-cast'){
                $cast = \Yii::$app->db->createCommand()->delete('casts', [
                    'year' => Yii::$app->request->post('year'),
                    'month' => Yii::$app->request->post('month'),
                    'user_id' => Yii::$app->request->post('user'),
                    'event_id' => Yii::$app->request->post('event'),
                ])->execute();
                if($cast) return 1;
            }
            
            if(Yii::$app->request->post('trigger') == 'add-in-understudy'){
                $understudy = new CastUnderstudy();
                $understudy->cast_id = Yii::$app->request->post('cast');
                $understudy->user_id = Yii::$app->request->post('user');
                if($understudy->save()) return 1;
            }
            if(Yii::$app->request->post('trigger') == 'delete-understudy'){
                $deleteUnderstudy = Yii::$app->db->createCommand()->delete('cast_understudy', [
                    'cast_id' => Yii::$app->request->post('cast'),
                    'user_id' => Yii::$app->request->post('user'),
                ])->execute();
                if($deleteUnderstudy) return 1;
            }
            /**
             * 1 - Добавлен на мероприятие
             * 2 - Удален с мероприятия
             */
            if(Yii::$app->request->post('trigger') == 'add-user-schedule'){
                $findInSchedule = UserInSchedule::find()->where([
                    'schedule_event_id' => Yii::$app->request->post('schedule'),
                    'user_id' => Yii::$app->request->post('user'),
                ])->asArray()->one();
                if($findInSchedule){
                    $deleteInSchedule = Yii::$app->db->createCommand()->delete('user_in_schedule', [
                        'schedule_event_id' => Yii::$app->request->post('schedule'),
                        'user_id' => Yii::$app->request->post('user'),
                    ])->execute();
                    if($deleteInSchedule) return 2;
                }else{
                    $userInSchedule = new UserInSchedule();
                    $userInSchedule->schedule_event_id = Yii::$app->request->post('schedule');
                    $userInSchedule->user_id = Yii::$app->request->post('user');
                    if($userInSchedule->save()) return 1;
                }
            }
            
            
            return 0;
        }
        
        $configActorsProf = Config::getConfig('actors_prof_cat');
        $actors = User::find()->select('user.id, user.name, user.surname')
                ->leftJoin('user_profession', 'user.id = user_profession.user_id')
                ->leftJoin('profession', 'user_profession.prof_id = profession.id')
                ->where(['profession.proff_cat_id' => $configActorsProf])
                ->andWhere(['user.is_active' => 1])
                ->asArray()->all();
        
        return $this->render('two', [
            'actors' => $actors,
        ]);
    }
    

}

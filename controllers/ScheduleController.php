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
use yii\base\Exception;

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
     * Action сводного расписания
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
    
    /**
     * Action расписания актеров
     */
    public function actionTwo(){
        
        if(Yii::$app->request->isAjax){
            // Загрузка мероприятий
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
                $response = [];
                $response['data'] = [];
                $actorsArr = Yii::$app->request->post('user');
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try {
                    foreach ($actorsArr as $key => $value){
                        $newCast = new Casts();
                        $newCast->event_id = Yii::$app->request->post('event');
                        $newCast->user_id = $value;
                        $newCast->month = Yii::$app->request->post('month');
                        $newCast->year = Yii::$app->request->post('year');
                        if($newCast->save()){
                            $count = count($response['data']);
                            $response['data'][$count]['user'] = $value;
                            $response['data'][$count]['cast'] = $newCast->id;
                        }
                    }
                    $transaction->commit();
                    $response['result'] = 'ok';
                    return json_encode($response);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    $response['result'] = 'error';
                    return json_encode($response);
                }
            }
            
            /**
             * Загружаем состав на выбранное мероприятие
             * ok - Есть данные для заполнения
             * last - нет данных, но найден состав из предыдущих месяцев
             * empty - нет данных
             */
            
            if(Yii::$app->request->post('trigger') == 'load-casts-in-schedule'){
//                return json_encode(ScheduleComponent::searchLastCast(Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'), 12));
                $data = [];
                $data = ScheduleComponent::loadCastInSchedule(Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'));
                $data['cast'] = ScheduleComponent::joinUnderstudy($data['cast']);
                
                if(!$data['cast']){
                    $searchCast = ScheduleComponent::searchLastCast(Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'), 12);
                    if($searchCast){
                        $result['result'] = 'last';
                        $result['data'] = $searchCast;
                    }else{
                        $result['result'] = 'empty';
                        $result['data'] = [];
                    }
                }else{
                    $result['result'] = 'ok';
                    $result['data'] = $data;
                }
                return json_encode($result);
            }
            
            if(Yii::$app->request->post('trigger') == 'add-last-cast'){
                $data = ScheduleComponent::loadCastInSchedule(Yii::$app->request->post('searchMonth'), Yii::$app->request->post('searchYear'), Yii::$app->request->post('event'));
                $data['cast'] = ScheduleComponent::joinUnderstudy($data['cast']);
                if(ScheduleComponent::copyLastCast($data['cast'], Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'))){
                    $addedData = ScheduleComponent::loadCastInSchedule(Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'));
                    $addedData['cast'] = ScheduleComponent::joinUnderstudy($addedData['cast']);
                    return json_encode([
                        'result' => 'ok',
                        'data' => $addedData
                        ]);
                }else{
                    return json_encode(['result' => 'error']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-actor-in-cast'){
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try{
                    $findCast = Casts::find()->where([
                        'year' => Yii::$app->request->post('year'),
                        'month' => Yii::$app->request->post('month'),
                        'user_id' => Yii::$app->request->post('user'),
                        'event_id' => Yii::$app->request->post('event'),
                    ])->one();
                    $findUnderstudy = CastUnderstudy::find()->where([
                        'cast_id' => $findCast->id
                    ])->all();
                    if($findUnderstudy){
                        foreach ($findUnderstudy as $key => $value){
                            \Yii::$app->db->createCommand()->delete('user_in_schedule', [
                                'user_id' => $value->user_id,
                                'cast_id' => $value->cast_id
                            ])->execute();
                            $value->delete();
                        }
                    }
                    \Yii::$app->db->createCommand()->delete('user_in_schedule', [
                        'user_id' => Yii::$app->request->post('user'),
                        'cast_id' => $findCast->id
                    ])->execute();
                    if($findCast->delete()){
                        $transaction->commit();
                        return 1;   
                    }
                }catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'add-in-understudy'){
                $response = [];
                $response['data'] = [];
                $actorsArr = Yii::$app->request->post('user');
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try {
                    foreach ($actorsArr as $key => $value){
                        $understudy = new CastUnderstudy();
                        $understudy->cast_id = Yii::$app->request->post('cast');
                        $understudy->user_id = $value;
                        if($understudy->save()){
                            $count = count($response['data']);
                            $response['data'][$count]['user'] = $value;
                            $response['data'][$count]['cast'] = $understudy->cast_id;
                        }
                    }
                    $transaction->commit();
                    $response['result'] = 'ok';
                    return json_encode($response);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    $response['result'] = 'error';
                    return json_encode($response);
                }
            }
            if(Yii::$app->request->post('trigger') == 'delete-understudy'){
                $deleteUnderstudy = Yii::$app->db->createCommand()->delete('cast_understudy', [
                    'cast_id' => Yii::$app->request->post('cast'),
                    'user_id' => Yii::$app->request->post('user'),
                ])->execute();
                \Yii::$app->db->createCommand()->delete('user_in_schedule', [
                    'user_id' => Yii::$app->request->post('user'),
                    'cast_id' => Yii::$app->request->post('cast')
                ])->execute();
                if($deleteUnderstudy) return 1;
            }
            /**
             * ok - Добавлен на мероприятие
             * deleted - Удален с мероприятия
             * error - есть пересечения
             */
            if(Yii::$app->request->post('trigger') == 'add-user-schedule'){
                $findInSchedule = UserInSchedule::find()->where([
                    'schedule_event_id' => Yii::$app->request->post('schedule'),
                    'user_id' => Yii::$app->request->post('user'),
                    'cast_id' => Yii::$app->request->post('cast'),
                ])->asArray()->one();
                if($findInSchedule){
                    $deleteInSchedule = Yii::$app->db->createCommand()->delete('user_in_schedule', [
                        'schedule_event_id' => Yii::$app->request->post('schedule'),
                        'user_id' => Yii::$app->request->post('user'),
                        'cast_id' => Yii::$app->request->post('cast'),
                    ])->execute();
                    if($deleteInSchedule) return json_encode(['result' => 'deleted']);
                }else{
                    $checkIntersect = ScheduleComponent::checkIntersect(Yii::$app->request->post('schedule'), Yii::$app->request->post('user'));
                    if(!$checkIntersect){
                        $userInSchedule = new UserInSchedule();
                        $userInSchedule->schedule_event_id = Yii::$app->request->post('schedule');
                        $userInSchedule->user_id = Yii::$app->request->post('user');
                        $userInSchedule->cast_id = Yii::$app->request->post('cast');
                        if($userInSchedule->save()) return json_encode(['result' => 'ok']);
                    }else{
                        return json_encode(['result' => 'error', 'data' => $checkIntersect]);
                    }
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'magic-add-schedule'){
                $scheduleList = Yii::$app->request->post('scheduleList');
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try {
                    foreach ($scheduleList as $key => $value){
                        $checkIntersect = ScheduleComponent::checkIntersect($value, Yii::$app->request->post('user'));
                        if(!$checkIntersect){
                            $transaction->db->createCommand()->insert('user_in_schedule', [
                                'schedule_event_id' => $value,
                                'user_id' => Yii::$app->request->post('user'),
                                'cast_id' => Yii::$app->request->post('cast')
                            ])->execute();
                        }else{
                            throw new Exception('Конфликт расписания');
                        }
                    }
                    $transaction->commit();
                    $response['result'] = 'ok';
                    return json_encode($response);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    $response['result'] = 'error';
                    $response['data'] = $checkIntersect;
                    return json_encode($response);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'check-fill'){
                return json_encode(ScheduleComponent::checkFullSchedule(Yii::$app->request->post('month'), Yii::$app->request->post('year')));
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
        
        $actors = ScheduleComponent::sortFirstLetter($actors, 'surname', true);
        
        return $this->render('two', [
            'actors' => $actors,
        ]);
    }
    
    /**
     * Action недельного расписания
     */
    public function actionThree(){
        return $this->render('three');
    }
    

}

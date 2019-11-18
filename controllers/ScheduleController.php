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
use app\components\Formatt;
use app\models\User;
use app\models\Casts;
use app\models\CastUnderstudy;
use app\models\UserInSchedule;
use app\models\ProfCatInSchedule;
use yii\base\Exception;
use app\models\ProffCategories;
use app\models\ScheduleViewHash;
use app\components\excel\WeekExcel;
use app\components\excel\WeekExcelTwo;
use app\components\word\WeekWord;
use app\models\RoomSetting;
use app\models\UserProfession;

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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['one'],
                        'roles' => ['visible_s_one'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['two', 'pencil'],
                        'roles' => ['visible_s_two'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['three', 'word'],
                        'roles' => ['visible_s_three'],
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
     * Action сводного расписания
     */
    public function actionOne(){
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'add-schedule'){
                if(!Yii::$app->user->can('create_event')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на создание мероприятий']);
                }
                $spectacleEventConfig = Config::getConfig('spectacle_event');
                if(in_array(Yii::$app->request->post('eventType'), $spectacleEventConfig) && +Yii::$app->request->post('withoutEvent') > 0){
                    return json_encode(['result' => 'error', 'response' => 'На спектакль обязательно нужно выбрать мероприятие']);
                }
                $scheduleEvent = new ScheduleEvents();
                $scheduleEvent->event_type_id = Yii::$app->request->post('eventType');
                if(+Yii::$app->request->post('withoutEvent') === 0){
                    $scheduleEvent->event_id = Yii::$app->request->post('event');
                }
                $scheduleEvent->room_id = Yii::$app->request->post('room');
                $scheduleEvent->date = date('Y-m-d', mktime(0, 0, 0, Yii::$app->request->post('date')['month'] + 1, Yii::$app->request->post('date')['day'], Yii::$app->request->post('date')['year']));
                $scheduleEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
                    $scheduleEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                }
                $scheduleEvent->is_modified = Yii::$app->request->post('modifiedEvent');
                if($scheduleEvent->validate() && $scheduleEvent->save()){
                    // Хардкод для eventCategory
//                    if(in_array($scheduleEvent->event_type_id, $spectacleEventConfig) && Yii::$app->request->post('eventCategory') == 1){
//                        $actorsProfCat = Config::getConfig('actors_prof_cat');
//                        $profInSchedule = new ProfCatInSchedule();
//                        $profInSchedule->prof_cat_id = $actorsProfCat[0];
//                        $profInSchedule->schedule_id = $scheduleEvent->id;
//                        $profInSchedule->save();
//                    }
                    $record = ScheduleEvents::find()
                        ->where(['id' => $scheduleEvent->id])
                        ->with('eventType')->with('event')->asArray()->one();
                    return json_encode(['result' => 'ok', 'response' => $record]);
                }else{
                    return json_encode(['result' => 'error', 'response' => 'Ошибка базы данных']);
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
                if(!Yii::$app->user->can('delete_event')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на удаление мероприятий']);
                }
                $findEvent = ScheduleEvents::findOne(Yii::$app->request->post('id'));
                Yii::$app->db->createCommand()->delete('prof_cat_in_schedule', ['schedule_id' => Yii::$app->request->post('id')])->execute();
                if(!$findEvent){
                    return json_encode(['result' => 'error', 'response' => 'Мероприятие не найдено']);
                }
                if($findEvent->delete()){
                    return json_encode(['result' => 'ok']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'edit-event'){
                if(!Yii::$app->user->can('edit_event')){
                    return json_encode(['response' => 'error', 'data' => 'У вас нет прав на редактирование мероприятий']);
                }
                $findEvent = ScheduleEvents::findOne(Yii::$app->request->post('id'));
                $findEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
                    $findEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                }else{
                    $findEvent->time_to = '';
                }
                $checkIntersect = ScheduleComponent::checkIntersectEdit('edit', $findEvent->id, $findEvent->date, $findEvent->time_from, $findEvent->time_to);
                if($checkIntersect){
                    return json_encode(['response' => 'intersect', 'data' => $checkIntersect]);
                }
                $findEvent->is_modified = Yii::$app->request->post('modifiedEvent');
                if($findEvent->validate() && $findEvent->save()){
                    $record = ScheduleEvents::find()
                        ->where(['id' => $findEvent->id])
                        ->with('eventType')->with('event')->asArray()->one();
                return json_encode(['response' => 'ok', 'data' => $record]);
                }
            }
            
            return 0;
        }
        
        $roomConfig = Config::getConfig('schedule_one_rooms');
        $rooms = Room::find()->where(['is_active' => 1, 'id' => $roomConfig])->asArray()->all();
        $eventType = ScheduleComponent::sortFirstLetter(EventType::find()->where(['is_active' => 1])->asArray()->all(), 'name');
        $events = ScheduleComponent::sortFirstLetter(Events::find()->where(['is_active' => 1])->asArray()->all(), 'name');
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
                        ->leftJoin('events', 'schedule_events.event_id = events.id')
                        ->where(['=', 'year(date)', Yii::$app->request->post('year')])
                        ->andWhere(['=', 'month(date)', Yii::$app->request->post('month')])
                        ->andWhere(['event_type_id' => $configEventType])
                        ->andWhere(['events.category_id' => 1])
                        ->with('eventType')->with('event')->asArray()->all();
                return json_encode(ScheduleComponent::transformEventsToTwo($schedule));
            }
            
            if(Yii::$app->request->post('trigger') == 'add-in-cast'){
                if(!\Yii::$app->user->can('add_casts')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на добавление состава']);
                }
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
                if(!\Yii::$app->user->can('add_casts')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на добавление состава']);
                }
                $data = ScheduleComponent::loadCastInSchedule(Yii::$app->request->post('searchMonth'), Yii::$app->request->post('searchYear'), Yii::$app->request->post('event'));
                $data['cast'] = ScheduleComponent::joinUnderstudy($data['cast']);
                if(ScheduleComponent::copyLastCast($data['cast'], Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'))){
                    $addedData = ScheduleComponent::loadCastInSchedule(Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'));
                    $addedData['cast'] = ScheduleComponent::joinUnderstudy($addedData['cast']);
                    // Фильтруем удаленных из системы юзеров
                    foreach ($addedData['cast'] as $key => $value){
                        if(isset($value['understudy'])){
                            foreach ($value['understudy'] as $kU => $vU){
                                if((int)$vU['is_active'] == 0){
                                    unset($addedData['cast'][$key]['understudy'][$kU]);
                                }
                            }
                        }
                        if((int)$value['is_active'] == 0){
                            unset($addedData['cast'][$key]);
                        }
                    }
                    return json_encode([
                        'result' => 'ok',
                        'data' => $addedData
                        ]);
                }else{
                    return json_encode(['result' => 'error']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-actor-in-cast'){
                if(!\Yii::$app->user->can('add_casts')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на удаление из состава']);
                }
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
                        return json_encode(['result' => 'ok']);
                    }
                }catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    return json_encode(['result' => 'error', 'response' => 'Ошибка базы данных']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'add-in-understudy'){
                if(!\Yii::$app->user->can('add_casts')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на добавление состава']);
                }
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
                    $response['response'] = 'Ошибка базы данных';
                    return json_encode($response);
                }
            }
            if(Yii::$app->request->post('trigger') == 'delete-understudy'){
                if(!\Yii::$app->user->can('add_casts')){
                    return json_encode(['result' => 'error', 'response' => 'У вас нет прав на удаление из состава']);
                }
                $deleteUnderstudy = Yii::$app->db->createCommand()->delete('cast_understudy', [
                    'cast_id' => Yii::$app->request->post('cast'),
                    'user_id' => Yii::$app->request->post('user'),
                ])->execute();
                \Yii::$app->db->createCommand()->delete('user_in_schedule', [
                    'user_id' => Yii::$app->request->post('user'),
                    'cast_id' => Yii::$app->request->post('cast')
                ])->execute();
                if($deleteUnderstudy){
                    return json_encode(['result' => 'ok']);
                }
            }
            /**
             * ok - Добавлен на мероприятие
             * deleted - Удален с мероприятия
             * error - есть пересечения
             * permission - нет прав
             */
            if(Yii::$app->request->post('trigger') == 'add-user-schedule'){
                if(!Yii::$app->user->can('add_all_in_schedule') && Yii::$app->user->can('add_cat_in_schedule')){
                    $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $thisUserProfCat = $getProfCat["proff_cat_id"];
                    
                    $getUsersProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->request->post('user')])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $insertUsersProfCat = $getUsersProfCat["proff_cat_id"];
                    
                    if((int)$thisUserProfCat != (int)$insertUsersProfCat){
                        return json_encode(['result' => 'permission', 'response' => 'Вы можете добавлять сотрудников только из своей службы']);
                    }
                }elseif(!Yii::$app->user->can('add_all_in_schedule') && !Yii::$app->user->can('add_cat_in_schedule')){
                    return json_encode(['result' => 'permission', 'response' => 'У вас нет прав на добавление сотрудников в мероприятие']);
                }
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
                if(!Yii::$app->user->can('add_all_in_schedule') && Yii::$app->user->can('add_cat_in_schedule')){
                    $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $thisUserProfCat = $getProfCat["proff_cat_id"];
                    
                    $getUsersProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->request->post('user')])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $insertUsersProfCat = $getUsersProfCat["proff_cat_id"];
                    
                    if((int)$thisUserProfCat != (int)$insertUsersProfCat){
                        return json_encode(['response' => 'permission', 'result' => 'Вы можете добавлять сотрудников только из своей службы']);
                    }
                }elseif(!Yii::$app->user->can('add_all_in_schedule') && !Yii::$app->user->can('add_cat_in_schedule')){
                    return json_encode(['response' => 'permission', 'result' => 'У вас нет прав на добавление сотрудников в мероприятие']);
                }
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
        $profCatLeave = ['8', '5', '16', '11', '14'];
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'add-schedule'){
                if(!Yii::$app->user->can('create_event')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на создание мероприятий']);
                }
                $spectacleEventConfig = Config::getConfig('spectacle_event');
                if(in_array(Yii::$app->request->post('eventType'), $spectacleEventConfig) && +Yii::$app->request->post('withoutEvent') > 0){
                    return json_encode(['response' => 'error', 'result' => 'На спектакль обязательно нужно выбрать мероприятие']);
                }
                $scheduleEvent = new ScheduleEvents();
                $scheduleEvent->event_type_id = Yii::$app->request->post('eventType');
                if(+Yii::$app->request->post('withoutEvent') === 0){
                    $scheduleEvent->event_id = Yii::$app->request->post('event');
                }
                $scheduleEvent->room_id = Yii::$app->request->post('room');
                $scheduleEvent->date = date('Y-m-d', mktime(0, 0, 0, Yii::$app->request->post('date')['month'] + 1, Yii::$app->request->post('date')['day'], Yii::$app->request->post('date')['year']));
                if((int)Yii::$app->request->post('allDay') > 0){
                    $scheduleEvent->time_from = 0;
                    $scheduleEvent->time_to = 1440;
                }else{
                    $scheduleEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                    if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
                        $scheduleEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                    }
                }
                $scheduleEvent->add_info = Yii::$app->request->post('addInfo');
                $scheduleEvent->is_modified = Yii::$app->request->post('modifiedEvent');
                if($scheduleEvent->validate() && $scheduleEvent->save()){
                    $record = ScheduleEvents::find()
                        ->where(['id' => $scheduleEvent->id])
                        ->with('eventType')->with('event')->with('profCat')->asArray()->one();
                    return json_encode(['response' => 'ok', 'result' => $record]);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'load-schedule'){ 
                // В javascript страницы есть хардкод отображения аткров и других служб по prof_cat
                $period = Yii::$app->request->post('period');
                $result = [];
                $result['schedule'] = ScheduleComponent::loadThreeSchedule($period);
                $result['config'] = Config::getAllConfig();
                $result['room_setting'] = RoomSetting::getSetting($period);
                return json_encode($result);
            }
            
            if(Yii::$app->request->post('trigger') == 'load-user-in-schedule'){
                $data = UserInSchedule::find()->select('user_in_schedule.*')
                    ->where(['schedule_event_id' => Yii::$app->request->post('event')])
                    ->with('userWithProf')->asArray()->all();
                foreach ($data as $key => $value){
                    $data[$key]['userSurname'] = $value['userWithProf']['surname'];
                }
                return json_encode(ScheduleComponent::sortFirstLetter($data, 'userSurname'));
            }
            
            if(Yii::$app->request->post('trigger') == 'add-prof-cat-in-event'){
                $profCategories = Yii::$app->request->post('profCat');
                $event = Yii::$app->request->post('event');
                
                if(!Yii::$app->user->can('add_all_prof_cat') && Yii::$app->user->can('add_my_prof_cat')){
                    $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $thisUserProfCat = $getProfCat["proff_cat_id"];
                    if(count($profCategories) > 1 || (int)$profCategories[0] != (int)$thisUserProfCat){
                        return json_encode(['response' => 'error', 'result' => 'Вы можете добавлять только свою службу']);
                    }
                }elseif(!Yii::$app->user->can('add_all_prof_cat') && !Yii::$app->user->can('add_my_prof_cat')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на добавление служб в мероприятие']);
                }
                
                foreach ($profCategories as $value){
                    $profCatInScheduleObj = new ProfCatInSchedule();
                    $profCatInScheduleObj->prof_cat_id = $value;
                    $profCatInScheduleObj->schedule_id = $event;
                    $profCatInScheduleObj->save();
                }
                $getProfCat = ScheduleEvents::find()
                        ->where(['id' => $event])
                        ->with('profCat')->asArray()->one();
                return json_encode([
                    'response' => 'ok',
                    'result' => $getProfCat
                ]);
            }
            
            if(Yii::$app->request->post('trigger') == 'add-user-in-schedule'){
                $users = Yii::$app->request->post('users');
                
                if(!Yii::$app->user->can('add_all_in_schedule') && Yii::$app->user->can('add_cat_in_schedule')){
                    $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $thisUserProfCat = $getProfCat["proff_cat_id"];
                    
                    $getUsersProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => $users])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $insertUsersProfCat = $getUsersProfCat["proff_cat_id"];
                    
                    if((int)$thisUserProfCat != (int)$insertUsersProfCat){
                        return json_encode(['response' => 'error', 'result' => 'Вы можете добавлять сотрудников только из своей службы']);
                    }
                }elseif(!Yii::$app->user->can('add_all_in_schedule') && !Yii::$app->user->can('add_cat_in_schedule')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на добавление сотрудников в мероприятие']);
                }
                
                $configSpectacle = Config::getConfig('spectacle_event');
                $configActor = Config::getConfig('actors_prof_cat');
                $eventSchedule = ScheduleEvents::findOne(Yii::$app->request->post('eventSchedule'));
                if(in_array($eventSchedule['event_type_id'], $configSpectacle) && in_array(Yii::$app->request->post('profCat'), $configActor)){
                    return json_encode(['response' => 'error', 'result' => 'Для заполнения актеров в спектаклях, воспользуйтесь расписанием актеров']);
                }
                
                foreach($users as $value){
                    $checkIntersect = ScheduleComponent::checkIntersect(Yii::$app->request->post('eventSchedule'), $value);
                    if($checkIntersect){
                        return json_encode(['result' => 'intersect', 'data' => $checkIntersect]);
                    }
                }
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try{
                    foreach ($users as $value){
                        Yii::$app->db->createCommand()->insert('user_in_schedule', [
                            'schedule_event_id' => Yii::$app->request->post('eventSchedule'),
                            'user_id' => $value,
                            'cast_id' => 0
                        ])->execute();
                    }
                    $userInSchedule = UserInSchedule::find()->select('user_in_schedule.*')
                        ->where(['schedule_event_id' => Yii::$app->request->post('eventSchedule')])
                        ->with('userWithProf')->asArray()->all();
                    $transaction->commit();
//                    $actorsProfCat = Config::getConfig('actors_prof_cat');
                    return json_encode([
                        'response' => 'ok',
                        'result' => $userInSchedule,
//                        'actors_prof_cat' => $actorsProfCat[0],
                        'event_schedule' => Yii::$app->request->post('eventSchedule')
                    ]);
                }catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    return json_encode(['response' => 'error', 'result' => 'Ошибка при добавлении в базу данных']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-in-schedule'){
                $userInSchedule = UserInSchedule::find()->where(['id' => Yii::$app->request->post('userInSchedule')])->one();
                
                if(!Yii::$app->user->can('add_all_in_schedule') && Yii::$app->user->can('add_cat_in_schedule')){
                    $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $thisUserProfCat = $getProfCat["proff_cat_id"];
                    
                    $getUsersProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => $userInSchedule['user_id']])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $insertUsersProfCat = $getUsersProfCat["proff_cat_id"];
                    
                    if((int)$thisUserProfCat != (int)$insertUsersProfCat){
                        return json_encode(['response' => 'error', 'result' => 'Вы можете удалять сотрудников только из своей службы']);
                    }
                }elseif(!Yii::$app->user->can('add_all_in_schedule') && !Yii::$app->user->can('add_cat_in_schedule')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на удаление сотрудников из мероприятие']);
                }
                $eventId = $userInSchedule['schedule_event_id'];
                $userInSchedule->delete();
                $userInSchedule = UserInSchedule::find()->select('user_in_schedule.*')
                        ->where(['schedule_event_id' => $eventId])
                        ->with('userWithProf')->asArray()->all();
                return json_encode(['response' => 'ok', 'result' => $userInSchedule, 'event_schedule' => $eventId]);
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-prof-cat'){
                if(!Yii::$app->user->can('add_all_prof_cat') && Yii::$app->user->can('add_my_prof_cat')){
                    $getProfCat = UserProfession::find()->select('profession.proff_cat_id')->where(['user_id' => Yii::$app->user->identity->id])
                    ->leftJoin('profession', 'profession.id = user_profession.prof_id')->asArray()->one();
                    $thisUserProfCat = $getProfCat["proff_cat_id"];
                    if((int)Yii::$app->request->post('profCat') != (int)$thisUserProfCat){
                        return json_encode(['response' => 'error', 'result' => 'Вы можете удалять только свою службу']);
                    }
                }elseif(!Yii::$app->user->can('add_all_prof_cat') && !Yii::$app->user->can('add_my_prof_cat')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на добавление служб в мероприятие']);
                }
                $userInSchedule = UserInSchedule::find()->select('user_in_schedule.*')
                        ->where(['schedule_event_id' => Yii::$app->request->post('eventSchedule')])
                        ->with('userWithProf')->asArray()->all();
                $deletedUsers = [];
                foreach ($userInSchedule as $key => $value){
                    if(+$value['userWithProf']['userProfession']['prof']['proff_cat_id'] == +Yii::$app->request->post('profCat')){
                        $deletedUsers[] = $value['userWithProf']['id'];
                    }
                }
                Yii::$app->db->createCommand()->delete('user_in_schedule', [
                    'schedule_event_id' => Yii::$app->request->post('eventSchedule'),
                    'user_id' => $deletedUsers
                ])->execute();
                Yii::$app->db->createCommand()->delete('prof_cat_in_schedule', [
                    'prof_cat_id' => Yii::$app->request->post('profCat'),
                    'schedule_id' => Yii::$app->request->post('eventSchedule')
                ])->execute();
                $schedule = ScheduleEvents::find()
                        ->where(['id' => Yii::$app->request->post('eventSchedule')])
                        ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->one();
                $schedule = ScheduleComponent::removeNeedUsers([$schedule]);
                
                return json_encode([
                    'response' => 'ok',
                    'result' => $schedule[0],
                ]);
            }
            
            if(Yii::$app->request->post('trigger') == 'edit-event'){
                if(!Yii::$app->user->can('edit_event')){
                    return json_encode(['response' => 'error', 'data' => 'У вас нет прав на редактирование мероприятий']);
                }
                $checkEditEvent = ScheduleComponent::checkEditEvent(Yii::$app->request->post('id'), Yii::$app->request->post('eventType'), Yii::$app->request->post('eventId'), Yii::$app->request->post('withoutEvent'));
                if($checkEditEvent['result'] == 'error'){
                    return json_encode(['response' => 'error', 'data' => implode('<br><br>', $checkEditEvent['text'])]);
                }
                
                $findEvent = ScheduleEvents::findOne(Yii::$app->request->post('id'));
                if((int)Yii::$app->request->post('allDay') > 0){
                    $findEvent->time_from = 0;
                    $findEvent->time_to = 1440;
                }else{
                    $findEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                    if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
                        $findEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                    }else{
                        $findEvent->time_to = '';
                    }
                }
//                $findEvent->time_from = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
//                if(\app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'))){
//                    $findEvent->time_to = \app\components\Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
//                }else{
//                    $findEvent->time_to = '';
//                }
                $findEvent->event_type_id = Yii::$app->request->post('eventType');
                if((int)Yii::$app->request->post('withoutEvent') > 0){
                    $findEvent->event_id = '';
                }else{
                    $findEvent->event_id = Yii::$app->request->post('eventId');
                }
                $findEvent->add_info = Yii::$app->request->post('addInfo');
                $findEvent->is_modified = Yii::$app->request->post('modifiedEvent');
                $findEvent->is_all = Yii::$app->request->post('isAll');
                $checkIntersect = ScheduleComponent::checkIntersectEdit('edit', $findEvent->id, $findEvent->date, $findEvent->time_from, $findEvent->time_to);
                if($checkIntersect){
                    return json_encode(['response' => 'intersect', 'data' => $checkIntersect]);
                }
                if($findEvent->validate() && $findEvent->save()){
                    $record = ScheduleEvents::find()
                        ->where(['id' => $findEvent->id])
                        ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->one();
                    $record = ScheduleComponent::removeNeedUsers([$record]);
                return json_encode(['response' => 'ok', 'data' => $record[0]]);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'search-cast'){
                $searchCast = ScheduleComponent::searchLastCast(Yii::$app->request->post('month'), Yii::$app->request->post('year'), Yii::$app->request->post('event'), 18);
                if($searchCast){
                    $data = ScheduleComponent::loadCastInSchedule($searchCast['month'], $searchCast['year'], Yii::$app->request->post('event'));
                    $data['cast'] = ScheduleComponent::joinUnderstudy($data['cast']);
                    $castIds = [];
                    foreach ($data['cast'] as $key => $value){
                        if(!in_array($value['id'], $castIds)) $castIds[] = $value['id'];
                        if(isset($value['understudy'])){
                            foreach ($value['understudy'] as $keyUnd => $valUnd){
                                if(!in_array($valUnd['id'], $castIds)) $castIds[] = $valUnd['id'];
                            }
                        }
                    }
                    return json_encode(['response' => 'ok', 'data' => $castIds]);
                }else{
                    return json_encode(['response' => 'error', 'result' => 'Состав не найден']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'copy-event'){
                if(!Yii::$app->user->can('copy_event')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на копирование мероприятий']);
                }
                $getEvent = ScheduleEvents::find()->where(['id' => Yii::$app->request->post('id')])->asArray()->one();
                $timeFrom = Formatt::timeToMinute(Yii::$app->request->post('timeFrom'));
                $timeTo = Formatt::timeToMinute(Yii::$app->request->post('timeTo'));
                $date = date('Y-m-d', mktime(0, 0, 0, Yii::$app->request->post('date')['month'] + 1, Yii::$app->request->post('date')['day'], Yii::$app->request->post('date')['year']));
                $checkCopyEvent = ScheduleComponent::checkCopyEvent($getEvent['id'], Yii::$app->request->post('moveUsers'));
                if($checkCopyEvent['result'] == 'error'){
                    return json_encode(['response' => 'error', 'result' => implode('<br><br>', $checkCopyEvent['text'])]);
                }
                if((int)Yii::$app->request->post('withoutIntersect') == 0){
                    $checkIntersectEvent = ScheduleComponent::checkIntersectEvent(Formatt::timeToMinute(Yii::$app->request->post('timeFrom')), Formatt::timeToMinute(Yii::$app->request->post('timeTo')), $date, Yii::$app->request->post('room'));
                    if(!$checkIntersectEvent){
                        return json_encode(['response' => 'error', 'result' => 'Изменяемое мероприятие пересекается с другими в этот день']);
                    }
                }
                if(+Yii::$app->request->post('moveUsers') > 0){
                    $checkIntersect = ScheduleComponent::checkIntersectEdit('copy', $getEvent['id'], date('Y-m-d', mktime(0, 0, 0, Yii::$app->request->post('date')['month'] + 1, Yii::$app->request->post('date')['day'], Yii::$app->request->post('date')['year'])), $timeFrom, $timeTo);
                    if($checkIntersect){
                        return json_encode(['response' => 'intersect', 'data' => $checkIntersect]);
                    }
                }
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try {
                    $newScheduleEvent = new ScheduleEvents();
                    $newScheduleEvent->event_type_id = Yii::$app->request->post('eventType');
                    if((int)Yii::$app->request->post('withoutEvent') > 0){
                        $newScheduleEvent->event_id = '';
                    }else{
                        $newScheduleEvent->event_id = Yii::$app->request->post('eventId');
                    }
                    $newScheduleEvent->room_id = Yii::$app->request->post('room');
                    $newScheduleEvent->date = $date;
                    $newScheduleEvent->time_from = $timeFrom;
                    $newScheduleEvent->time_to = $timeTo?$timeTo:null;
                    $newScheduleEvent->is_modified = Yii::$app->request->post('modifiedEvent');
                    $newScheduleEvent->add_info = Yii::$app->request->post('addInfo');
                    $newScheduleEvent->is_all = Yii::$app->request->post('isAll');
                    if($newScheduleEvent->save()){
                        if((int)Yii::$app->request->post('moveUsers') > 0){
                            $getUserInSchedule = UserInSchedule::find()->where([
                                'schedule_event_id' => Yii::$app->request->post('id')
                            ])->asArray()->all();
                            if($getUserInSchedule){
                                foreach ($getUserInSchedule as $key => $value){
                                    $db->createCommand()->insert('user_in_schedule', [
                                        'schedule_event_id' => $newScheduleEvent->id,
                                        'user_id' => $value['user_id'],
                                        'cast_id' => $value['cast_id']
                                    ])->execute();
                                }
                            }
                            $getProfCat = ProfCatInSchedule::find()->where(['schedule_id' => Yii::$app->request->post('id')])->asArray()->all();
                            if($getProfCat){
                                foreach ($getProfCat as $key => $value){
                                    $db->createCommand()->insert('prof_cat_in_schedule', [
                                        'prof_cat_id' => $value['prof_cat_id'],
                                        'schedule_id' => $newScheduleEvent->id
                                    ])->execute();
                                }
                            }
                        }
                    }
                    $transaction->commit();
                    $record = ScheduleEvents::find()
                        ->where(['id' => $newScheduleEvent->id])
                        ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->one();
                    $record = ScheduleComponent::removeNeedUsers([$record]);
                    
                    $response['result'] = 'ok';
                    return json_encode(['response' => 'ok', 'result' => $record[0]]);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    //throw $e;
                    return json_encode(['response' => 'error', 'result' => 'Что-то пошло не так, перезагрузите страницу и попробуйте снова']);
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'delete-event'){
                if(!Yii::$app->user->can('delete_event')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на удаление мероприятий']);
                }
                $getEvent = ScheduleEvents::find()->where(['id' => Yii::$app->request->post('id')])->one();
                Yii::$app->db->createCommand()->delete('prof_cat_in_schedule', ['schedule_id' => Yii::$app->request->post('id')])->execute();
                if($getEvent->delete()){
                    return json_encode(['response' => 'ok']);
                }else{
                    return json_encode(['response' => 'error', 'result' => 'Ошибка базы данных. Перезагрузите страницу и попробуйте снова']);
                }
            }

            if(Yii::$app->request->post('trigger') == 'generate-link'){
                $period = Yii::$app->request->post('period');
                $from = $period[0]['year'] ."-" .$period[0]['month'] ."-" .$period[0]['day'];
                $to = $period[1]['year'] ."-" .$period[1]['month'] ."-" .$period[1]['day'];
                $scheduleHash = ScheduleViewHash::returnInfoByDate($from, $to);
                return json_encode(['response' => 'ok', 'result' => $scheduleHash]);
            }
            
            if(Yii::$app->request->post('trigger') == 'schedule-hash-show'){
                $period = Yii::$app->request->post('period');
                $from = $period[0]['year'] ."-" .$period[0]['month'] ."-" .$period[0]['day'];
                $to = $period[1]['year'] ."-" .$period[1]['month'] ."-" .$period[1]['day'];
                Yii::$app->db->createCommand()
                        ->update('schedule_view_hash', ['show' => Yii::$app->request->post('result')], ['date_from' => $from, 'date_to' => $to])->execute();
                return json_encode(['response' => 'ok']);
            }
            
            if(Yii::$app->request->post('trigger') == 'set-room-config'){
                if(!Yii::$app->user->can('room_config')){
                    return json_encode(['response' => 'error', 'result' => 'У вас нет прав на настройку отображения залов']);
                }
                $checkSetting = RoomSetting::checkSetting(Yii::$app->request->post('period'), Yii::$app->request->post('roomIds'));
                if(!$checkSetting){
                    return json_encode(['response' => 'error', 'result' => 'Кажется вы пытаетесь убрать залы в которых стоят мероприятия. Сначала удалите их']);
                }
                RoomSetting::setSetting(Yii::$app->request->post('period'), Yii::$app->request->post('roomIds'));
                $setting = RoomSetting::getSetting(Yii::$app->request->post('period'));
                return json_encode(['response' => 'ok', 'result' => $setting]);
            }
            
            
            return json_encode(['response' => 'error', 'result' => 'Ajax запрос не вошел ни в одно условие. Сообщите разработчику о данной ошибке']);
        }
        
        
        $rooms = Room::find()->where(['is_active' => 1])->asArray()->all();
        $profCategories = ScheduleComponent::sortFirstLetter(ProffCategories::find()->asArray()->all(), 'name');
        $eventType = ScheduleComponent::sortFirstLetter(EventType::find()->where(['is_active' => 1])->asArray()->all(), 'name');
        $events = ScheduleComponent::sortFirstLetter(Events::find()->where(['is_active' => 1])->asArray()->all(), 'name');
        $eventCategories = EventCategories::find()->asArray()->all();
        $users = User::find()->select('user.id, user.name, user.surname')
                ->with('userProfessionJoinProf')
                ->where(['user.is_active' => 1])
                ->asArray()->all();
        $users = ScheduleComponent::sortFirstLetter($users, 'surname', true);
        
        return $this->render('three', [
            'rooms' => $rooms,
            'profCategories' => $profCategories,
            'users' => $users,
            'eventType' => $eventType,
            'events' => $events,
            'eventCategories' => $eventCategories,
        ]);
    }
    
    
    /**
     * Action выгрузки в excel
     */
//    public function actionExcelOne(){
//        
//        WeekExcel::excelWeekSchedule(Yii::$app->request->get('from'), Yii::$app->request->get('to'));
//        
//    }
    
    /**
     * Action выгрузки в excel
     */
//    public function actionExcelTwo(){
//        
//        WeekExcelTwo::excelWeekSchedule(Yii::$app->request->get('from'), Yii::$app->request->get('to'));
//        
//    }
    
    /**
     * Action выгрузки в word
     */
    public function actionWord(){
        WeekWord::wordWeekSchedule(Yii::$app->request->get('from'), Yii::$app->request->get('to'));
    }
    
    public function actionPencil(){
        $getCatId = Config::getConfig('actors_prof_cat');
        
        $pencilExcel = new \app\components\excel\PencilsExcel(Yii::$app->request->get('month'), Yii::$app->request->get('year'), $getCatId);
        $pencilExcel->run();
//        var_dump(array('Hello'));
    }
    

}

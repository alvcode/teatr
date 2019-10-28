<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Room;
use app\models\EventType;
use app\models\Events;
use app\models\EventCategories;
use app\models\ScheduleEvents;
use app\components\Formatt;
use app\components\ScheduleComponent;
use app\models\ScheduleViewHash;

class PanelController extends AccessController
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
                        'roles' => ['visible_panel'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['room-event'],
                        'roles' => ['visible_room_event'],
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
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'load-timesheet-stat'){
                $result = ScheduleComponent::panelTimesheetStatistic(Yii::$app->request->post('from'), Yii::$app->request->post('to'), Yii::$app->user->identity->id);
                return json_encode(['result' => 'ok', 'response' => $result]);
            }
        }
        $nDate = date('Y-m-d');
        $nearSchedule = ScheduleEvents::find()
            ->leftJoin('user_in_schedule', 'user_in_schedule.schedule_event_id = schedule_events.id')
            ->where(['between', 'date', $nDate, date('Y-m-d', strtotime($nDate ." + 10 day"))])
            ->andWhere(['user_in_schedule.user_id' => Yii::$app->user->identity->id])
            ->with('eventType')->with('event')->orderBy('date ASC, time_from ASC')
            ->asArray()->all();
        
        foreach($nearSchedule as $k => $v){
            $nearSchedule[$k]['time_from'] = Formatt::minuteToTime($v['time_from']);
            if($v['time_to']){
                $nearSchedule[$k]['time_to'] = Formatt::minuteToTime($v['time_to']);
            }
            $nearSchedule[$k]['date'] = Formatt::panelHumanDate($v['date']);
        }

        $getScheduleLinks = ScheduleViewHash::returnLinksByWeekCount([0,1]);
        
        return $this->render('index', [
            'nearSchedule' => $nearSchedule,
            'scheduleLinks' => $getScheduleLinks
        ]);
    }
    
    
    public function actionRoomEvent(){
        
        $roomModel = new Room();
        $eventTypeModel = new EventType();
        $eventsModel = new Events();
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'rename-room'){
                $roomModel = Room::findOne(Yii::$app->request->post('roomId'));
                $roomModel->name = Yii::$app->request->post('roomName');
                if($roomModel->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'remove-room'){
                $roomModel = Room::findOne(Yii::$app->request->post('roomId'));
                $roomModel->is_active = 0;
                if($roomModel->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'remove-event-type'){
                $eventTypeModel = EventType::findOne(Yii::$app->request->post('eventTypeId'));
                $eventTypeModel->is_active = 0;
                if($eventTypeModel->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'remove-event'){
                $eventModel = Events::findOne(Yii::$app->request->post('eventId'));
                $eventModel->is_active = 0;
                if($eventModel->save()){
                    return 1;
                }else{
                    return 0;
                }
            }
            
            if(Yii::$app->request->post('trigger') == 'edit-event-name'){
                Yii::$app->db->createCommand()
                        ->update('events', [
                                    'name' => Yii::$app->request->post('firstName'), 
                                    'other_name' => Yii::$app->request->post('otherName')
                                ], ['id' => Yii::$app->request->post('eventId')
                            ])->execute();
                return 1;
            }
        }
        
        if($roomModel->load(Yii::$app->request->post())){
            $roomModel->is_active = 1;
            if($roomModel->save()){
                Yii::$app->session->setFlash('success', "Зал успешно добавлен");
            }else{
                Yii::$app->session->setFlash('error', "Что-то пошло не так, обратитесь к разработчику");
            }
            return $this->redirect('/panel/room-event/');
        }
        if($eventTypeModel->load(Yii::$app->request->post())){
            $eventTypeModel->is_active = 1;
            if($eventTypeModel->save()){
                Yii::$app->session->setFlash('success', "Новый тип мероприятия успешно добавлен");
            }else{
                Yii::$app->session->setFlash('error', "Что-то пошло не так, обратитесь к разработчику");
            }
            return $this->redirect('/panel/room-event/');
        }
        if($eventsModel->load(Yii::$app->request->post())){
            $eventsModel->is_active = 1;
            if($eventsModel->save()){
                Yii::$app->session->setFlash('success', "Спектакль успешно добавлен");
            }else{
                Yii::$app->session->setFlash('error', "Что-то пошло не так, обратитесь к разработчику");
            }
            return $this->redirect('/panel/room-event/');
        }
        
        $rooms = Room::find()->where(['is_active' => '1'])->asArray()->all();
        $eventType = ScheduleComponent::sortFirstLetter(EventType::find()->where(['is_active' => 1])->asArray()->all(), 'name');
        $events = EventCategories::find()->with('events')->asArray()->all();
        foreach ($events as $key => $value){
            if($value['events']){
                $events[$key]['events'] = ScheduleComponent::sortFirstLetter($value['events'], 'name');
            }
        }
        $eventCategories = EventCategories::find()->asArray()->all();
        
        return $this->render('room_event', [
            'roomModel' => $roomModel,
            'eventTypeModel' => $eventTypeModel,
            'eventsModel' => $eventsModel,
            'rooms' => $rooms,
            'eventType' => $eventType,
            'events' => $events,
            'eventCategories' => $eventCategories,
        ]);
    }

}

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
        
        return $this->render('index');
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
            
            if(Yii::$app->request->post('trigger') == 'edit-timesheet'){
                $eventTypeModel = EventType::findOne(Yii::$app->request->post('eventTypeId'));
                $eventTypeModel->timesheet_hour = Yii::$app->request->post('timesheetHour');
                $eventTypeModel->timesheet_count = Yii::$app->request->post('timesheetCount');
                if($eventTypeModel->save()){
                    return 1;
                }else{
                    return 0;
                }
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
        $eventType = EventType::find()->where(['is_active' => '1'])->asArray()->all();
        $events = Events::find()->where(['is_active' => '1'])->asArray()->all();
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

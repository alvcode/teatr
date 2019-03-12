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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
        }
        
        if($roomModel->load(Yii::$app->request->post())){
            if($roomModel->save()){
                Yii::$app->session->setFlash('success', "Зал успешно добавлен");
            }else{
                Yii::$app->session->setFlash('error', "Что-то пошло не так, обратитесь к разработчику");
            }
            return $this->redirect('/panel/room-event/');
        }
        
        
        $rooms = Room::find()->asArray()->all();
        
        return $this->render('room_event', [
            'roomModel' => $roomModel,
            'rooms' => $rooms,
        ]);
    }

}

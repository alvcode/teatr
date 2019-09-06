<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ScheduleViewHash;
use app\models\ScheduleEvents;
use app\components\ScheduleComponent;
use yii\base\Exception;
use app\models\Room;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'logout' => ['post'],
//                ],
//            ],
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
    public function actionIndex()
    {
        if (!Yii::$app->getUser()->isGuest){
            return $this->redirect('/panel/index/');
        }
        
        $model = new LoginForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('/panel');
        }
        
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    
    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionRegister()
    {
       $model = new \app\models\User();
       $model->email = 'alvcode@ya.ru';
       $model->username = 'Александр';
       $model->password = 'blackjack163';
       $model->save();
       

        return $this->render('index');
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect('/site/index');
    }

    /**
     * Week schedule action.
     *
     * @return Response
     */
    public function actionWeekSchedule($from, $to, $hash)
    {
        $this->layout = 'schedule';
        $profCatLeave = ['8', '5', '16', '11', '14'];

        $searchHash = ScheduleViewHash::find()->where(['date_from' => $from, 'date_to' => $to])->asArray()->one();
        if($searchHash['hash'] == $hash){
        $schedule = ScheduleEvents::find()
                ->where(['between', 'date', $from, $to])
                ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->all();
        
        $dateFrom = date('d.m.Y', strtotime($from));
        $dateTo = date('d.m.Y', strtotime($to));
        
        $schedule = ScheduleComponent::removeNeedUsers($schedule);
        
        $activesRoom = [];
        foreach ($schedule as $key => $value){
            if(!in_array($value['room_id'], $activesRoom)){
                $activesRoom[] = $value['room_id'];
            }
        }
        $dates = [];
        for ($i = 0; $i < 7; $i++){
            $cc = count($dates);
            $dates[$cc]['day'] = date('d', strtotime($from ." + " .$i ." day"));
            $dates[$cc]['month'] = date('m', strtotime($from ." + " .$i ." day"));
            $dates[$cc]['year'] = date('Y', strtotime($from ." + " .$i ." day"));
        }
        $scheduleSort = [];
        foreach ($schedule as $key => $value){
            foreach ($dates as $keyD => $valueD){
                if(strtotime($value['date']) === mktime(0, 0, 0,$valueD['month'], $valueD['day'], $valueD['year'])){
                    $scheduleSort[strtotime($value['date'])][$value['room_id']][intval($value['time_from'])] = $value;
                }
            }
        }
        $rooms = Room::find()->where(['is_active' => 1, 'id' => $activesRoom])->asArray()->all();

        echo "<pre>";
        var_dump($scheduleSort); exit();
        }else{
            throw new Exception('Неверная ссылка');
        }

        return $this->render('week');
    }

}

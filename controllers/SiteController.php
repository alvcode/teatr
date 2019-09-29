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
use yii\web\NotFoundHttpException;
use app\models\Room;
use app\models\Config;

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
    public function actionWeekSchedule()
    {
        $this->layout = 'schedule';
        
        if(Yii::$app->request->isAjax){
            if(Yii::$app->request->post('trigger') == 'load-schedule'){ 
                // В javascript страницы есть хардкод отображения аткров и других служб по prof_cat
                $searchHash = ScheduleViewHash::find()->where(['date_from' => Yii::$app->request->post('from'), 'date_to' => Yii::$app->request->post('to')])->asArray()->one();
                if($searchHash['hash'] == Yii::$app->request->post('hash')){
                    $period = Yii::$app->request->post('period');
                    $result = [];
                    $result['schedule'] = ScheduleComponent::loadThreeSchedule($period);
                    $result['config'] = Config::getAllConfig();
                    return json_encode(['result' => 'ok', 'response' => $result]);
                }else{
                    return json_encode(['result' => 'error', 'response' => 'Хэш не прошел проверку на подлинность. Обратитесь к разработчику, если считаете это ошибкой программы']);
                }
            }
        }
        
        $searchHash = ScheduleViewHash::find()->where(['date_from' => Yii::$app->request->get('from'), 'date_to' => Yii::$app->request->get('to')])->asArray()->one();
        if($searchHash['hash'] == Yii::$app->request->get('hash')){
            $rooms = Room::find()->where(['is_active' => 1])->asArray()->all();
        }else{
            throw new NotFoundHttpException('Ссылка не прошла проверку на подлинность. Убедитесь в том, что скопировали ссылку полностью или обратитесь в администрацию');
        }
        
        return $this->render('week', [
            'rooms' => $rooms,
            'from' => Yii::$app->request->get('from'),
            'to' => Yii::$app->request->get('to'),
            'hash' => Yii::$app->request->get('hash')
        ]);
        
    }

}

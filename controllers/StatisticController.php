<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\ProffCategories;
use app\components\excel\TimesheetExcel;
use yii\base\Exception;

class StatisticController extends AccessController
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
        
        $categoryAll = ProffCategories::find()->with('professions')->asArray()->all();
        $professions = [];
        foreach($categoryAll as $key => $value){
            if($value['professions']){
                foreach($value['professions'] as $keyP => $valueP){
                    $countArr = count($professions);
                    $professions[$countArr]['id'] = $valueP['id'];
                    $professions[$countArr]['name'] = $valueP['name'] ." (".$value['name'] .")";
                }
            }
        }
        
        return $this->render('index', [
            'professions' => $professions,
            'categories' => $categoryAll
        ]);
    }
    
    
    public function actionTimesheet(){
        if(Yii::$app->request->get('prof')){
            // Режим профессии
            $mode = 'prof';
            $id = Yii::$app->request->get('prof');
        }elseif(Yii::$app->request->get('profCat')){
            // Режим службы
            $mode = 'profCat';
            $id = Yii::$app->request->get('profCat');
        }
        $timesheet = new TimesheetExcel(Yii::$app->request->get('from'), Yii::$app->request->get('to'), $id, $mode);
        if((int)Yii::$app->request->get('time_error') == 0){
            $errors = $timesheet->checkTimeError();
            if($errors){
                return $this->render('timesheet_error', [
                    't_to_errors' => $errors,
                ]);
            }
        }
        $timesheet->run();
    }
    

}

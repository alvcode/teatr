<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\ProffCategories;
use app\components\excel\TimesheetExcel;

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
        $categories = [];
        foreach($categoryAll as $key => $value){
            if($value['professions']){
                foreach($value['professions'] as $keyP => $valueP){
                    $countArr = count($categories);
                    $categories[$countArr]['id'] = $valueP['id'];
                    $categories[$countArr]['name'] = $valueP['name'] ." (".$value['name'] .")";
                }
            }
        }
        
        return $this->render('index', [
            'categories' => $categories,
        ]);
    }
    
    
    public function actionTimesheet(){
        TimesheetExcel::excelTimesheet(Yii::$app->request->get('from'), Yii::$app->request->get('to'), Yii::$app->request->get('prof'));
    }
    

}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "schedule_view_hash".
 *
 * @property int $id
 * @property string $date_from
 * @property string $date_to
 * @property string $hash
 * @property int $show
 */
class ScheduleViewHash extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'schedule_view_hash';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'hash', 'show'], 'required'],
            [['date_from', 'date_to'], 'safe'],
            [['show'], 'integer'],
            [['hash'], 'string', 'max' => 45],
        ];
    }
    
    /**
     * Возвращает ссылки на просмотр расписания
     * 
     * @param array $weekLength - номера недель. 0 - текущая, 1 - следующая и т.д...
     * @param bool $showFlag - если true, то выбирает ссылки только с флагом show = 1, кроме текущей недели (текущую неделю отображаем всегда)
     * @return array
     */
    public static function returnLinksByWeekCount($weekLength, $showFlag = true){
        $result = [];
        $nowWeekNumber = date('N');
        $mondayMinus = $nowWeekNumber - 1;
        foreach ($weekLength as $v){
            $counter = count($result);
            $dateFrom = date('Y-m-d', strtotime(date('Y-m-d', strtotime('-' .$mondayMinus .' day')) .' +' .$v .' week'));
            $dateTo = date('Y-m-d', strtotime($dateFrom ." +6 day"));
            
            $getRow = self::find()->where(['date_from' => $dateFrom, 'date_to' => $dateTo])->asArray()->one();
            
            if($getRow){
                // Если текущая неделя - отображаем
                if($v == 0){
                    $result[$v]['date_from'] = date('d.m.Y', strtotime($getRow['date_from']));
                    $result[$v]['date_to'] = date('d.m.Y', strtotime($getRow['date_to']));
                    $result[$v]['link'] = self::generateLinkStr($getRow['date_from'], $getRow['date_to'], $getRow['hash']);
                // Если не текущая и либо show = 1 либо не нужна проверка на show
                }elseif($v != 0 && (($showFlag && (int)$getRow['show'] === 1) || !$showFlag)){
                    $result[$v]['date_from'] = date('d.m.Y', strtotime($getRow['date_from']));
                    $result[$v]['date_to'] = date('d.m.Y', strtotime($getRow['date_to']));
                    $result[$v]['link'] = self::generateLinkStr($getRow['date_from'], $getRow['date_to'], $getRow['hash']);
                }
            }else{
                // Создаем новую запись если не нужна проверка, либо нет генерации на текущую неделю
                if(!$showFlag || $v == 0){
                    $newHash = new self();
                    $newHash->date_from = $dateFrom;
                    $newHash->date_to = $dateTo;
                    $newHash->hash = \Yii::$app->getSecurity()->generateRandomString();
                    $newHash->show = 0;
                    if($newHash->save()){
                        $result[$v]['date_from'] = date('d.m.Y', strtotime($newHash->date_from));
                        $result[$v]['date_to'] = date('d.m.Y', strtotime($newHash->date_to));
                        $result[$v]['link'] = self::generateLinkStr($newHash->date_from, $newHash->date_to, $newHash->hash);
                    }
                }
                
            }
        }
        return $result;
    }
    
    /**
     * Возвращает данные для переданных дат. Если записи нет, то генерирует новую
     * 
     * @param date $dateFrom - Y-m-d
     * @param date $dateTo - Y-m-d
     * @return array
     */
    public static function returnInfoByDate($dateFrom, $dateTo){
        $result = [];
        $search = self::find()->where(['date_from' => $dateFrom, 'date_to' => $dateTo])->asArray()->one();
        Yii::warning($search);
        if($search){
            $result['date_from'] = $search['date_from'];
            $result['date_to'] = $search['date_to'];
            $result['link'] = self::generateLinkStr($search['date_from'], $search['date_to'], $search['hash']);
            $result['show'] = $search['show'];
        }else{
            $newHash = new self();
            $newHash->date_from = $dateFrom;
            $newHash->date_to = $dateTo;
            $newHash->hash = \Yii::$app->getSecurity()->generateRandomString();
            $newHash->show = 0;
            if($newHash->save()){
                $result['date_from'] = date('d.m.Y', strtotime($newHash->date_from));
                $result['date_to'] = date('d.m.Y', strtotime($newHash->date_to));
                $result['link'] = self::generateLinkStr($newHash->date_from, $newHash->date_to, $newHash->hash);
                $result['show'] = $newHash->show;
            }
        }
        return $result;
    }
    
    
    
    /**
     * Генерирует ссылку для просмотра расписания
     * 
     * @param date $dateFrom - Y-m-d
     * @param date $dateTo - Y-m-d
     * @param string $hash
     * @return string
     */
    public static function generateLinkStr($dateFrom, $dateTo, $hash){
        return $_SERVER["REQUEST_SCHEME"] .'://' .$_SERVER["HTTP_HOST"] .'/site/week-schedule?from=' .$dateFrom .'&to=' .$dateTo .'&hash=' .$hash;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_from' => 'Date From',
            'date_to' => 'Date To',
            'hash' => 'Hash',
        ];
    }
}

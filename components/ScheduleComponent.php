<?php

namespace app\components;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;

/**
 *
 * 
 */

class ScheduleComponent extends Model{
    
    /**
     * Принимает на вход список мероприятий и преобразует в удобном для обработки виде для
     * расписания актеров. Первые ключи это даты, вторые ключи это id-мероприятий
     * @param array $events
     * @return array
     */
    public static function transformEventsToTwo($events){
        $result = [];
        
        foreach ($events as $key => $value){
            $date = date('j', strtotime($value['date']));
            $eventId = $value['event']['id'];
            if(!isset($result['schedule'][$date])){
                $result['schedule'][$date] = [];
            }
            if(!isset($result['schedule'][$date][$eventId])){
                $result['schedule'][$date][$eventId] = [];
            }
            if(!isset($result['allEvents'][$eventId])){
                $result['allEvents'][$eventId]['id'] = $eventId;
                $result['allEvents'][$eventId]['name'] = $value['event']['name'];
            }
            $result['schedule'][$date][$eventId][] = $value;
            
        }
        
        return $result;
    }
    
    /**
     * Принимает массив с полем cast_id, осуществляет поиск в understudy и присоединяем
     * к соответствующим юзерам
     * @param array $users
     * @return array
     */
    public static function joinUnderstudy($users){
        $findUnderstudy = CastUnderstudy::find()->select('cast_understudy.cast_id, user.id, user.name, user.surname')->where(['cast_id' => \yii\helpers\ArrayHelper::getColumn($users, 'cast_id')])
                ->leftJoin('user', 'cast_understudy.user_id = user.id')->asArray()->all();
        foreach ($users as $keyU => $valueU){
            foreach($findUnderstudy as $key => $value){
                if($valueU['cast_id'] == $value['cast_id']){
                    $users[$keyU]['understudy'][] = $value;
                }
            }
        }
        return $users;
    }
   
}

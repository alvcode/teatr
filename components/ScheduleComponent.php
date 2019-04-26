<?php

namespace app\components;

use Yii;
use yii\base\Model;

/**
 *
 * 
 */

class ScheduleComponent extends Model{
    
    /**
     * Принимает на вход список мероприятий и преобразует в удобном для обработки виде для
     * расписания актеров
     * @param array $events
     * @return array
     */
    public static function transformEventsToTwo($events){
        $result = [];
        
        foreach ($events as $key => $value){
            $date = date('j', strtotime($value['date']));
            $eventId = $value['event']['id'];
            if(!isset($result[$date])){
                $result[$date] = [];
            }
            if(!isset($result[$date][$eventId])){
                $result[$date][$eventId] = [];
            }
            $result[$date][$eventId][] = $value;
            
        }
        
        return $result;
    }
   
}

<?php

namespace app\components;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;
use app\models\ScheduleEvents;

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
        $findUnderstudy = CastUnderstudy::find()->select('*')
                ->where(['cast_id' => \yii\helpers\ArrayHelper::getColumn($users, 'cast_id')])->with('user')->asArray()->all();
        
        foreach ($users as $keyU => $valueU){
            foreach($findUnderstudy as $key => $value){
                if($valueU['cast_id'] == $value['cast_id']){
                    $users[$keyU]['understudy'][] = $value['user'];
                }
            }
        }
        return $users;
    }
    
    public static function sortFirstLetter($arr, $param){
        $result = [];
        $alphabet = ['а', 'А', 'б', 'Б', 'в', 'В', 'г', 'Г', 'д', 'Д', 'е', 'Е', 'ё', 'Ё', 'ж', 'Ж', 'з', 'З', 'и', 'И', 'й', 'Й', 'к', 'К', 'л', 'Л', 'м', 'М', 'н', 'Н', 'о', 'О', 
            'п', 'П', 'р', 'Р', 'с', 'С', 'т', 'Т', 'у', 'У', 'ф', 'Ф', 'х', 'Х', 'ц', 'Ц', 'ч', 'Ч', 'ш', 'Ш', 'щ', 'Щ', 'ъ', 'Ъ', 'ы', 'Ы', 'ь', 'Ь', 'э', 'Э', 'ю', 'Ю', 'я', 'Я'];
        foreach($alphabet as $keyA => $valueA){
            foreach($arr as $keyArr => $valueArr){
                $first_letter = mb_substr($valueArr[$param],0,1);
                if($first_letter == $valueA){
                    $result[$valueA][] = $valueArr;
                }
            }
        }
        return $result;
    }
    
    /**
     * Проверка на пересечение времени. Передаем ID мероприятия из расписания на которое хотим поставить сотрудника
     * Функция берет все мероприятия где стоит сотрудник на сегодня и проверяет, чтобы время не пересекалось.
     * false - не пересекается, array - данные с пересечениями
     * 
     * @param integer $scheduleId
     * @param integer $userId
     * @return array
     */
    public static function checkIntersect($scheduleId, $userId){
        $result = [];
        $findSchedule = ScheduleEvents::findOne($scheduleId);
        $getAllEvents = ScheduleEvents::find()->select('events.name, schedule_events.time_from, schedule_events.time_to')
                ->leftJoin('user_in_schedule', 'schedule_events.id = user_in_schedule.schedule_event_id')
                ->leftJoin('events', 'events.id = schedule_events.event_id')
                ->where(['date(schedule_events.date)' => $findSchedule->date, 'user_in_schedule.user_id' => $userId])
                ->asArray()->all();
        if($getAllEvents){
            if($findSchedule->time_to){
                foreach ($getAllEvents as $key => $value){
                    if($value['time_to']){
                        if((($value['time_from'] < $findSchedule->time_to && $value['time_from'] >= $findSchedule->time_from))
                                || ($value['time_to'] <= $findSchedule->time_to && $value['time_to'] > $findSchedule->time_from) 
                                || ($value['time_from'] <= $findSchedule->time_from && $value-['time_to'] >= $findSchedule->time_to)){
                                    $result[] = $value;
                                }
                    }else{
                        if(+$findSchedule->time_from == $value['time_from']){
                            $result[] = $value;
                        }
                    }
                }
            }else{
                foreach ($getAllEvents as $key => $value){
                    if(+$findSchedule->time_from == $value['time_from']){
                        $result[] = $value;
                    }
//                    return [];
                }
            }
        }
        if($result){
            return $result;
        }else{
            return false;
        }
    }
   
}

<?php

namespace app\components;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;
use app\models\ScheduleEvents;
use app\models\Casts;
use app\models\UserInSchedule;

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
                                || ($value['time_from'] <= $findSchedule->time_from && $value['time_to'] >= $findSchedule->time_to)){
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
                }
            }
        }
        if($result){
            return $result;
        }else{
            return false;
        }
    }
    
    public static function checkFullSchedule($month, $year){
        $result = [];
        $schedule = ScheduleEvents::find()->select('*')
                ->where(['=', 'year(date)', $year])
                ->andWhere(['=', 'month(date)', $month])
                ->asArray()->all();
        
        $casts = Casts::find()->where(['year' => $year, 'month' => $month])->with('understudy')->asArray()->all();
        $data = [];
        foreach ($schedule as $key => $value){
            $data[$key] = $value;
            $data[$key]['casts'] = [];
            foreach ($casts as $keyC => $valueC){
                if($value['event_id'] == $valueC['event_id']){
                    $data[$key]['casts'][] = $valueC;
                }
            }
        }
        $userInSchedule = UserInSchedule::find()->where(['schedule_event_id' => \yii\helpers\ArrayHelper::getColumn($schedule, 'id')])
                ->asArray()->all();
        foreach ($userInSchedule as $key => $value){
            foreach ($data as $kD => $vD){
                if($vD['casts']){
                    if($vD['id'] == $value['schedule_event_id']){
                        foreach ($vD['casts'] as $kC => $vC){
                            if($value['user_id'] == $vC['user_id'] && $value['cast_id'] == $vC['id']){
                                $data[$kD]['casts'][$kC]['status'] = '1';
                            }
                            if($vC['understudy']){
                                foreach ($vC['understudy'] as $kU => $vU){
                                    if($value['user_id'] == $vU['user_id'] && $value['cast_id'] == $vU['cast_id']){
                                        $data[$kD]['casts'][$kC]['understudy'][$kU]['status'] = '1';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($data as $key => $value){
            if($value['casts']){
                foreach ($value['casts'] as $kC => $vC){
                    $z = 0;
                    if(isset($vC['status'])) $z++;
                    if($vC['understudy']){
                        foreach ($vC['understudy'] as $kU => $vU){
                            if(isset($vU['status'])) $z++;
                        }
                    }
                    if(!$z){
                        $countR = count($result);
                        $result[$countR]['event_id'] = $value['event_id'];
                        $result[$countR]['date'] = $value['date'];
                        $result[$countR]['time_from'] = $value['time_from'];
                        $result[$countR]['user_id'] = $vC['user_id'];
                    }
                }
            }
        }
        $uniqueUsers = array_unique(\yii\helpers\ArrayHelper::getColumn($result, 'user_id'));
        $uniqueEvents = array_unique(\yii\helpers\ArrayHelper::getColumn($result, 'event_id'));
        
        $getUsers = \app\models\User::find()->select('id, name, surname')->where(['id' => $uniqueUsers])->asArray()->all();
        $getEvents = \app\models\Events::find()->select('id, name')->where(['id' => $uniqueEvents])->asArray()->all();
        
        foreach ($result as $key => $value){
            foreach ($getUsers as $kU => $vU){
                if($value['user_id'] == $vU['id']){
                    $result[$key]['name'] = $vU['name'];
                    $result[$key]['surname'] = $vU['surname'];
                }
            }
            foreach ($getEvents as $kE => $vE){
                if($value['event_id'] == $vE['id']){
                    $result[$key]['event_name'] = $vE['name'];
                }
            }
        }
        
        return $result;
    }
   
}

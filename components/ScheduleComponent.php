<?php

namespace app\components;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;
use app\models\ScheduleEvents;
use app\models\Casts;
use app\models\UserInSchedule;
use app\models\User;
use app\models\Config;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use app\models\Room;
use app\models\TimesheetConfig;

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
        if(isset($result['allEvents'])){
            $result['allEvents'] = self::sortFirstLetter($result['allEvents'], 'name');
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
    
    /**
     * Сортирует массив по алфавиту.
     * 
     * @param array $arr - массив
     * @param string $param - ключ, который нужно отсортировать
     * @param boolean $letterKey - добавлять ли доп.ключи в виде первой буквы
     * 
     * @return array
     */
    public static function sortFirstLetter($arr, $param, $letterKey = false){
        $result = [];
        $alphabet = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J', 'k', 'K', 'l', 'L', 'm', 'M', 'n', 'N', 'o', 'O', 'p', 'P', 'q', 'Q',
            'r', 'R', 's', 'S', 't', 'T', 'u', 'U', 'v', 'V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', 
            'а', 'А', 'б', 'Б', 'в', 'В', 'г', 'Г', 'д', 'Д', 'е', 'Е', 'ё', 'Ё', 'ж', 'Ж', 'з', 'З', 'и', 'И', 'й', 'Й', 'к', 'К', 'л', 'Л', 'м', 'М', 'н', 'Н', 'о', 'О', 
            'п', 'П', 'р', 'Р', 'с', 'С', 'т', 'Т', 'у', 'У', 'ф', 'Ф', 'х', 'Х', 'ц', 'Ц', 'ч', 'Ч', 'ш', 'Ш', 'щ', 'Щ', 'ъ', 'Ъ', 'ы', 'Ы', 'ь', 'Ь', 'э', 'Э', 'ю', 'Ю', 'я', 'Я', '?', '.', '(', '!'];
        foreach($alphabet as $keyA => $valueA){
            foreach($arr as $keyArr => $valueArr){
                $first_letter = mb_substr($valueArr[$param],0,1);
                if($first_letter == $valueA){
                    if($letterKey){
                        $result[$valueA][] = $valueArr;
                    }else{
                        $result[] = $valueArr;
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Проверка на пересечение времени сотрудника. Передаем ID мероприятия из расписания на которое хотим поставить сотрудника
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
        $getAllEvents = ScheduleEvents::find()->select('schedule_events.id, events.name, schedule_events.time_from, schedule_events.time_to, user.name user_name, user.surname')
                ->leftJoin('user_in_schedule', 'schedule_events.id = user_in_schedule.schedule_event_id')
                ->leftJoin('events', 'events.id = schedule_events.event_id')
                ->leftJoin('user', 'user_in_schedule.user_id = user.id')
                ->where(['date(schedule_events.date)' => $findSchedule->date, 'user_in_schedule.user_id' => $userId])
                ->asArray()->all();
        if($getAllEvents){
            foreach ($getAllEvents as $key => $value){
                if($value['time_to'] && $findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(((+$value['time_from'] < +$findSchedule->time_to && +$value['time_from'] >= +$findSchedule->time_from))
                        || (+$value['time_to'] <= +$findSchedule->time_to && +$value['time_to'] > +$findSchedule->time_from) 
                        || (+$value['time_from'] <= +$findSchedule->time_from && +$value['time_to'] >= +$findSchedule->time_to)){
                        $result[] = $value;
                    }
                }elseif($value['time_to'] && !$findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(+$value['time_from'] <= +$findSchedule->time_from && +$value['time_to'] > +$findSchedule->time_from){
                        $result[] = $value;
                    }
                }elseif(!$value['time_to'] && $findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(+$findSchedule->time_from <= +$value['time_from'] && +$findSchedule->time_to > +$value['time_from']){
                        $result[] = $value;
                    }
                }elseif(!$value['time_to'] && !$findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(+$findSchedule->time_from == +$value['time_from']){
                        $result[] = $value;
                    }
                }
            }
//            if($findSchedule->time_to){
//                foreach ($getAllEvents as $key => $value){
//                    if($value['time_to']){
//                        if(((+$value['time_from'] < +$findSchedule->time_to && +$value['time_from'] >= +$findSchedule->time_from))
//                                || (+$value['time_to'] <= +$findSchedule->time_to && +$value['time_to'] > +$findSchedule->time_from) 
//                                || (+$value['time_from'] <= +$findSchedule->time_from && +$value['time_to'] >= +$findSchedule->time_to)){
//                                    $result[] = $value;
//                        }
//                    }else{
//                        if(+$findSchedule->time_from == $value['time_from']){
//                            $result[] = $value;
//                        }
//                    }
//                }
//            }else{
//                foreach ($getAllEvents as $key => $value){
//                    if(+$findSchedule->time_from == $value['time_from']){
//                        $result[] = $value;
//                    }
//                }
//            }
        }
        if($result){
            return $result;
        }else{
            return false;
        }
    }
    
    /**
     * Функция подобная checkIntersect, но принимает на вход новое время, т.к используется
     * для поиска конфликтов при изменении времени мероприятия
     * @param integer $scheduleId
     * @param string $dateParam
     * @param integer $timeFrom
     * @param integer $timeTo
     * @return array
     */
    public static function checkIntersectEdit($scheduleId, $dateParam, $timeFrom, $timeTo = null){
        $result = [];
        $findSchedule = ScheduleEvents::findOne($scheduleId);
        $findUsers = UserInSchedule::find()->where(['schedule_event_id' => $findSchedule->id])->with('user')->asArray()->all();
        $findSchedule->time_from = $timeFrom;
        if($timeTo){
            $findSchedule->time_to = $timeTo;
        }else{
            $findSchedule->time_to = null;
        }
        
        $allEvents = ScheduleEvents::find()->where(['date' => $dateParam])->with('event')->asArray()->all();
        if($findUsers && $allEvents){
            foreach ($allEvents as $key => $value){
                if($value['event']){
                    $thisEvent = ['name' => $value['event']['name']];
                }else{
                    $thisEvent = ['name' => null];
                }
                if($value['time_to'] && $findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(((+$value['time_from'] < +$findSchedule->time_to && +$value['time_from'] >= +$findSchedule->time_from))
                        || (+$value['time_to'] <= +$findSchedule->time_to && +$value['time_to'] > +$findSchedule->time_from) 
                        || (+$value['time_from'] <= +$findSchedule->time_from && +$value['time_to'] >= +$findSchedule->time_to)){

                        $users = UserInSchedule::find()->where(['schedule_event_id' => $value['id']])->with('user')->asArray()->all();
                        foreach ($users as $keyThis => $valueThis){
                            foreach ($findUsers as $keyList => $valueList){
                                if(+$valueThis['user']['id'] == +$valueList['user']['id']){
                                    $valueThis['user']['user_name'] = $valueThis['user']['name'];
                                    $result[] = array_merge($valueThis['user'], $thisEvent);
                                }
                            }
                        }
                    }
                }elseif($value['time_to'] && !$findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(+$value['time_from'] <= +$findSchedule->time_from && +$value['time_to'] > +$findSchedule->time_from){
                        $users = UserInSchedule::find()->where(['schedule_event_id' => $value['id']])->with('user')->asArray()->all();
                        foreach ($users as $keyThis => $valueThis){
                            foreach ($findUsers as $keyList => $valueList){
                                if(+$valueThis['user']['id'] == +$valueList['user']['id']){
                                    $valueThis['user']['user_name'] = $valueThis['user']['name'];
                                    $result[] = array_merge($valueThis['user'], $value['event']);
                                }
                            }
                        }
                    }
                }elseif(!$value['time_to'] && $findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(+$findSchedule->time_from <= +$value['time_from'] && +$findSchedule->time_to > +$value['time_from']){
                        $users = UserInSchedule::find()->where(['schedule_event_id' => $value['id']])->with('user')->asArray()->all();
                        foreach ($users as $keyThis => $valueThis){
                            foreach ($findUsers as $keyList => $valueList){
                                if(+$valueThis['user']['id'] == +$valueList['user']['id']){
                                    $valueThis['user']['user_name'] = $valueThis['user']['name'];
                                    $result[] = array_merge($valueThis['user'], $value['event']);
                                }
                            }
                        }
                    }
                }elseif(!$value['time_to'] && !$findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(+$findSchedule->time_from == +$value['time_from']){
                        $users = UserInSchedule::find()->where(['schedule_event_id' => $value['id']])->with('user')->asArray()->all();
                        foreach ($users as $keyThis => $valueThis){
                            foreach ($findUsers as $keyList => $valueList){
                                if(+$valueThis['user']['id'] == +$valueList['user']['id']){
                                    $valueThis['user']['user_name'] = $valueThis['user']['name'];
                                    $result[] = array_merge($valueThis['user'], $value['event']);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Выполняет проверку на возможность изменения типа мероприятия или самого мероприятия. 
     * Если уже проставлены актеры, то запрещаем
     * 
     * @param integer $scheduleId
     * @param integer $eventType
     * @param integer $eventId
     * @param integer $withoutEvent
     */
    public static function checkEditEvent($scheduleId, $eventType, $eventId, $withoutEvent){
        $result = [];
        $result['result'] = 'ok';
        $event = ScheduleEvents::find()->where(['id' => $scheduleId])
                ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->one();
        
        $spectacleConfig = Config::getConfig('spectacle_event');
        $actorsCat = Config::getConfig('actors_prof_cat');
        
        $existActors = 0; // Будет хранить инфу, имеются ли проставленные актеры на данном мероприятии
        
        if($event['allUsersInEvent']){
            foreach ($event['allUsersInEvent'] as $key => $value){
                if(in_array($value['userWithProf']['userProfession']['prof']['proff_cat_id'], $actorsCat)){
                    $existActors = 1;
                    break;
                }
            }
        }
        
        if($existActors){
            if((int)$withoutEvent > 0){
                if(in_array($event['eventType']['id'], $spectacleConfig)){
                    $result['result'] = 'error';
                    $result['text'][] = 'Вы пытаетесь удалить название спектакля. Сначала удалите актеров, иначе это нарушит работу программы';
                }
            }else{
                
                if((int)$event['eventType']['id'] != (int)$eventType && (in_array($event['eventType']['id'], $spectacleConfig) || in_array($eventType, $spectacleConfig))){
                    $result['result'] = 'error';
                    $result['text'][] = 'Вы пытаетесь изменить тип мероприятия со спектакля или на спектакль. Сначала удалите актеров, иначе это нарушит работу программы';
                }

                if((int)$event['event']['id'] != (int)$eventId && in_array($event['eventType']['id'], $spectacleConfig)){
                    $result['result'] = 'error';
                    $result['text'][] = 'Вы пытаетесь изменить мероприятие. Сначала удалите актеров, иначе это нарушит работу программы';
                }
            }
        }
        Yii::warning($result);
        return $result;
    }
    
    /**
     * Проверка при копировании записи в расписании.
     * 
     * @param int $scheduleId
     * @param int $moveUsers
     * @return array
     */
    public static function checkCopyEvent($scheduleId, $moveUsers){
        $result = [];
        $result['result'] = 'ok';
        $event = ScheduleEvents::find()->where(['id' => $scheduleId])
                ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->one();
        
        $spectacleConfig = Config::getConfig('spectacle_event');
        $actorsCat = Config::getConfig('actors_prof_cat');
        
        $existActors = 0; // Будет хранить инфу, имеются ли проставленные актеры на данном мероприятии
        
        if($event['allUsersInEvent']){
            foreach ($event['allUsersInEvent'] as $key => $value){
                if(in_array($value['userWithProf']['userProfession']['prof']['proff_cat_id'], $actorsCat)){
                    $existActors = 1;
                    break;
                }
            }
        }
        
        if($existActors && in_array($event['eventType']['id'], $spectacleConfig) && (int)$moveUsers > 0){
            $result['result'] = 'error';
            $result['text'][] = 'Вы пытаетесь скопировать из спектакля в котором стоят актеры. Сначала удалите актеров, иначе это нарушит работу программы';
        }
        return $result;
    }
    
    /**
     * Загружает состав и проставленные дни из user_in_schedule для него
     * Внимание! Не загружает дубли
     * 
     * @param integer $month
     * @param integer $year
     * @param integer $event
     * @return array
     */
    public static function loadCastInSchedule($month, $year, $event){
        $data = [];
        $data['cast'] = User::find()->select('user.id, user.name, user.surname, casts.event_id, casts.id cast_id')
                ->leftJoin('casts', 'casts.user_id = user.id')
                ->where([
                    'casts.year' => $year, 
                    'casts.month' => $month, 
                    'casts.event_id' => $event,
                ])
                ->asArray()->all();
        $data['schedule'] = UserInSchedule::find()->select('user_in_schedule.*')
                ->leftJoin('schedule_events', 'schedule_events.id = user_in_schedule.schedule_event_id')
                ->where(['=', 'year(schedule_events.date)', $year])
                ->andWhere(['=', 'month(schedule_events.date)', $month])
                ->andWhere(['schedule_events.event_id' => $event])
                ->asArray()->all();
        return $data;
    }
    
    /**
     * Принимает массив состава с дублями (loadCastInSchedule + joinUnderstudy) и проставляет на указанный месяц и год
     * и вставляет в БД
     * 
     * @param array $data
     * @param integer $month
     * @param integer $year
     * @param integer $event
     * 
     * @return boolean
     */
    public static function copyLastCast($data, $month, $year, $event){
        if(!$data) return false;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            foreach ($data as $key => $value){
                $newCast = new Casts();
                $newCast->event_id = $value['event_id'];
                $newCast->user_id = $value['id'];
                $newCast->month = $month;
                $newCast->year = $year;
                $newCast->save();
                if(isset($value['understudy'])){
                    foreach ($value['understudy'] as $keyU => $valueU){
                        $db->createCommand()->insert('cast_understudy', [
                            'cast_id' => $newCast->id,
                            'user_id' => $valueU['id']
                        ])->execute();
                    }
                }
            }
            $transaction->commit();
        }catch (\Exception $e) {
            $transaction->rollBack();
            //throw $e;
            return false;
        }
        return true;
    }
    
    // Поиск последнего состава
    public static function searchLastCast($month, $year, $eventId, $monthCount){
        $result = [];
        for($i = 0; $i <= $monthCount; $i++){
            $Date = explode(".", date("n.Y", mktime(0, 0, 0, $month - $i, 1, $year)));
            $findCast = Casts::find()->where([
                'year' => $Date[1],
                'month' => $Date[0],
                'event_id' => $eventId
            ])->asArray()->all();
            if($findCast){
                $result['month'] = $Date[0];
                $result['year'] = $Date[1];
                break;
            }
        }
        return $result;
    }
    
    /**
     * Проверяет расписание актеров на заполненность
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function checkFullSchedule($month, $year){
        $spectacleEventConfig = Config::getConfig('spectacle_event');
        $result = [];
        $schedule = ScheduleEvents::find()->select('*')
                ->where(['=', 'year(date)', $year])
                ->andWhere(['=', 'month(date)', $month])
                ->andWhere(['event_type_id' => $spectacleEventConfig])
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

    /**
     * Возвращает статистику по табелю для панели пользователя panel/index
     * 
     * @param $dateFrom
     * @param $dateTo
     * @param $userId
     * 
     * @return array
     */
    public static function panelTimesheetStatistic($dateFrom, $dateTo, $userId){
        $explodeFrom = explode('-', $dateFrom);
        $explodeTo = explode('-', $dateTo);
        
        $from = date('Y-m-d', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $to = date('Y-m-d', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        
        $schedule = ScheduleEvents::find()
            ->leftJoin('events', 'schedule_events.event_id = events.id')
            ->leftJoin('user_in_schedule', 'user_in_schedule.schedule_event_id = schedule_events.id')
            ->where(['between', 'date', $from, $to])
            ->andWhere(['user_in_schedule.user_id' => $userId])
            ->with('eventType')->with('event')->with('profCat')->orderBy('date ASC, time_from ASC')->asArray()->all();

        $timesheetConfig = TimesheetConfig::find()->where(['user_id' => $userId])->asArray()->all();
        return $schedule;
    }
    
    /**
     * Переводит минуты в время
     * 
     */
    public static function minuteToTime($from, $to = false){
        $result = floor($from / 60) .":" .($from % 60 < 10?"0" .$from % 60:$from % 60);
        if($to){
            $result .= floor($to / 60) .":" .($to % 60 < 10?"0" .$to % 60:$to % 60);
        }
        return $result;
    }

    /**
     * Удаляет пользователей в соответствии с конфигурацией.
     * 
     * @param array $schedule - Передаем в виде массива, а не 1 записи
     */
    public static function removeNeedUsers($schedule){
        $spectacleEventConfig = Config::getConfig('spectacle_event');
        $profCatLeave = Config::getConfig('show_in_schedule_prof_cat');

        foreach ($schedule as $key => $value){
            if(!in_array($value['event_type_id'], $spectacleEventConfig)){
                foreach ($value['allUsersInEvent'] as $allKey => $allVal){
                    if(!in_array($allVal['userWithProf']['userProfession']['prof']['proff_cat_id'], $profCatLeave)){
                        unset($schedule[$key]['allUsersInEvent'][$allKey]);
                    }
                }
            }else{
                $schedule[$key]['allUsersInEvent'] = [];
            }
        }
        return $schedule;
    }
    
    /**
     * Загрузка данных для недельного расписания. Используется в генерации расписания по ссылке для сотрудников,
     * поэтому вынесено в метод
     */
    public static function loadThreeSchedule($period){
        $startDate = date('Y-m-d', strtotime($period[0]['year'] ."-" .$period[0]['month'] ."-" .$period[0]['day']));
        $endDate = date('Y-m-d', strtotime($period[1]['year'] ."-" .$period[1]['month'] ."-" .$period[1]['day']));
        $schedule = ScheduleEvents::find()
                ->where(['between', 'date', $startDate, $endDate])
                ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->all();
        $spectacleEventConfig = Config::getConfig('spectacle_event');
        $schedule = self::removeNeedUsers($schedule);
        // Сортировка сотрудников и служб
        foreach ($schedule as $key => $value){
            foreach ($value['allUsersInEvent'] as $allKey => $allVal){
                $schedule[$key]['allUsersInEvent'][$allKey]['userSurname'] = $allVal['userWithProf']['surname'];
            }
            foreach ($value['profCat'] as $keyProf => $valProf){
                $schedule[$key]['profCat'][$keyProf]['alias'] = $valProf['profCat']['alias'];
            }
        }
//                $value['allUsersInEvent'][$allKey]['userSurname'] = $allVal['userWithProf']['surname'];
        foreach ($schedule as $key => $value){
            $schedule[$key]['allUsersInEvent'] = ScheduleComponent::sortFirstLetter($schedule[$key]['allUsersInEvent'], 'userSurname');
            $schedule[$key]['profCat'] = ScheduleComponent::sortFirstLetter($schedule[$key]['profCat'], 'alias');
        }
        return $schedule;
    }
   
}

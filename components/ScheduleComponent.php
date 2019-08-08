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
        $alphabet = ['а', 'А', 'б', 'Б', 'в', 'В', 'г', 'Г', 'д', 'Д', 'е', 'Е', 'ё', 'Ё', 'ж', 'Ж', 'з', 'З', 'и', 'И', 'й', 'Й', 'к', 'К', 'л', 'Л', 'м', 'М', 'н', 'Н', 'о', 'О', 
            'п', 'П', 'р', 'Р', 'с', 'С', 'т', 'Т', 'у', 'У', 'ф', 'Ф', 'х', 'Х', 'ц', 'Ц', 'ч', 'Ч', 'ш', 'Ш', 'щ', 'Щ', 'ъ', 'Ъ', 'ы', 'Ы', 'ь', 'Ь', 'э', 'Э', 'ю', 'Ю', 'я', 'Я'];
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
        $findSchedule->time_to = $timeTo;
        
        $allEvents = ScheduleEvents::find()->where(['date' => $dateParam])->with('event')->asArray()->all();
        if($findUsers && $allEvents){
            foreach ($allEvents as $key => $value){
                if($value['time_to'] && $findSchedule->time_to && +$value['id'] != +$findSchedule->id){
                    if(((+$value['time_from'] < +$findSchedule->time_to && +$value['time_from'] >= +$findSchedule->time_from))
                        || (+$value['time_to'] <= +$findSchedule->time_to && +$value['time_to'] > +$findSchedule->time_from) 
                        || (+$value['time_from'] <= +$findSchedule->time_from && +$value['time_to'] >= +$findSchedule->time_to)){

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
     * Генерация недельного расписания в Excel 
     * @param string $from
     * @param string $to
     * @return 
     */
    public static function excelWeekSchedule($from, $to){
        $weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        $roomMatrix = []; // Соответствие комнаты к столбцу
        $explodeFrom = explode('-', $from);
        $explodeTo = explode('-', $to);
        $dates = [];
        for ($i = 0; $i < 7; $i++){
            $cc = count($dates);
            $dates[$cc]['day'] = date('d', mktime(0, 0, 0, $explodeFrom[1], ($explodeFrom[2] + $i), $explodeFrom[0]));
            $dates[$cc]['month'] = date('m', mktime(0, 0, 0, $explodeFrom[1], ($explodeFrom[2] + $i), $explodeFrom[0]));
            $dates[$cc]['year'] = date('Y', mktime(0, 0, 0, $explodeFrom[1], ($explodeFrom[2] + $i), $explodeFrom[0]));
        }
        
        $dateFrom = date('d.m.Y', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[2], $explodeFrom[0]));
        $dateTo = date('d.m.Y', mktime(0, 0, 0, $explodeTo[1], $explodeTo[2], $explodeTo[0]));

        $schedule = ScheduleEvents::find()
                ->where(['between', 'date', $from, $to])
                ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->all();
        $spectacleEventConfig = Config::getConfig('spectacle_event');
        $actorsProfCat = Config::getConfig('actors_prof_cat');
        // ************************************************************ ВЫНЕСТИ В МЕТОД *****************************************************
        foreach ($schedule as $key => $value){
            if(!in_array($value['event_type_id'], $spectacleEventConfig)){
                foreach ($value['allUsersInEvent'] as $allKey => $allVal){
                    if(!in_array($allVal['userWithProf']['userProfession']['prof']['proff_cat_id'], $actorsProfCat)){
                        unset($schedule[$key]['allUsersInEvent'][$allKey]);
                    }
                }
            }else{
                $schedule[$key]['allUsersInEvent'] = [];
            }
        }
//        echo \yii\helpers\VarDumper::dumpAsString($schedule, 10, true);
        $activesRoom = [];
        
        foreach ($schedule as $key => $value){
            if(!in_array($value['room_id'], $activesRoom)){
                $activesRoom[] = $value['room_id'];
            }
        }
        $rooms = Room::find()->where(['is_active' => 1, 'id' => $activesRoom])->asArray()->all();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValueByColumnAndRow(1, 1, $dateFrom ." - " .$dateTo);
        $sheet->getStyleByColumnAndRow(1, 1)->getFont()->setSize(15);
//        $sheet->getStyleByColumnAndRow(1, 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCellsByColumnAndRow(1, 1, count($rooms) + 1, 1);
        
        $sheet->setCellValueByColumnAndRow(1, 2, "РАСПИСАНИЕ НА НЕДЕЛЮ");
        $sheet->getStyleByColumnAndRow(1, 2)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow(1, 2)->getFont()->setSize(20)->setBold(700);
        $sheet->mergeCellsByColumnAndRow(1, 2, count($rooms) + 1, 2);
        // header
        $sheet->getStyleByColumnAndRow(1, 1)->applyFromArray([
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyleByColumnAndRow(1, 2)->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        for($i = 2; $i <= (count($rooms) +1); $i++){
            $sheet->getStyleByColumnAndRow($i, 1)->applyFromArray([
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => array('argb' => '000000'),
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            $sheet->getStyleByColumnAndRow($i, 2)->applyFromArray([
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => array('argb' => '000000'),
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
        }
        
        $sheet->getStyleByColumnAndRow(count($rooms) + 1, 1)->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ]
            ]
        ]);
        
        $sheet->getStyleByColumnAndRow(count($rooms) + 1, 2)->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ]
            ]
        ]);
        
        // Rooms
        $sheet->setCellValueByColumnAndRow(1, 4, 'Дата');
        $sheet->getStyleByColumnAndRow(1, 4)->applyFromArray([
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ],
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ]
            ]
        ]);
        $sheet->getStyleByColumnAndRow(1, 4)->getFont()->setBold(700);
        $sheet->getStyleByColumnAndRow(1, 4)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
        $roomCount = 2;
        foreach ($rooms as $key => $value){
            $roomMatrix[$value['id']] = $roomCount;
            $sheet->setCellValueByColumnAndRow($roomCount, 4, $value['name']);
            $sheet->getStyleByColumnAndRow($roomCount, 4)->applyFromArray([
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ],
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ]
                ]
            ]);
            $sheet->getStyleByColumnAndRow($roomCount, 4)->getFont()->setBold(700);
            $sheet->getStyleByColumnAndRow($roomCount, 4)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getColumnDimensionByColumn($roomCount)->setWidth(40);
//            $sheet->getColumnDimensionByColumn($roomCount)->setAutoSize(true);
            $roomCount++;
        }
        
        
        $scheduleSort = [];
        foreach ($schedule as $key => $value){
            foreach ($dates as $keyD => $valueD){
                if(strtotime($value['date']) === mktime(0, 0, 0,$valueD['month'], $valueD['day'], $valueD['year'])){
                    $scheduleSort[strtotime($value['date'])][$roomMatrix[$value['room_id']]][intval($value['time_from'])] = $value;
                }
            }
        }
        
        $dayCount = 5;
        foreach ($dates as $key => $value){
            $timeDate = mktime(0, 0, 0,$value['month'], $value['day'], $value['year']);
            $maxCount = 5;
            if(isset($scheduleSort[$timeDate])){
                $weekday = $weekdayName[date('w', $timeDate)];
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setWrapText(true);
                $sheet->setCellValueByColumnAndRow(1, $dayCount, $value['day'] ."." .$value['month'] ."." .$value['year'] ."\n" .$weekday );
                $sheet->getStyleByColumnAndRow(1, $dayCount)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ],
                        'right' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ]
                    ]
                ]);
                foreach ($scheduleSort[$timeDate] as $col => $events){
                    $gapCount = $dayCount;
                    $repeatArr = [];
                    for($i = 0; $i <= 1440; $i++){
                        $resultStr = '';
                        $objRichText = new RichText();
                        if(isset($events[$i]) && !in_array($events[$i]['event']['id'] ."-" .$events[$i]['eventType']['id'], $repeatArr)){
                            // Если спектакль, то повторы в этот день в одну строку
                            if(in_array($events[$i]['eventType']['id'], $spectacleEventConfig)){
                                $repeatArr[] = $events[$i]['event']['id'] ."-" .$events[$i]['eventType']['id'];
                                for($z = 0; $z <= 1440; $z++){
                                    if(isset($events[$z]) && +$events[$i]['event']['id'] == +$events[$z]['event']['id'] && +$events[$i]['eventType']['id'] == +$events[$z]['eventType']['id']){
    //                                    $resultStr .= self::minuteToTime($events[$z]['time_from']) ." ";
                                        $objBold = $objRichText->createTextRun(self::minuteToTime($events[$z]['time_from']) ."- ");
                                        $objBold->getFont()->setBold(true);
                                    }
                                }
                            }else{
                                $objBold = $objRichText->createTextRun(self::minuteToTime($events[$i]['time_from']) ."- ");
                                $objBold->getFont()->setBold(true);
                            }
                            
                            $objRichText->createText("(" .$events[$i]['eventType']['name'] .") ");
//                            $objRichText->createText($events[$i]['event']['name']);
                            $objBold = $objRichText->createTextRun($events[$i]['event']['name']);
                            $objBold->getFont()->setBold(true);
                            if($events[$i]['event']['other_name']){
                                $objBold = $objRichText->createTextRun(" (" .$events[$i]['event']['other_name'] ."). ");
                                $objBold->getFont()->setBold(true);
                            }
//                            $resultStr .= "(" .$events[$i]['eventType']['name'] .") " .$events[$i]['event']['name'];
                            
                            if($events[$i]['add_info']){
//                                $resultStr .= " (" .$events[$i]['add_info'] .")";
                                $objRichText->createText(" (" .$events[$i]['add_info'] .")");
                            }
                            if($events[$i]['allUsersInEvent'] && !in_array($events[$i]['eventType']['id'], $spectacleEventConfig)){
                                $objRichText->createText(" (");
                                $allUsersArr = [];
                                foreach ($events[$i]['allUsersInEvent'] as $keyUser => $valUser){
                                    $allUsersArr[] = $valUser['userWithProf']['surname'];
                                }
                                $objRichText->createText(implode(', ', $allUsersArr) .")");
                            }
                            if($events[$i]['profCat']){
                                $objRichText->createText("\n");
                                $allProffArr = [];
                                foreach ($events[$i]['profCat'] as $keyProf => $valProf){
                                    $allProffArr[] = $valProf['profCat']['alias'];
                                }
                                $objBold = $objRichText->createTextRun(implode(', ', $allProffArr));
                                $objBold->getFont()->setBold(true);
                            }
                            
                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setWrapText(true);
                            $sheet->setCellValueByColumnAndRow($col, $gapCount, $objRichText);
                            if((int)$events[$i]['is_modified'] === 1){
                                $sheet->getStyleByColumnAndRow($col, $gapCount)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                            }
                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            $gapCount++;
                        }
                    }
                    if($gapCount > $maxCount){
                        $maxCount = $gapCount;
                    }
                }
//                var_dump($maxCount);
                $sheet->mergeCellsByColumnAndRow(1, $dayCount, 1, ($maxCount - 1));
                $roomCount = 2;
                foreach ($rooms as $key => $value){
                    $sheet->getStyleByColumnAndRow($roomCount, $dayCount)->applyFromArray([
                        'borders' => [
                            'right' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ]
                        ]
                    ]);
                    if($maxCount - $dayCount > 1){
                        for($k = $dayCount; $k <= $maxCount; $k++){
                            $sheet->getStyleByColumnAndRow($roomCount, $k)->applyFromArray([
                                'borders' => [
                                    'right' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ],
                                    'left' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => array('argb' => '000000'),
                                    ]
                                ]
                                
                            ]);
                        }
                    }
                    $sheet->getStyleByColumnAndRow($roomCount, ($maxCount -1))->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ],
                            'right' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ]
                        ]
                    ]);
                    $roomCount++;
                }
                $sheet->getStyleByColumnAndRow(1, ($maxCount -1))->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ],
                    ]
                ]);
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($dayCount)->setRowHeight(30);
                $dayCount = $maxCount;
            }else{
                $weekday = $weekdayName[date('w', $timeDate)];
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setWrapText(true);
                $sheet->setCellValueByColumnAndRow(1, $dayCount, $value['day'] ."." .$value['month'] ."." .$value['year'] ."\n" .$weekday );
                $sheet->getStyleByColumnAndRow(1, $dayCount)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ],
                        'right' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        ]
                    ]
                ]);
                $roomCount = 2;
                foreach ($rooms as $key => $value){
                    $sheet->getStyleByColumnAndRow($roomCount, $dayCount)->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ],
                            'right' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ]
                        ]
                    ]);
                    $roomCount++;
                }
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($dayCount)->setRowHeight(30);
                $dayCount++;
            }
        }
        
//        exit();
//        echo "<pre>";
//        var_dump($scheduleSort); exit();
//        
//        foreach ($scheduleSort as $keyRow => $valRow){
//            $eventMax = 0;
//            foreach ($valRow as $keyCol => $valCol){
//                $resultStr = '';
//                $repeatArr = [];
//                if(count($valCol) > $eventMax){
//                    $eventMax = count($valCol);
//                }
//                for($i = 0; $i <= 1440; $i++){
//                    if(isset($valCol[$i]) && !in_array($valCol[$i]['event']['id'] ."-" .$valCol[$i]['eventType']['id'], $repeatArr)){
//                        $repeatArr[] = $valCol[$i]['event']['id'] ."-" .$valCol[$i]['eventType']['id'];
//                        for($z = 0; $z <= 1440; $z++){
//                            if(isset($valCol[$z]) && $valCol[$i]['event']['id'] == $valCol[$z]['event']['id'] && $valCol[$i]['eventType']['id'] == $valCol[$z]['eventType']['id']){
//                                $resultStr .= self::minuteToTime($valCol[$z]['time_from']) ." ";
//                            }
//                        }
//                        $resultStr .= "(" .$valCol[$i]['eventType']['name'] .") " .$valCol[$i]['event']['name'] ."\n \n";
//                    }
//                }
//                $sheet->getStyleByColumnAndRow($keyCol, $keyRow)->getAlignment()->setWrapText(true);
//                $sheet->setCellValueByColumnAndRow($keyCol, $keyRow, $resultStr);
//                $sheet->getStyleByColumnAndRow($keyCol, $keyRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//                $sheet->getStyleByColumnAndRow($keyCol, $keyRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
//            }
//        }
        
//        $dayCount = 5;
//        foreach ($dates as $key => $value){
//            $spreadsheet->getActiveSheet()->getRowDimension($dayCount)->setRowHeight(-1);
//            $dayCount++;
//        }
        $filename = "Расписание_" .$dateFrom ."-" .$dateTo .".xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save('files/week_schedule/' .$filename);
        return \Yii::$app->response->sendFile('files/week_schedule/' .$filename);
        
        
    }
    
    public static function minuteToTime($from, $to = false){
        $result = floor($from / 60) .":" .($from % 60 < 10?"0" .$from % 60:$from % 60);
        if($to){
            $result .= floor($to / 60) .":" .($to % 60 < 10?"0" .$to % 60:$to % 60);
        }
        return $result;
    }
   
}

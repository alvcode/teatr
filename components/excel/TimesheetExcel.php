<?php

namespace app\components\excel;

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
use yii\base\Exception;
use app\components\ScheduleComponent;
use app\models\Profession;
use app\components\Formatt;
use app\models\TimesheetConfig;

/**
 *
 * 
 */

class TimesheetExcel extends Model{
    
    public static $timesheetConfig;
    
    /**
     * Генерация табеля в Excel 
     * По тем или иным причинам решено было сделать немного хардкода, далее в комментариях
     * все написано где что
     * 
     * @param string $from
     * @param string $to
     * @param integer $profId
     * @return 
     */
    public static function excelTimesheet($from, $to, $profId){
        // Хардкод настройки. 
        $roomIds = ['3' => 'Большая сцена', '4' => 'Малая сцена'];
        $eventTypes = ['17' => 'Репетиция', '26' => 'Раус'];
        // end
        $weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        
        $explodeFrom = explode('-', $from);
        $explodeTo = explode('-', $to);
        
        $from = date('Y-m-d', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $to = date('Y-m-d', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        $dateFrom = date('d.m.Y', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $dateTo = date('d.m.Y', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        
        $schedule = ScheduleEvents::find()
            ->where(['between', 'date', $from, $to])
            ->andWhere(['or', ['room_id' => array_keys($roomIds)], ['event_type_id' => array_keys($eventTypes)]])
            ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->orderBy('date ASC, time_from ASC')->asArray()->all();
        
        $schedule = ScheduleComponent::transformEventsToTwo($schedule);
        
        $users = User::find()->select('user.id, user.name, user.surname')
                ->leftJoin('user_profession', 'user.id = user_profession.user_id')
                ->where(['user_profession.prof_id' => $profId, 'user.is_active' => 1])
                ->asArray()->all();
        
        self::$timesheetConfig = TimesheetConfig::find()->where(['user_id' => \yii\helpers\ArrayHelper::getColumn($users, 'id')])->asArray()->all();
        
//        var_dump(self::calculateTime(300, 480)); exit();
//echo \yii\helpers\VarDumper::dumpAsString($schedule, 10, true);
        
        $profession = Profession::find()->where(['id' => $profId])->asArray()->one();
        
        /*
         * 
         * Выводим все мероприятия, записываем к каждому номер колонки. ОТСОРТИРОВАТЬ расписание, не забыть
         * Далее перебираем юзеров. Каждому выделяем $roomIds и $eventTypes строк
         * В береборе юзеров перебираем расписание. Если нашли юзера  в allUsersInEvent - подгружаем настройку табеля
         * Если соответствует- вписываем в требуемую строку и колонку часы или выход
         * В каждую строку ведем подсчет сколько вписали, в самом конце вбиваем сумму на каждую
         * Выплевываем
         */
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        //============================================== Добавляем HEAD
        $headRow = 4;
        $headColumn = 3;
        $countAllEvents = 0;
        for($i = 0; $i < $headColumn; $i++){
            $sheet->getStyleByColumnAndRow($i, $headRow)->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
            $sheet->getStyleByColumnAndRow($i, ($headRow +1))->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
            $sheet->getStyleByColumnAndRow($i, ($headRow +2))->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
        }
        foreach ($schedule['schedule'] as $day => $eventId){
            $eventInDay = 0;
            $startColumnDate = $headColumn;
            foreach ($eventId as $eventKey => $eventValue){
                $eventInDay += count($eventId);
                $sameEvent = count($eventValue);
                $startColumnEvent = $headColumn;
                $eventName = '';
                foreach ($eventValue as $keyEvent => $event){
                    $sheet->setCellValueByColumnAndRow($headColumn, ($headRow +2), Formatt::minuteToTime($event['time_from']));
                    $schedule['schedule'][$day][$eventKey][$keyEvent]['column'] = $headColumn;
                    $countAllEvents++;
                    $sheet->getStyleByColumnAndRow($headColumn, ($headRow +2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyleByColumnAndRow($headColumn, ($headRow +2))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyleByColumnAndRow($headColumn, ($headRow +2))->getFont()->setSize(9);
                    $sheet->getColumnDimensionByColumn($headColumn)->setWidth(5);
                    // borders
                    $sheet->getStyleByColumnAndRow($headColumn, $headRow)->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
                    $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
                    $sheet->getStyleByColumnAndRow($headColumn, ($headRow +2))->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
            
                    if($event['event']){
                        $eventName = $event['event']['name'];
                    }else{
                        $eventName = $event['eventType']['name'];
                    }
                    $eventDate = $event['date'];
                    $dateNumber = date('d', strtotime($event['date']));
                    $weekDay = $weekdayName[date('w', strtotime($event['date']))];
                    $headColumn++;
                }
                $sheet->setCellValueByColumnAndRow($startColumnEvent, ($headRow +1), $eventName);
                if($sameEvent == 1){
                    $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getAlignment()->setTextRotation(90);
                }
                $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getAlignment()->setWrapText(true);
                $sheet->mergeCellsByColumnAndRow($startColumnEvent, ($headRow +1), ($headColumn -1), ($headRow +1));
                $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getFont()->setSize(9);
            }
            //Вкидываем дату и объединяем ячейки
            $sheet->setCellValueByColumnAndRow($startColumnDate, $headRow, $dateNumber ." " .$weekDay);
            $sheet->getStyleByColumnAndRow($startColumnDate, $headRow)->getFont()->setSize(9);
            $sheet->getStyleByColumnAndRow($startColumnDate, $headRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCellsByColumnAndRow($startColumnDate, $headRow, ($headColumn -1), $headRow);
        }
        $sheet->setCellValueByColumnAndRow($headColumn, ($headRow +1), 'ВСЕГО выходы');
        $sheet->setCellValueByColumnAndRow($headColumn +1, ($headRow +1), 'ВСЕГО часы');
        $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->getAlignment()->setWrapText(true);
        $sheet->getStyleByColumnAndRow($headColumn +1, ($headRow +1))->getAlignment()->setWrapText(true);
        $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyleByColumnAndRow($headColumn +1, ($headRow +1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow($headColumn +1, ($headRow +1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(($headRow +1))->setRowHeight(60);
        $sheet->setCellValueByColumnAndRow(1, ($headRow +2), 'Фамилия И.О');
        $sheet->getColumnDimensionByColumn(1)->setWidth(25);
        $sheet->getColumnDimensionByColumn(2)->setWidth(14);
        $sheet->getStyleByColumnAndRow(1, ($headRow +2))->getFont()->setBold(700);
        
        // ============================================================= Формируем BODY
        $countLines = count($roomIds) + count($eventTypes);
        $bodyRow = 7;
        $resultColumn = $countAllEvents + 3;
        foreach ($users as $userKey => $userValue){
            $userData = []; // Храним информацию о соответствии строк к room и event_type + общее кол-во часов/выходов
            $sheet->mergeCellsByColumnAndRow(1, $bodyRow, 1, ($bodyRow +($countLines -1)));
            $sheet->getStyleByColumnAndRow(1, ($bodyRow +($countLines -1)))->applyFromArray(self::generateBorders(['bottom', 'left']));
            $sheet->setCellValueByColumnAndRow(1, $bodyRow, $userValue['surname'] ." " .$userValue['name']);
            foreach ($roomIds as $roomKey => $roomVal){
                $c = count($userData);
                $userData[$c]['type'] = 'room';
                $userData[$c]['id'] = $roomKey;
                $userData[$c]['row'] = $bodyRow;
                $userData[$c]['work_time'] = 0;
                $userData[$c]['work_day'] = 0;
                $sheet->setCellValueByColumnAndRow(2, $bodyRow, $roomVal);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->getAlignment()->setWrapText(true);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->getFont()->setSize(9);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->applyFromArray(self::generateBorders(['left', 'bottom', 'right']));
                for($z = 3; $z <= ($countAllEvents + 4); $z++){
                    $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'bottom', 'right']));
                }
                $bodyRow++;
            }
            foreach ($eventTypes as $keyEventType => $valEventType){
                $c = count($userData);
                $userData[$c]['type'] = 'event';
                $userData[$c]['id'] = $keyEventType;
                $userData[$c]['row'] = $bodyRow;
                $userData[$c]['work_time'] = 0;
                $userData[$c]['work_day'] = 0;
                $sheet->setCellValueByColumnAndRow(2, $bodyRow, $valEventType);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->getAlignment()->setWrapText(true);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->getFont()->setSize(9);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->applyFromArray(self::generateBorders(['left', 'bottom', 'right']));
                for($z = 3; $z <= ($countAllEvents + 4); $z++){
                    $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'bottom', 'right']));
                }
                $bodyRow++;
            }
            // Добавляем в ячейки часы/выходы
            foreach ($schedule['schedule'] as $day => $eventId){
                foreach ($eventId as $eventKey => $eventValue){
                    foreach ($eventValue as $keyEvent => $event){
                        if($event['allUsersInEvent']){
                            foreach ($event['allUsersInEvent'] as $usKey => $usVal){
                                $timesheetConfig = self::checkTimesheetConfig($userValue['id'], $event['eventType']['id']);
                                // 1 - часы, 2 - выходы
                                if($timesheetConfig && $timesheetConfig == 1 && $event['time_to']){
                                    if(in_array($event['eventType']['id'], array_keys($eventTypes))){
                                        foreach ($userData as $keyUserData => $valUserData){
                                            if($valUserData['type'] == 'event' && (int)$valUserData['id'] == (int)$event['eventType']['id']){
                                                $workTime = self::calculateTime($event['time_from'], $event['time_to']);
                                                $userData[$keyUserData]['work_time'] += $workTime;
                                                $sheet->setCellValueByColumnAndRow($event['column'], $valUserData['row'], $workTime);
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                            }
                                        }
                                    }else{
                                        foreach ($userData as $keyUserData => $valUserData){
                                            if($valUserData['type'] == 'room' && (int)$valUserData['id'] == (int)$event['room_id']){
                                                $workTime = self::calculateTime($event['time_from'], $event['time_to']);
                                                $userData[$keyUserData]['work_time'] += $workTime;
                                                $sheet->setCellValueByColumnAndRow($event['column'], $valUserData['row'], $workTime);
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                            }
                                        }
                                    }
                                }elseif($timesheetConfig && $timesheetConfig == 2){
                                    if(in_array($event['eventType']['id'], array_keys($eventTypes))){
                                        foreach ($userData as $keyUserData => $valUserData){
                                            if($valUserData['type'] == 'event' && (int)$valUserData['id'] == (int)$event['eventType']['id']){
                                                $userData[$keyUserData]['work_day'] += 1;
                                                $sheet->setCellValueByColumnAndRow($event['column'], $valUserData['row'], '1');
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                            }
                                        }
                                    }else{
                                        foreach ($userData as $keyUserData => $valUserData){
                                            if($valUserData['type'] == 'room' && (int)$valUserData['id'] == (int)$event['room_id']){
                                                $userData[$keyUserData]['work_day'] += 1;
                                                $sheet->setCellValueByColumnAndRow($event['column'], $valUserData['row'], '1');
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                                $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Тут мы должны вписать итоговые значения на основе $userData
        }
        
        $filename = "Табель_(" .$profession['name'] .")_" .$dateFrom ."-" .$dateTo .".xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save('files/timesheets/' .$filename);
        return \Yii::$app->response->sendFile('files/timesheets/' .$filename);
        
        echo \yii\helpers\VarDumper::dumpAsString($schedule, 10, true);
    }
    
    /**
     * Возвращает настройку для border.
     * В параметр передаем массив вида ['top', 'right]
     * @param array $conf
     * @return array
     */
    public static function generateBorders($conf){
        $result = [];
        $result['borders'] = [];
        if($conf){
            foreach ($conf as $value){
                $result['borders'][$value] = [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ];
            }
        }
        return $result;
    }
    
    /**
     * Проверяет, есть ли настройка табеля у юзера для мероприятия
     * @param int $userId
     * @param int $eventType
     * @return int
     */
    public static function checkTimesheetConfig($userId, $eventType){
        $result = 0;
        if(self::$timesheetConfig){
            foreach (self::$timesheetConfig as $key => $value){
                if((int)$value['user_id'] == (int)$userId && (int)$value['event_type_id'] == (int)$eventType){
                    $result = (int)$value['method'];
                }
            }
        }
        return $result;
    }
    
    public static function calculateTime($timeFrom, $timeTo){
        $difference = (int)$timeTo - (int)$timeFrom;
        $result = floor($difference / 60) + (100 / 60 * ($difference % 60) / 100);
        return $result;
    }
   
}

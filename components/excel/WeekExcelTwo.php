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

/**
 *
 * 
 */

class WeekExcelTwo extends Model{
    
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
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        
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
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
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
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => array('argb' => '000000'),
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => array('argb' => '000000'),
                    ],
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => array('argb' => '000000'),
                    ]
                ]
            ]);
            $sheet->getStyleByColumnAndRow($roomCount, 4)->getFont()->setBold(700);
            $sheet->getStyleByColumnAndRow($roomCount, 4)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getColumnDimensionByColumn($roomCount)->setWidth(30);
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
//        echo \yii\helpers\VarDumper::dumpAsString($scheduleSort, 10, true);
        $dayCount = 5;
        foreach ($dates as $key => $value){
            $timeDate = mktime(0, 0, 0,$value['month'], $value['day'], $value['year']);
            $maxCount = 5;
            if(isset($scheduleSort[$timeDate])){
                $weekday = $weekdayName[date('w', $timeDate)];
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setWrapText(true);
                $sheet->setCellValueByColumnAndRow(1, $dayCount, $value['day'] ."." .$value['month'] ."." .$value['year'] ."\n" .$weekday );
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getFont()->setBold(700);
                $sheet->getStyleByColumnAndRow(1, $dayCount)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => array('argb' => '000000'),
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
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
                    $eventCount = count($events);
                    $k = 1;
                    $objRichText = new RichText();
                    for($i = 0; $i <= 1440; $i++){
                        $resultStr = '';
//                        $objRichText = new RichText();
                        if(isset($events[$i]) && !in_array($events[$i]['event']['id'] ."-" .$events[$i]['eventType']['id'], $repeatArr)){
                            // Если спектакль, то повторы в этот день в одну строку
                            if(in_array($events[$i]['eventType']['id'], $spectacleEventConfig)){
                                $repeatArr[] = $events[$i]['event']['id'] ."-" .$events[$i]['eventType']['id'];
                                for($z = 0; $z <= 1440; $z++){
                                    if(isset($events[$z]) && +$events[$i]['event']['id'] == +$events[$z]['event']['id'] && +$events[$i]['eventType']['id'] == +$events[$z]['eventType']['id']){
    //                                    $resultStr .= self::minuteToTime($events[$z]['time_from']) ." ";
                                        $objBold = $objRichText->createTextRun(self::minuteToTime($events[$z]['time_from'], $events[$z]['time_to']) ." ");
                                        $objBold->getFont()->setBold(true);
                                    }
                                }
                            }else{
                                $objBold = $objRichText->createTextRun(self::minuteToTime($events[$i]['time_from'], $events[$i]['time_to']) ." ");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                            }
                            
                            $objBold = $objRichText->createTextRun("(" .$events[$i]['eventType']['name'] .") ");
                            if((int)$events[$i]['is_modified'] === 1){
                                $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                            }
                            
                            if($events[$i]['event']['name']){
                                $objBold = $objRichText->createTextRun($events[$i]['event']['name']);
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                            }
                            
                            if($events[$i]['event']['other_name']){
                                $objBold = $objRichText->createTextRun(" (" .$events[$i]['event']['other_name'] ."). ");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                            }
//                            $resultStr .= "(" .$events[$i]['eventType']['name'] .") " .$events[$i]['event']['name'];
                            
                            if($events[$i]['add_info']){
//                                $objRichText->createText(" (" .$events[$i]['add_info'] .")");
                                $objBold = $objRichText->createTextRun(" (" .$events[$i]['add_info'] .")");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                            }
                            if($events[$i]['allUsersInEvent'] && !in_array($events[$i]['eventType']['id'], $spectacleEventConfig)){
                                $objRichText->createText(" (");
                                $allUsersArr = [];
                                foreach ($events[$i]['allUsersInEvent'] as $keyUser => $valUser){
                                    $allUsersArr[] = $valUser['userWithProf']['surname'];
                                }
//                                $objRichText->createText(implode(', ', $allUsersArr) .")");
                                $objBold = $objRichText->createTextRun(implode(', ', $allUsersArr) .")");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                            }
                            if($events[$i]['profCat']){
                                $objRichText->createText("\n");
                                $allProffArr = [];
                                foreach ($events[$i]['profCat'] as $keyProf => $valProf){
                                    $allProffArr[] = $valProf['profCat']['alias'];
                                }
                                $objBold = $objRichText->createTextRun(implode(', ', $allProffArr));
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                            }
                            if($k < $eventCount){
                                $objRichText->createText("\n \n");
                            }
                            
                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setWrapText(true);
//                            $sheet->setCellValueByColumnAndRow($col, $gapCount, $objRichText);
//                            if((int)$events[$i]['is_modified'] === 1){
//                                $sheet->getStyleByColumnAndRow($col, $gapCount)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
//                            }
//                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
//                            $gapCount++;
                            $k++;
                        }
                    }
                    $sheet->setCellValueByColumnAndRow($col, $gapCount, $objRichText);
                    $gapCount++;
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
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
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
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => array('argb' => '000000'),
                        ],
                    ]
                ]);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setPrintAreaByColumnAndRow(1, 1, (count($rooms) +2), ($maxCount -1));
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($dayCount)->setRowHeight(30);
                $dayCount = $maxCount;
            }else{
                $weekday = $weekdayName[date('w', $timeDate)];
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setWrapText(true);
                $sheet->setCellValueByColumnAndRow(1, $dayCount, $value['day'] ."." .$value['month'] ."." .$value['year'] ."\n" .$weekday );
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getFont()->setBold(700);
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
    
    public static function minuteToTime($from, $to){
        $result = floor($from / 60) .":" .($from % 60 < 10?"0" .$from % 60:$from % 60);
        if($to){
            $result .= "-" .floor($to / 60) .":" .($to % 60 < 10?"0" .$to % 60:$to % 60);
        }
        return $result;
    }
   
}

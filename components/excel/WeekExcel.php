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

class WeekExcel extends Model{
    
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
            $sheet->getStyleByColumnAndRow($roomCount, 4)->getFont()->setSize(13);
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
        
        $dayCount = 5;
        foreach ($dates as $key => $value){
            $timeDate = mktime(0, 0, 0,$value['month'], $value['day'], $value['year']);
            $maxCount = 5;
            if(isset($scheduleSort[$timeDate])){
                $weekday = $weekdayName[date('w', $timeDate)];
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getAlignment()->setWrapText(true);
                $sheet->setCellValueByColumnAndRow(1, $dayCount, $value['day'] ."." .$value['month'] ."." .$value['year'] ."\n" .$weekday );
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getFont()->setBold(700);
                $sheet->getStyleByColumnAndRow(1, $dayCount)->getFont()->setSize(15);
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

                    // Проходим по всем залам за текущую дату, записываем кол-во мероприятий и кол-во букв в каждом
                    $textSizeArr = [];
                    foreach ($scheduleSort[$timeDate] as $coll => $eventss){
                        $repeatArr = [];
                        
                        for($i = 0; $i <= 1440; $i++){
                            if(isset($eventss[$i]) && !in_array($eventss[$i]['event']['id'] ."-" .$eventss[$i]['eventType']['id'], $repeatArr)){
                                if(!isset($textSizeArr[$coll])){
                                    $textSizeArr[$coll]['count'] = 0;
                                    $textSizeArr[$coll]['text'] = "";
                                }
                                if(in_array($eventss[$i]['eventType']['id'], $spectacleEventConfig)){
                                    $repeatArr[] = $eventss[$i]['event']['id'] ."-" .$eventss[$i]['eventType']['id'];
                                    for($z = 0; $z <= 1440; $z++){
                                        if(isset($eventss[$z]) && +$eventss[$i]['event']['id'] == +$eventss[$z]['event']['id'] && +$eventss[$i]['eventType']['id'] == +$eventss[$z]['eventType']['id']){
                                            $textSizeArr[$coll]['text'] .= self::minuteToTime($eventss[$z]['time_from'], $eventss[$z]['time_to']) ." ";
                                        }
                                    }
                                }else{
                                    $textSizeArr[$coll]['text'] .= self::minuteToTime($eventss[$i]['time_from'], $eventss[$i]['time_to']) ." ";
                                }

                                if($eventss[$i]['event']['name']){
                                    $textSizeArr[$coll]['text'] .= $eventss[$i]['event']['name'];
                                }

                                if($eventss[$i]['event']['other_name']){
                                    $textSizeArr[$coll]['text'] .= " (" .$eventss[$i]['event']['other_name'] ."). ";
                                }

                                if($eventss[$i]['add_info']){
                                    $textSizeArr[$coll]['text'] .= " (" .$eventss[$i]['add_info'] .")";
                                }

                                if($eventss[$i]['allUsersInEvent'] && !in_array($eventss[$i]['eventType']['id'], $spectacleEventConfig)){
                                    $textSizeArr[$coll]['text'] .= " (";
                                    $allUsersArr = [];
                                    foreach ($eventss[$i]['allUsersInEvent'] as $keyUser => $valUser){
                                        $allUsersArr[] = $valUser['userWithProf']['surname'];
                                    }
                                    $textSizeArr[$coll]['text'] .= implode(', ', $allUsersArr) .")";
                                }
                                if($eventss[$i]['profCat']){
                                    $textSizeArr[$coll]['text'] .= "\n";
                                    $allProffArr = [];
                                    foreach ($eventss[$i]['profCat'] as $keyProf => $valProf){
                                        $allProffArr[] = $valProf['profCat']['alias'];
                                    }
                                    $textSizeArr[$coll]['text'] .= implode(', ', $allProffArr);
                                }

                                $textSizeArr[$coll]['count']++;
                            }
                        }
                    }
                    // Вписываем кол-во букв в каждый зал
                    foreach ($textSizeArr as $k => $v){
                        $textSizeArr[$k]['size'] = iconv_strlen($v['text']);
                    }

                    // Находим зал, где больше всего буков
                    if($textSizeArr){
                        $maxRoom = ['room' => 0, 'size' => 0, 'count' => 0];
                        foreach ($textSizeArr as $k => $v){
                            if($v['size'] >= $maxRoom['size']){
                                $maxRoom = ['room' => $k, 'size' => $v['size'], 'count' => $v['count']];
                            }
                        }
                    }

                    // ****** Теперь на основе вычислений о максимальном кол-ве символов, вкидываем все внутрь расписания
                    $gapCount = $dayCount;
                    $repeatArr = [];
                    $eventCount = count($events);
                    $k = 1;
                    $objRichText = new RichText();
                    for($i = 0; $i <= 1440; $i++){
                        $resultStr = '';
                        // $objRichText = new RichText();
                        if(isset($events[$i]) && !in_array($events[$i]['event']['id'] ."-" .$events[$i]['eventType']['id'], $repeatArr)){
                            // Если спектакль, то повторы в этот день в одну строку
                            if(in_array($events[$i]['eventType']['id'], $spectacleEventConfig)){
                                $repeatArr[] = $events[$i]['event']['id'] ."-" .$events[$i]['eventType']['id'];
                                for($z = 0; $z <= 1440; $z++){
                                    if(isset($events[$z]) && +$events[$i]['event']['id'] == +$events[$z]['event']['id'] && +$events[$i]['eventType']['id'] == +$events[$z]['eventType']['id']){
                                        $objBold = $objRichText->createTextRun(self::minuteToTime($events[$z]['time_from'], $events[$z]['time_to']) ." ");
                                        if((int)$events[$i]['is_modified'] === 1){
                                            $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                        }
                                        $objBold->getFont()->setBold(true);
                                        $objBold->getFont()->setSize(16);
                                    }
                                }
                            }else{
                                $objBold = $objRichText->createTextRun(self::minuteToTime($events[$i]['time_from'], $events[$i]['time_to']) ." ");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                                $objBold->getFont()->setSize(16);
                            }

                            $objBold = $objRichText->createTextRun("(" .$events[$i]['eventType']['name'] .") ");
                            $objBold->getFont()->setSize(16);
                            if((int)$events[$i]['is_modified'] === 1){
                                $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                            }

                            if($events[$i]['event']['name']){
                                $objBold = $objRichText->createTextRun($events[$i]['event']['name']);
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                                $objBold->getFont()->setSize(16);
                            }
                            
                            if($events[$i]['event']['other_name']){
                                $objBold = $objRichText->createTextRun(" (" .$events[$i]['event']['other_name'] ."). ");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setBold(true);
                                $objBold->getFont()->setSize(16);
                            }
                            
                            if($events[$i]['add_info']){
                                $objBold = $objRichText->createTextRun(" (" .$events[$i]['add_info'] .")");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setSize(16);
                            }
                            
                            if($events[$i]['allUsersInEvent'] && !in_array($events[$i]['eventType']['id'], $spectacleEventConfig)){
                                $objRichText->createText(" (");
                                $allUsersArr = [];
                                foreach ($events[$i]['allUsersInEvent'] as $keyUser => $valUser){
                                    $allUsersArr[] = $valUser['userWithProf']['surname'] . " " .mb_substr($valUser['userWithProf']['name'],0,1) .".";
                                }
                                $objBold = $objRichText->createTextRun(implode(', ', $allUsersArr) .")");
                                if((int)$events[$i]['is_modified'] === 1){
                                    $objBold->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                                }
                                $objBold->getFont()->setSize(16);
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
                                $objBold->getFont()->setSize(16);
                            }
                           if($k < $eventCount){
                               $objRichText->createText("\n \n");
                           }
                            if(isset($maxRoom) && (int)$maxRoom['room'] === (int)$col){
                                $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setWrapText(true);
                                $sheet->setCellValueByColumnAndRow($col, $gapCount, $objRichText);
                                $objRichText = new RichText();
                                $gapCount++;
                            }else{
                                $k++;
                                if($k < $eventCount){
                                    $objRichText->createText("\n \n");
                                }
                            }
                            if((int)$events[$i]['is_modified'] === 1){
                                $sheet->getStyleByColumnAndRow($col, $gapCount)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                            }
                            $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        }
                    }
                    if(isset($maxRoom) && (int)$maxRoom['room'] !== (int)$col){
                        $sheet->getStyleByColumnAndRow($col, $gapCount)->getAlignment()->setWrapText(true);
                        $sheet->setCellValueByColumnAndRow($col, $gapCount, $objRichText);
                        $sheet->mergeCellsByColumnAndRow($col, $gapCount, $col, ($gapCount + ($maxRoom['count'] - 1)));
                        $gapCount++;
                    }
                    if($gapCount > $maxCount){
                        $maxCount = $gapCount;
                    }
                }
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

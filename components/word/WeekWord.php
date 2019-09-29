<?php

namespace app\components\word;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;
use app\models\ScheduleEvents;
use app\models\Casts;
use app\models\UserInSchedule;
use app\models\User;
use app\models\Config;
use app\components\ScheduleComponent;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use app\models\Room;

/**
 *
 * 
 */
class WeekWord extends Model {

    /**
     * Генерация недельного расписания в Word
     * @param string $from
     * @param string $to
     * @return 
     */
    public static function wordWeekSchedule($from, $to) {
        $weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        //$roomMatrix = []; // Соответствие комнаты к столбцу
        $explodeFrom = explode('-', $from);
        $explodeTo = explode('-', $to);
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
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
        $schedule = ScheduleComponent::removeNeedUsers($schedule);

        $spectacleEventConfig = Config::getConfig('spectacle_event');
        $actorsProfCat = Config::getConfig('actors_prof_cat');
        $hideProfCat = Config::getConfig('hide_prof_cat');

        $activesRoom = [];

        foreach ($schedule as $key => $value) {
            if (!in_array($value['room_id'], $activesRoom)) {
                $activesRoom[] = $value['room_id'];
            }
        }
        $rooms = Room::find()->where(['is_active' => 1, 'id' => $activesRoom])->asArray()->all();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);


        $section = $phpWord->addSection(array(
            'orientation' => 'landscape',
                //          'marginLeft'   => 600,
                //          'marginRight'  => 600,
                //          'marginTop'    => 600,
                //          'marginBottom' => 600,
        ));

        $styleTable = array('borderSize' => 6, 'borderColor' => '999999');
        $cellVCentered = array('valign' => 'center');

        $phpWord->addTableStyle('Colspan Rowspan', $styleTable);
        $table = $section->addTable('Colspan Rowspan');

        $roomCount = 1;
        $table->addRow(null, array('tblHeader' => true));
        $table->addCell(2000, ['valign' => 'center', 'align' => 'center'])->addText('Дата', [], ['align' => 'center', 'valign' => 'center']);
        foreach ($rooms as $key => $value) {
            $table->addCell(2000)->addText($value['name'], array('bold' => true), ['align' => 'center', 'valign' => 'center']);
            $roomCount++;
        }

        $scheduleSort = [];
        foreach ($schedule as $key => $value) {
            foreach ($dates as $keyD => $valueD) {
                if (strtotime($value['date']) === mktime(0, 0, 0, $valueD['month'], $valueD['day'], $valueD['year'])) {
                    $scheduleSort[strtotime($value['date'])][$value['room_id']][intval($value['time_from'])][] = $value;
                }
            }
        }
//echo "<pre>";
//        var_dump($roomMatrix); exit();
        foreach ($dates as $key => $value) {
            $timeDate = mktime(0, 0, 0, $value['month'], $value['day'], $value['year']);
            if (isset($scheduleSort[$timeDate])) {
                $weekday = $weekdayName[date('w', $timeDate)];
                $table->addRow(null, array('tblHeader' => false));
                $cell = $table->addCell(2000)->createTextRun(['valign' => 'center', 'align' => 'center']);
                $cell->addText($value['day'] . "." . $value['month'] . "." . $value['year'], ['bold' => true], ['valign' => 'center', 'align' => 'center']);
                $cell->addTextBreak(1);
                $cell->addText($weekday, ['bold' => true], ['valign' => 'center', 'align' => 'center']);

                // Записываем ячейки для каждой комнаты
                $roomObjects = [];
                foreach ($rooms as $key => $value) {
                    $roomObjects[$value['id']] = $table->addCell(2000)->createTextRun();
                }

                foreach ($scheduleSort[$timeDate] as $col => $events) {
                    $repeatArr = [];
                    // Создаем createTextRun
//                    $cell = $table->addCell(2000)->createTextRun(['valign' => 'center', 'align' => 'center']);
                    $eventCount = 0;
                    for ($i = 0; $i <= 1440; $i++) {
                        if (isset($events[$i])) {
                            // Пробегаемся по самим мероприятиям
                            foreach ($events[$i] as $kk => $eventData) {
                                if($eventCount > 0){
                                    $roomObjects[$col]->addTextBreak(2);
                                }
                                if (!in_array($eventData['event']['id'] . "-" . $eventData['eventType']['id'], $repeatArr)) {
                                    $timeRepeatText = [];
                                    if (in_array($eventData['eventType']['id'], $spectacleEventConfig)) {
                                        $repeatArr[] = $eventData['event']['id'] . "-" . $eventData['eventType']['id'];
                                        for ($z = 0; $z <= 1440; $z++) {
                                            if (isset($events[$z])) {
                                                foreach ($events[$z] as $keyCheck => $eventCheck) {
                                                    if (+$eventData['event']['id'] == +$eventCheck['event']['id'] && +$eventData['eventType']['id'] == +$eventCheck['eventType']['id'] /* && +$eventData['id'] != +$eventCheck['id'] */) {
                                                        $timeRepeatText[] = self::minuteToTime($eventCheck['time_from'], $eventCheck['time_to']);
                                                    }
                                                }
                                            }
                                        }
                                        if((int) $eventData['is_modified'] === 1) {
                                            $roomObjects[$col]->addText(implode(" / ", $timeRepeatText), ['bold' => true, 'color' => '#FD3333', 'underline' => 'single']);
                                        } else {
                                            $roomObjects[$col]->addText(implode(" / ", $timeRepeatText), ['bold' => true, 'underline' => 'single']);
                                        }
                                    } else {
                                        if((int) $eventData['is_modified'] === 1) {
                                            $roomObjects[$col]->addText(self::minuteToTime($eventData['time_from'], $eventData['time_to']) . " ", ['bold' => true, 'color' => '#FD3333', 'underline' => 'single']);
                                        } else {
                                            $roomObjects[$col]->addText(self::minuteToTime($eventData['time_from'], $eventData['time_to']) . " ", ['bold' => true, 'underline' => 'single']);
                                        }
                                    }
//                                }

                                if((int) $eventData['is_modified'] === 1) {
                                    $roomObjects[$col]->addText("(" . $eventData['eventType']['name'] . ") ", ['color' => '#FD3333']);
                                }else{
                                    $roomObjects[$col]->addText("(" . $eventData['eventType']['name'] . ") ");
                                }

                                if ($eventData['event']['name']) {
                                    if((int) $eventData['is_modified'] === 1) {
                                        $roomObjects[$col]->addText($eventData['event']['name'], ['bold' => true, 'color' => '#FD3333']);
                                    }else{
                                        $roomObjects[$col]->addText($eventData['event']['name'], ['bold' => true]);
                                    }
                                }

                                if ($eventData['event']['other_name']) {
                                    if((int) $eventData['is_modified'] === 1) {
                                        $roomObjects[$col]->addText(" (" . $eventData['event']['other_name'] . ") ", ['color' => '#FD3333']);
                                    }else{
                                        $roomObjects[$col]->addText(" (" . $eventData['event']['other_name'] . ") ");
                                    }
                                }

                                if ($eventData['allUsersInEvent'] && !in_array($eventData['eventType']['id'], $spectacleEventConfig)) {
                                    $allUsersArr = [];
                                    // Сортируем по алфавиту
                                    foreach ($eventData['allUsersInEvent'] as $keyUser => $valUser){
                                        $eventData['allUsersInEvent'][$keyUser]['userSurname'] = $valUser['userWithProf']['surname'];
                                    }
                                    $eventData['allUsersInEvent'] = \app\components\ScheduleComponent::sortFirstLetter($eventData['allUsersInEvent'], 'userSurname');
                                    foreach ($eventData['allUsersInEvent'] as $keyUser => $valUser) {
                                        if (+$valUser['userWithProf']['userProfession']['prof']['proff_cat_id'] != $actorsProfCat[0]) {
                                            $allUsersArr[] = $valUser['userWithProf']['surname'] .(+$valUser['userWithProf']['show_full_name'] == 1?" " .$valUser['userWithProf']['name']:"");
                                        }
                                    }
                                    if ($allUsersArr) {
                                        if((int) $eventData['is_modified'] === 1) {
                                            $roomObjects[$col]->addText(" " . implode(', ', $allUsersArr) . ".", ['size' => 10, 'color' => '#FD3333']);
                                        }else{
                                            $roomObjects[$col]->addText(" " . implode(', ', $allUsersArr) . ".", ['size' => 10]);
                                        }
                                    }
                                }

                                if ($eventData['add_info']) {
                                    if((int) $eventData['is_modified'] === 1) {
                                        $roomObjects[$col]->addText(" (" . $eventData['add_info'] . ")", ['color' => '#FD3333']);
                                    }else{
                                        $roomObjects[$col]->addText(" (" . $eventData['add_info'] . ")");
                                    }
                                }
                                
                                // Если is_all > 0, то отображаем слово ВСЕ, иначе- фамилии
                                $allUsersArr = [];
                                if((int)$eventData['is_all'] > 0){
                                    $allUsersArr[] = '(ВСЕ)';
                                }else{
                                    if ($eventData['allUsersInEvent'] && !in_array($eventData['eventType']['id'], $spectacleEventConfig)) {
                                        $allUsersArr = [];
                                        // Сортируем по алфавиту
                                        foreach ($eventData['allUsersInEvent'] as $keyUser => $valUser){
                                            $eventData['allUsersInEvent'][$keyUser]['userSurname'] = $valUser['userWithProf']['surname'];
                                        }
                                        $eventData['allUsersInEvent'] = \app\components\ScheduleComponent::sortFirstLetter($eventData['allUsersInEvent'], 'userSurname');
                                        foreach ($eventData['allUsersInEvent'] as $keyUser => $valUser) {
                                            if (+$valUser['userWithProf']['userProfession']['prof']['proff_cat_id'] == $actorsProfCat[0]) {
                                                $allUsersArr[] = $valUser['userWithProf']['surname'] .(+$valUser['userWithProf']['show_full_name'] == 1?" " .$valUser['userWithProf']['name']:"");;
                                            }
                                        }
                                    }
                                }
                                if ($allUsersArr) {
                                    if((int) $eventData['is_modified'] === 1) {
                                        $roomObjects[$col]->addText(" " . implode(', ', $allUsersArr) . ".", ['size' => 10, 'color' => '#FD3333']);
                                    }else{
                                        $roomObjects[$col]->addText(" " . implode(', ', $allUsersArr) . ".", ['size' => 10]);
                                    }
                                }

                                if ($eventData['profCat']) {
                                    $roomObjects[$col]->addTextBreak(1);
                                    $allProffArr = [];
                                    // Сортируем по алфавиту
                                    foreach ($eventData['profCat'] as $keyProf => $valProf){
                                        $eventData['profCat'][$keyProf]['alias'] = $valProf['profCat']['alias'];
                                    }
                                    $eventData['profCat'] = \app\components\ScheduleComponent::sortFirstLetter($eventData['profCat'], 'alias');
                                    foreach ($eventData['profCat'] as $keyProf => $valProf) {
                                        // Скрываем службы, согласно настройкам
                                        if(!in_array($valProf['profCat']['id'], $hideProfCat)){
                                            $allProffArr[] = $valProf['profCat']['alias'];
                                        }
                                    }
                                    if ($allProffArr) {
                                        if((int) $eventData['is_modified'] === 1) {
                                            $roomObjects[$col]->addText(implode(', ', $allProffArr), ['bold' => true, 'color' => '#FD3333']);
                                        }else{
                                            $roomObjects[$col]->addText(implode(', ', $allProffArr), ['bold' => true]);
                                        }
                                    }
                                }
                                $eventCount++;
                            }
                            }
                        }
                    }
                }
            }
        }

        $filename = "Week_" .$dateFrom ."-" .$dateTo .".docx";
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('files/week_schedule/' .$filename);

        return \Yii::$app->response->sendFile('files/week_schedule/' .$filename);


//        $filename = "Расписание_" .$dateFrom ."-" .$dateTo .".xlsx";
//        $writer = new Xlsx($spreadsheet);
//        $writer->save('files/week_schedule/' .$filename);
//        return \Yii::$app->response->sendFile('files/week_schedule/' .$filename);
    }

    public static function minuteToTime($from, $to) {
        $result = floor($from / 60) . ":" . ($from % 60 < 10 ? "0" . $from % 60 : $from % 60);
        if ($to) {
            $result .= "-" . floor($to / 60) . ":" . ($to % 60 < 10 ? "0" . $to % 60 : $to % 60);
        }
        return $result;
    }

}

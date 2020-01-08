<?php
/**
 * Генерирование табеля
 * Видит Бог, я не виноват в этом дерьме
 */
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
use app\models\ProffCategories;

/**
 *
 * 
 */

class TimesheetExcel extends Model{
    
    // Конфиг. Хардкод, так как меняется что-то чуть ли не каждую неделю, сами не знают чего хотят
    public $roomIds = ['3' => 'Большая сцена', '4' => 'Малая сцена'];
    public $eventTypes = ['17' => 'Репетиция', '26' => 'Раус'];
    
    
    // Дата от
    public $from;
    
    // Дата до
    public $to;
    
    // id профессии или службы. В зависимости от $mode понимаем что тут лежит
    public $profId;
    
    // Режим работы. prof или profCat (поиск профессии или сразу всей службы)
    public $mode;
    
    // Профессия или Служба с которой работаем для вывода в название
    public $profName;
    
    // Конфигурация табеля
    public $timesheetConfig;
    
    // Настройки приложения
    public $mainConfig = [];
    
    // Загруженное расписание
    protected $schedule;
    
    // Загруженные юзеры
    protected $users;
    
    public function __construct($from, $to, $profId, $mode) {
        parent::__construct();
        if(!$from || !$to || !$profId || !$mode){
            throw new Exception('Переданы не все параметры');
        }
        $this->from = $from;
        $this->to = $to;
        $this->profId = $profId;
        $this->mode = $mode;
        
        $this->loadUsers();
        $this->loadTimesheetConfig();
        $this->loadMainConfig();
        $this->loadProfName();
        $this->loadSchedule();
    }
    
//    public function init($from, $to, $profId, $mode){
//        parent::init();
//        if(!$from || !$to || !$profId || !$mode){
//            throw new Exception('Переданы не все параметры');
//        }
//        $this->from = $from;
//        $this->to = $to;
//        $this->profId = $profId;
//        $this->mode = $mode;
//        
//        $this->loadSchedule();
//        $this->loadUsers();
//        $this->loadTimesheetConfig();
//        $this->loadProfName();
//    }
    
    /**
     * Загружает расписание
     */
    public function loadSchedule(){
        $explodeFrom = explode('-', $this->from);
        $explodeTo = explode('-', $this->to);
        
        $from = date('Y-m-d', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $to = date('Y-m-d', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        
        $schedule = ScheduleEvents::find()
            ->leftJoin('events', 'schedule_events.event_id = events.id')
            ->where(['between', 'date', $from, $to])
            ->andWhere(['or', [
                    //'room_id' => array_keys($this->roomIds), 
                    'events.category_id' => 1,
                    'event_type_id' => $this->mainConfig
                ], ['event_type_id' => array_keys($this->eventTypes), 'events.category_id' => 1]])
//            ->andWhere(['events.category_id' => 1])
            ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->orderBy('date ASC, time_from ASC')->asArray()->all();
        
        
        $this->schedule = ScheduleComponent::transformEventsToTwo($schedule);
//        echo "<pre>";
//        var_dump($this->schedule); exit();
    }
    
    /**
     * Загружает юзеров
     */
    public function loadUsers(){
        if($this->mode == 'prof'){
            $users = User::find()->select('user.id, user.name, user.surname')
                ->leftJoin('user_profession', 'user.id = user_profession.user_id')
                ->where(['user_profession.prof_id' => $this->profId, 'user.is_active' => 1])
                ->asArray()->all();
            $this->users = ScheduleComponent::sortFirstLetter($users, 'surname');
        }elseif($this->mode == 'profCat'){
            $users = User::find()->select('user.id, user.name, user.surname')
                ->leftJoin('user_profession', 'user.id = user_profession.user_id')
                ->leftJoin('profession', 'user_profession.prof_id = profession.id')
                ->where(['profession.proff_cat_id' => $this->profId, 'user.is_active' => 1])
                ->asArray()->all();
            $this->users = ScheduleComponent::sortFirstLetter($users, 'surname');
        }
    }
    
    /**
     * Загружает настройки табеля
     */
    public function loadTimesheetConfig(){
        $this->timesheetConfig = TimesheetConfig::find()->where(['user_id' => \yii\helpers\ArrayHelper::getColumn($this->users, 'id')])->asArray()->all();
    }
    
    public function loadMainConfig(){
        $allConfig = Config::getAllConfig();
        foreach ($allConfig['spectacle_event'] as $value){
            $this->mainConfig[count($this->mainConfig)] = $value;
        }
    }
    
    public function loadProfName(){
        if($this->mode == 'prof'){
            $this->profName = Profession::find()->where(['id' => $this->profId])->asArray()->one();
        }elseif($this->mode == 'profCat'){
            $this->profName = ProffCategories::find()->where(['id' => $this->profId])->asArray()->one();
        }
    }
    
    public function checkTimeError(){
        $result = [];
        foreach ($this->schedule['schedule'] as $day => $eventId){
            foreach ($eventId as $eventKey => $eventValue){
                foreach ($eventValue as $keyEvent => $event){
                    if(in_array($event['eventType']['id'], array_keys($this->eventTypes)) || in_array($event['room_id'], array_keys($this->roomIds))){
                        if(!$event['time_to']){
                            $c = count($result);
                            $result[$c]['name'] = $event['event']['name'];
                            $result[$c]['type'] = $event['eventType']['name'];
                            $result[$c]['date'] = date('d.m.Y', strtotime($event['date']));
                            $result[$c]['time'] = Formatt::minuteToTime($event['time_from']);
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    
    /**
     * Генерация табеля в Excel 
     * По тем или иным причинам решено было сделать немного хардкода, далее в комментариях
     * все написано где что
     * 
     * @return 
     */
    public function run(){
        // Хардкод настройки. Перенести в параметры класса 
//        $roomIds = ['3' => 'Большая сцена', '4' => 'Малая сцена'];
//        $eventTypes = ['17' => 'Репетиция', '26' => 'Раус'];
        // end
        $weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        
        $explodeFrom = explode('-', $this->from);
        $explodeTo = explode('-', $this->to);
        
        $from = date('Y-m-d', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $to = date('Y-m-d', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        $dateFrom = date('d.m.Y', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $dateTo = date('d.m.Y', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        
//        $schedule = ScheduleEvents::find()
//            ->leftJoin('events', 'schedule_events.event_id = events.id')
//            ->where(['between', 'date', $from, $to])
//            ->andWhere(['or', ['room_id' => array_keys($roomIds)], ['event_type_id' => array_keys($eventTypes)]])
//            ->andWhere(['events.category_id' => 1])
//            ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->orderBy('date ASC, time_from ASC')->asArray()->all();
        
//        $schedule = ScheduleComponent::transformEventsToTwo($schedule);
        
//        $users = User::find()->select('user.id, user.name, user.surname')
//                ->leftJoin('user_profession', 'user.id = user_profession.user_id')
//                ->where(['user_profession.prof_id' => $profId, 'user.is_active' => 1])
//                ->asArray()->all();
        
//        self::$timesheetConfig = TimesheetConfig::find()->where(['user_id' => \yii\helpers\ArrayHelper::getColumn($users, 'id')])->asArray()->all();
        
//        var_dump(self::calculateTime(300, 480)); exit();
//echo \yii\helpers\VarDumper::dumpAsString($schedule, 10, true);
        
//        $profession = Profession::find()->where(['id' => $profId])->asArray()->one();
        
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
        foreach ($this->schedule['schedule'] as $day => $eventId){
            $eventInDay = 0;
            $startColumnDate = $headColumn;
            foreach ($eventId as $eventKey => $eventValue){
                $eventInDay += count($eventId);
                $sameEvent = count($eventValue);
                $startColumnEvent = $headColumn;
                $eventName = '';
                foreach ($eventValue as $keyEvent => $event){
                    $sheet->setCellValueByColumnAndRow($headColumn, ($headRow +2), Formatt::minuteToTime($event['time_from']));
                    $this->schedule['schedule'][$day][$eventKey][$keyEvent]['column'] = $headColumn;
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
        $countLines = count($this->roomIds) + count($this->eventTypes);
        $bodyRow = 7;
        $resultColumn = $countAllEvents + 3;
        foreach ($this->users as $userKey => $userValue){
            $userData = []; // Храним информацию о соответствии строк к room и event_type + общее кол-во часов/выходов
            $sheet->mergeCellsByColumnAndRow(1, $bodyRow, 1, ($bodyRow +($countLines -1)));
            $sheet->getStyleByColumnAndRow(1, ($bodyRow +($countLines -1)))->applyFromArray(self::generateBorders(['left']));
            $sheet->getStyleByColumnAndRow(1, ($bodyRow +($countLines -1)))->applyFromArray(self::generateBorders(['bottom'], 'medium'));
            $sheet->setCellValueByColumnAndRow(1, $bodyRow, $userValue['surname'] ." " .$userValue['name']);
            foreach ($this->roomIds as $roomKey => $roomVal){
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
                $sheet->getStyleByColumnAndRow($countAllEvents + 3, $bodyRow)->applyFromArray(self::generateBorders(['left'], 'medium'));
                for($z = 3; $z <= ($countAllEvents + 4); $z++){
                    $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'bottom', 'right']));
                }
                $bodyRow++;
            }
            $t = 1;
            foreach ($this->eventTypes as $keyEventType => $valEventType){
                $c = count($userData);
                $userData[$c]['type'] = 'event';
                $userData[$c]['id'] = $keyEventType;
                $userData[$c]['row'] = $bodyRow;
                $userData[$c]['work_time'] = 0; // часы
                $userData[$c]['work_day'] = 0; // выходы
                $sheet->setCellValueByColumnAndRow(2, $bodyRow, $valEventType);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->getAlignment()->setWrapText(true);
                $sheet->getStyleByColumnAndRow(2, $bodyRow)->getFont()->setSize(9);
                $sheet->getStyleByColumnAndRow($countAllEvents + 3, $bodyRow)->applyFromArray(self::generateBorders(['left'], 'medium'));
                if($t == count($this->eventTypes)){
                    $sheet->getStyleByColumnAndRow(2, $bodyRow)->applyFromArray(self::generateBorders(['left', 'right']));
                    $sheet->getStyleByColumnAndRow(2, $bodyRow)->applyFromArray(self::generateBorders(['bottom'], 'medium'));
                }else{
                    $sheet->getStyleByColumnAndRow(2, $bodyRow)->applyFromArray(self::generateBorders(['left', 'bottom', 'right']));
                }
                for($z = 3; $z <= ($countAllEvents + 4); $z++){
                    if($t == count($this->eventTypes)){
                        $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'right']));
                        $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['bottom'], 'medium'));
                    }else{
                        $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'bottom', 'right']));
                    }
                }
                $bodyRow++;
                $t++;
            }
            // Добавляем в ячейки часы/выходы
            foreach ($this->schedule['schedule'] as $day => $eventId){
                foreach ($eventId as $eventKey => $eventValue){
                    foreach ($eventValue as $keyEvent => $event){
                        if($event['allUsersInEvent']){
                            foreach ($event['allUsersInEvent'] as $usKey => $usVal){
                                if((int)$userValue['id'] == (int)$usVal['user_id']){
                                    $timesheetConfig = $this->checkTimesheetConfig($userValue['id'], $event['eventType']['id']);
                                    // 1 - часы, 2 - выходы
                                    if($timesheetConfig && (int)$timesheetConfig == 1 && $event['time_to']){
                                        if(in_array($event['eventType']['id'], array_keys($this->eventTypes))){
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
                                            // Тут значитсяяяяяя, если $event['room_id'] != большой или малой сцене, то вхерачиваем все в малую сцену,
                                            // но есть подозрение, что делать это нужно не в этом цикле, а уровнем выше, завтра трезвому нужно разобраться
                                            foreach ($userData as $keyUserData => $valUserData){
                                                if($valUserData['type'] == 'room' && ((int)$valUserData['id'] == (int)$event['room_id']
                                                        || ((int)$valUserData['id'] == 4 && !in_array($event['room_id'], array_keys($this->roomIds))))){
                                                    $workTime = self::calculateTime($event['time_from'], $event['time_to']);
                                                    $userData[$keyUserData]['work_time'] += $workTime;
                                                    $sheet->setCellValueByColumnAndRow($event['column'], $valUserData['row'], $workTime);
                                                    $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                                    $sheet->getStyleByColumnAndRow($event['column'], $valUserData['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                                }
                                            }
                                        }
                                    }elseif($timesheetConfig && (int)$timesheetConfig == 2){
                                        if(in_array($event['eventType']['id'], array_keys($this->eventTypes))){
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
                                                if($valUserData['type'] == 'room' && ((int)$valUserData['id'] == (int)$event['room_id']
                                                    || ((int)$valUserData['id'] == 4 && !in_array($event['room_id'], array_keys($this->roomIds))))){
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
            }
            $endLiter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($headColumn - 1);

            foreach ($userData as $k => $v){
                if($v['work_day']){
                    $sheet->getCellByColumnAndRow($headColumn, $v['row'])->setValue('=SUMPRODUCT(C'. $v['row'] .':'. $endLiter . $v['row'] .')');
//                    $sheet->setCellValueByColumnAndRow($headColumn, $v['row'], $v['work_day']);
                    $sheet->getStyleByColumnAndRow($headColumn, $v['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyleByColumnAndRow($headColumn, $v['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
                if($v['work_time']){
                    $sheet->getCellByColumnAndRow($headColumn +1, $v['row'])->setValue('=SUMPRODUCT(C'. $v['row'] .':'. $endLiter . $v['row'] .')');
//                    $sheet->setCellValueByColumnAndRow($headColumn +1, $v['row'], $v['work_time']);
                    $sheet->getStyleByColumnAndRow($headColumn +1, $v['row'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyleByColumnAndRow($headColumn +1, $v['row'])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }
        }
        $filename = "Tabel_(" .$this->profName['name'] .")_" .$dateFrom ."-" .$dateTo .".xlsx";
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
    public static function generateBorders($conf, $style = 'thin'){
        $styleConst = false;
        if($style == 'thin'){
            $styleConst = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        }elseif($style == 'medium'){
            $styleConst = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;
        }
        $result = [];
        $result['borders'] = [];
        if($conf){
            foreach ($conf as $value){
                $result['borders'][$value] = [
                    'borderStyle' => $styleConst,
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
    public function checkTimesheetConfig($userId, $eventType){
        $result = 0;
        if($this->timesheetConfig){
            foreach ($this->timesheetConfig as $key => $value){
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

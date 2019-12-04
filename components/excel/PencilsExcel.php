<?php
/**
 * Генерирование так называемых "карандашей" (отображение всех сотрудников на месяц в спектаклях)
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

class PencilsExcel extends Model{

    public $colors = ['EC7063','A569BD','5DADE2','45B39D','58D68D','F4D03F',
        'EB984E','DC7633','D7DBDD','B2BABB','808B96','0E6251','4A235A','7D6608','6E2C00',
        'A9DFBF','FADBD8','FAD7A0','E6B0AA','EC7063','A569BD','5DADE2','45B39D','58D68D','F4D03F',
        'EB984E','DC7633','D7DBDD','B2BABB','808B96','0E6251','4A235A','7D6608','6E2C00'
    ];
    
    // Месяц
    public $month;
    
    // Год
    public $year;
    
    // id профессии или службы. В зависимости от $mode понимаем что тут лежит
    public $profCatId;
    
    // Профессия или Служба с которой работаем для вывода в название
    public $profCatName;
    
    // Конфигурация табеля
    public $timesheetConfig;
    
    // Настройки приложения
    public $mainConfig = [];
    
    // Загруженное расписание
    protected $schedule;
    
    // Загруженные юзеры
    protected $users;
    
    public function __construct($month, $year, $profCatId) {
        parent::__construct();
        if(!$month || !$year || !$profCatId){
            throw new Exception('Переданы не все параметры');
        }
        $this->month = $month;
        $this->year = $year;
        $this->profCatId = $profCatId;
        
        $this->loadUsers();
        $this->loadMainConfig();
        $this->loadProfCatName();
        $this->loadSchedule();
    }
    
    
    /**
     * Загружает расписание
     */
    public function loadSchedule(){
        $schedule = ScheduleEvents::find()
            ->leftJoin('events', 'schedule_events.event_id = events.id')
            ->where(['=', 'year(date)', $this->year])
            ->andWhere(['=', 'month(date)', $this->month])
            ->andWhere(['event_type_id' => $this->mainConfig['schedule_two_event_type']])
            ->andWhere(['events.category_id' => 1])
            ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->orderBy('date ASC, time_from ASC')->asArray()->all();
        
        $this->schedule = ScheduleComponent::transformEventsToTwo($schedule);
//        echo "<pre>";
//        var_dump($this->schedule); exit();
    }
    
    /**
     * Загружаем юзеров
     */
    public function loadUsers(){
        $users = User::find()->select('user.id, user.name, user.surname')
            ->leftJoin('user_profession', 'user.id = user_profession.user_id')
            ->leftJoin('profession', 'user_profession.prof_id = profession.id')
            ->where(['profession.proff_cat_id' => $this->profCatId, 'user.is_active' => 1])
            ->asArray()->all();
        $this->users = ScheduleComponent::sortFirstLetter($users, 'surname');
    }
    
    
    public function loadMainConfig(){
        $this->mainConfig = Config::getAllConfig();
    }
    
    public function loadProfCatName(){
        $this->profCatName = ProffCategories::find()->where(['id' => $this->profCatId])->asArray()->one();
    }
    
    
    /**
     * Генерация табеля в Excel 
     * По тем или иным причинам решено было сделать немного хардкода, далее в комментариях
     * все написано где что
     * 
     * @return 
     */
    public function run(){
        $weekdayName = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        //============================================== Добавляем HEAD
        $headRow = 4;
        $headColumn = 2;
        $countAllEvents = 0;
        for($i = 0; $i < $headColumn; $i++){
            $sheet->getStyleByColumnAndRow($i, $headRow)->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
            $sheet->getStyleByColumnAndRow($i, ($headRow +1))->applyFromArray(self::generateBorders(['top', 'right', 'bottom', 'left']));
        }
        // $eventColors = [];
        $eventsListIds = [];
        $existsEvent = false;
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
                        $existsEvent = true;
                        if(!in_array($event['event']['id'], $eventsListIds)){
                            $eventsListIds[] = $event['event']['id'];
                        }
                    }else{
                        $eventName = $event['eventType']['name'];
                        $existsEvent = false;
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
                if($existsEvent){
                    $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($this->colors[array_search($event['event']['id'], $eventsListIds)]);
                }
                $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyleByColumnAndRow($startColumnEvent, ($headRow +1))->getFont()->setSize(9);
            }
            //Вкидываем дату и объединяем ячейки
            $sheet->setCellValueByColumnAndRow($startColumnDate, $headRow, $dateNumber ." " .$weekDay);
            $sheet->getStyleByColumnAndRow($startColumnDate, $headRow)->getFont()->setSize(9);
            $sheet->getStyleByColumnAndRow($startColumnDate, $headRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCellsByColumnAndRow($startColumnDate, $headRow, ($headColumn -1), $headRow);
        }
        // var_dump($eventsListIds); exit();
//        $sheet->setCellValueByColumnAndRow($headColumn, ($headRow +1), 'ВСЕГО выходы');
//        $sheet->setCellValueByColumnAndRow($headColumn +1, ($headRow +1), 'ВСЕГО часы');
        $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->getAlignment()->setWrapText(true);
        $sheet->getStyleByColumnAndRow($headColumn +1, ($headRow +1))->getAlignment()->setWrapText(true);
        $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow($headColumn, ($headRow +1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyleByColumnAndRow($headColumn +1, ($headRow +1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyleByColumnAndRow($headColumn +1, ($headRow +1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(($headRow +1))->setRowHeight(60);
        $sheet->setCellValueByColumnAndRow(1, ($headRow +2), 'Фамилия И.О');
        $sheet->getColumnDimensionByColumn(1)->setWidth(25);
//        $sheet->getColumnDimensionByColumn(2)->setWidth(14);
        $sheet->getStyleByColumnAndRow(1, ($headRow +2))->getFont()->setBold(700);
        
        // ============================================================= Формируем BODY
        $bodyRow = 7;
        foreach ($this->users as $userKey => $userValue){
            $userData = []; // Храним информацию о соответствии строк к room и event_type + общее кол-во часов/выходов
            $sheet->getStyleByColumnAndRow(1, $bodyRow)->applyFromArray(self::generateBorders(['left']));
            $sheet->getStyleByColumnAndRow(1, $bodyRow)->applyFromArray(self::generateBorders(['bottom'], 'medium'));
            $sheet->setCellValueByColumnAndRow(1, $bodyRow, $userValue['surname'] ." " .$userValue['name']);
            
            $sheet->getStyleByColumnAndRow(1, $bodyRow)->getAlignment()->setWrapText(true);
            $sheet->getStyleByColumnAndRow(2, $bodyRow)->applyFromArray(self::generateBorders(['left', 'bottom', 'right']));
            
            for($z = 2; $z <= ($countAllEvents +1); $z++){
                $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'right']));
                $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['bottom'], 'medium'));
                $sheet->getStyleByColumnAndRow($z, $bodyRow)->applyFromArray(self::generateBorders(['top', 'bottom', 'right']));
            }
            
            // Отмечаем в строку на каких мероприятиях стоит сотрудник
            foreach ($this->schedule['schedule'] as $day => $eventId){
                foreach ($eventId as $eventKey => $eventValue){
                    foreach ($eventValue as $keyEvent => $event){
                        if($event['allUsersInEvent']){
                            foreach ($event['allUsersInEvent'] as $usKey => $usVal){
                                if((int)$userValue['id'] == (int)$usVal['user_id']){
                                    if($event['event']){
                                        $sheet->getStyleByColumnAndRow($event['column'], $bodyRow)->getFill()
                                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                        ->getStartColor()->setARGB($this->colors[array_search($event['event']['id'], $eventsListIds)]);
                                    }
                                    
                                    // $sheet->setCellValueByColumnAndRow($event['column'], $bodyRow, '+');
                                    $sheet->getStyleByColumnAndRow($event['column'], $bodyRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                    $sheet->getStyleByColumnAndRow($event['column'], $bodyRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                    
                                }
                            }
                        }
                    }
                }
            }
            $bodyRow++;
        }
        $filename = "Pencil_(" .$this->profCatName['name'] .")_" .$this->month ."." .$this->year .".xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save('files/pencil/' .$filename);
        return \Yii::$app->response->sendFile('files/pencil/' .$filename);
        
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
    
}

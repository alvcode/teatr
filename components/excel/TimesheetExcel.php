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

class TimesheetExcel extends Model{
    
    /**
     * Генерация табеля в Excel 
     * @param string $from
     * @param string $to
     * @return 
     */
    public static function excelTimesheet($from, $to, $profId){
        
    }
   
}

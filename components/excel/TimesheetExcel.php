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

/**
 *
 * 
 */

class TimesheetExcel extends Model{
    
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
        $eventTypes = ['17' => 'Репетиция'];
        // end
        
        $explodeFrom = explode('-', $from);
        $explodeTo = explode('-', $to);
        
        $from = date('Y-m-d', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $to = date('Y-m-d', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        $dateFrom = date('d.m.Y', mktime(0, 0, 0, $explodeFrom[1], $explodeFrom[0], $explodeFrom[2]));
        $dateTo = date('d.m.Y', mktime(0, 0, 0, $explodeTo[1], $explodeTo[0], $explodeTo[2]));
        
        $schedule = ScheduleEvents::find()
            ->where(['between', 'date', $from, $to])
            ->andWhere(['or', ['room_id' => array_keys($roomIds)], ['event_type_id' => array_keys($eventTypes)]])
            ->with('eventType')->with('event')->with('profCat')->with('allUsersInEvent')->asArray()->all();
        
        $users = User::find()->select('user.id, user.name, user.surname')
                ->leftJoin('user_profession', 'user.id = user_profession.user_id')
                ->where(['user_profession.prof_id' => $profId, 'user.is_active' => 1])
                ->asArray()->all();
        
        /*
         * 
         * Выводим все мероприятия, записываем к каждому номер колонки. ОТСОРТИРОВАТЬ расписание, не забыть
         * Далее перебираем юзеров. Каждому выделяем $roomIds и $eventTypes строк
         * В береборе юзеров перебираем расписание. Если нашли юзера  в allUsersInEvent - подгружаем настройку табеля
         * Если соответствует- вписываем в требуемую строку и колонку часы или выход
         * В каждую строку ведем подсчет сколько вписали, в самом конце вбиваем сумму на каждую
         * Выплевываем
         */

echo \yii\helpers\VarDumper::dumpAsString($users, 10, true);
    }
   
}

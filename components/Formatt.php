<?php

namespace app\components;

use Yii;
use yii\base\Model;

/**
 *
 * 
 */

class Formatt extends Model{
    
    // Переписать метод
    public static function formDateToMysql($date, $delimeter){
        if($date == '' || !$date) return null;
        $dateExplode = explode($delimeter, $date);

        return $dateExplode[2] . "-" .$dateExplode[1] ."-" .$dateExplode[0];
    }
    
    // Переписать метод
    public static function dateMysqlToForm($date){
        if($date == '' || !$date) return false;
        $firstExplode = explode(' ', $date);
        $dateExplode = explode('-', $firstExplode[0]);
        $timeExplode = explode(':', $firstExplode[1]);

        return $dateExplode[2] . "." .$dateExplode[1] ."." .$dateExplode[0] ." " .$timeExplode[0] .":" .$timeExplode[1] .":" .$timeExplode[2];
    }
   
    public static function timeToMinute($time){
        if($time == '' || !$time) return false;
        $timeExplode = explode(':', $time);

        return intval($timeExplode[0] * 60) + intval($timeExplode[1]);
    }
    
    public static function minuteToTime($from, $to = false){
        $result = floor($from / 60) .":" .($from % 60 < 10?"0" .$from % 60:$from % 60);
        if($to){
            $result .= floor($to / 60) .":" .($to % 60 < 10?"0" .$to % 60:$to % 60);
        }
        return $result;
    }
   
}

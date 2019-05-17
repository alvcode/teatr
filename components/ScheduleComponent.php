<?php

namespace app\components;

use Yii;
use yii\base\Model;
use app\models\CastUnderstudy;

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
    
    public static function sortFirstLetter($arr, $param){
        $result = [];
        $alphabet = ['а', 'А', 'б', 'Б', 'в', 'В', 'г', 'Г', 'д', 'Д', 'е', 'Е', 'ё', 'Ё', 'ж', 'Ж', 'з', 'З', 'и', 'И', 'й', 'Й', 'к', 'К', 'л', 'Л', 'м', 'М', 'н', 'Н', 'о', 'О', 
            'п', 'П', 'р', 'Р', 'с', 'С', 'т', 'Т', 'у', 'У', 'ф', 'Ф', 'х', 'Х', 'ц', 'Ц', 'ч', 'Ч', 'ш', 'Ш', 'щ', 'Щ', 'ъ', 'Ъ', 'ы', 'Ы', 'ь', 'Ь', 'э', 'Э', 'ю', 'Ю', 'я', 'Я'];
        foreach($alphabet as $keyA => $valueA){
            foreach($arr as $keyArr => $valueArr){
                $first_letter = mb_substr($valueArr[$param],0,1);
                if($first_letter == $valueA){
                    $result[$valueA][] = $valueArr;
                }
            }
        }
        return $result;
    }
   
}

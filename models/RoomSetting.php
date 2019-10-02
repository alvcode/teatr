<?php

namespace app\models;

use Yii;
use app\models\Room;

/**
 * This is the model class for table "room_setting".
 *
 * @property int $id
 * @property string $date_from
 * @property string $date_to
 * @property int $room_id
 */
class RoomSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'room_id'], 'required'],
            [['date_from', 'date_to'], 'safe'],
            [['room_id'], 'integer'],
        ];
    }
    
    public static function getSetting($period){
        $startDate = date('Y-m-d', strtotime($period[0]['year'] ."-" .$period[0]['month'] ."-" .$period[0]['day']));
        $endDate = date('Y-m-d', strtotime($period[1]['year'] ."-" .$period[1]['month'] ."-" .$period[1]['day']));
        $result = [];
        $getSetting = self::find()->where(['date_from' => $startDate, 'date_to' => $endDate])->asArray()->all();
        if($getSetting){
            $result = \yii\helpers\ArrayHelper::getColumn($getSetting, 'room_id');
        }
        return $result;
    }
    
    /**
     * Устанавливает конфигурацию
     * @param array $period
     * @param array $roomIds
     */
    public static function setSetting($period, $roomIds){
        $startDate = date('Y-m-d', strtotime($period[0]['year'] ."-" .$period[0]['month'] ."-" .$period[0]['day']));
        $endDate = date('Y-m-d', strtotime($period[1]['year'] ."-" .$period[1]['month'] ."-" .$period[1]['day']));
        // Удаляем настройки, если они были для данной недели
        Yii::$app->db->createCommand()->delete('room_setting', ['date_from' => $startDate, 'date_to' => $endDate])->execute();
        foreach ($roomIds as $key => $value){
            Yii::$app->db->createCommand()->insert('room_setting', ['date_from' => $startDate, 'date_to' => $endDate, 'room_id' => $value])->execute();
        }
        return true;
    }
    
    /**
     * Проверка. Запрещаем удалять залы, если в них стоят мероприятия
     * @param array $period
     * @param array $roomIds
     */
    public static function checkSetting($period, $roomIds){
        $startDate = date('Y-m-d', strtotime($period[0]['year'] ."-" .$period[0]['month'] ."-" .$period[0]['day']));
        $endDate = date('Y-m-d', strtotime($period[1]['year'] ."-" .$period[1]['month'] ."-" .$period[1]['day']));
        $allRooms = \yii\helpers\ArrayHelper::getColumn(Room::find()->where(['is_active' => 1])->asArray()->all(), 'id');
        foreach ($allRooms as $key => $value){
            if(in_array($value, $roomIds)){
                unset($allRooms[$key]);
            }
        }
        if($allRooms){
            $schedule = ScheduleEvents::find()
                ->where(['between', 'date', $startDate, $endDate])
                ->andWhere(['room_id' => $allRooms])->asArray()->all();
            if($schedule){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_from' => 'Date From',
            'date_to' => 'Date To',
            'room_id' => 'Room ID',
        ];
    }
}

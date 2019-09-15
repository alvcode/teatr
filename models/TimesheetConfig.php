<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "timesheet_config".
 *
 * @property int $id
 * @property int $user_id
 * @property int $event_type_id
 * @property int $method
 */
class TimesheetConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'timesheet_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'event_type_id', 'method'], 'required'],
            [['user_id', 'event_type_id', 'method'], 'integer'],
        ];
    }
    
    /**
     * Проверка подготовленных данных для настройки на повторы
     * Формат данных: [['eventType' => 5, 'method' => 1], ['eventType' => 14, 'method' => 2]]
     * 
     * @param array $data
     * @return boolean [true - все хорошо, false - повторы]
     */
    public static function checkRepeat($data){
        if($data){
            $uniqueTimesheet = array_unique(\yii\helpers\ArrayHelper::getColumn($data, 'eventType'));
            if(count($uniqueTimesheet) < count($data)){
                return false;
            }
        }
        return true;
    }
    
    /**
     * Устанавливает конфигурацию
     * Формат данных: [['eventType' => 5, 'method' => 1], ['eventType' => 14, 'method' => 2]]
     * 
     * @param array $data
     * @param integer $userId
     * @return boolean
     */
    public static function setConfig($data, $userId){
        Yii::$app->db->createCommand()->delete('timesheet_config', ['user_id' => $userId])->execute();
        if($data){
            foreach ($data as $key => $value){
                Yii::$app->db->createCommand()->insert('timesheet_config', [
                    'user_id' => $userId,
                    'event_type_id' => $value['eventType'],
                    'method' => $value['method']
                ])->execute();
            }
        }
        return true;
    }
    
    public function getEventType()
    {
        return $this->hasOne(EventType::className(), ['id' => 'event_type_id']);
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->select('user.id, user.name, user.surname');
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'event_type_id' => 'Event Type ID',
            'method' => 'Method',
        ];
    }
}

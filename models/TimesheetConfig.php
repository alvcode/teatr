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

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "schedule_events".
 *
 * @property int $id
 * @property int $event_type_id
 * @property int $event_id
 * @property int $room_id
 * @property string $date
 * @property int $time_from
 * @property int $time_to
 * @property int $is_modified
 * @property int $is_all
 */
class ScheduleEvents extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'schedule_events';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_type_id', 'date', 'time_from', 'room_id'], 'required'],
            [['event_type_id', 'event_id', 'time_from', 'time_to', 'room_id', 'is_modified', 'is_all'], 'integer'],
            [['add_info'], 'string', 'max' => 1000],
            [['date'], 'safe'],
        ];
    }
    
    
    public function getEventType()
    {
        return $this->hasOne(EventType::className(), ['id' => 'event_type_id']);
    }
    
    public function getEvent()
    {
        return $this->hasOne(Events::className(), ['id' => 'event_id']);
    }
    
    public function getProfCat()
    {
        return $this->hasMany(ProfCatInSchedule::className(), ['schedule_id' => 'id'])->with('profCat');
    }
    
    public function getAllUsersInEvent()
    {
        return $this->hasMany(UserInSchedule::className(), ['schedule_event_id' => 'id'])->with('userWithProf');
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_type_id' => 'Event Type ID',
            'event_id' => 'Event ID',
            'date' => 'Date',
            'time_from' => 'Time From',
            'time_to' => 'Time To',
        ];
    }
}

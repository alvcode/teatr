<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "schedule_events".
 *
 * @property int $id
 * @property int $event_type_id
 * @property int $event_id
 * @property string $date
 * @property int $time_from
 * @property int $time_to
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
            [['event_type_id', 'event_id', 'date', 'time_from'], 'required'],
            [['event_type_id', 'event_id', 'time_from', 'time_to'], 'integer'],
            [['date'], 'safe'],
        ];
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

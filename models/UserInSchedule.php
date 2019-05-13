<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_in_schedule".
 *
 * @property int $id
 * @property int $schedule_event_id
 * @property int $user_id
 */
class UserInSchedule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_in_schedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['schedule_event_id', 'user_id'], 'required'],
            [['schedule_event_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'schedule_event_id' => 'Schedule Event ID',
            'user_id' => 'User ID',
        ];
    }
}

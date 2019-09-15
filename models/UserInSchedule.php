<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_in_schedule".
 *
 * @property int $id
 * @property int $schedule_event_id
 * @property int $user_id
 * @property int $cast_id
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
            [['schedule_event_id', 'user_id', 'cast_id'], 'required'],
            [['schedule_event_id', 'user_id', 'cast_id'], 'integer'],
        ];
    }
    
    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id'])->select('user.id, user.name, user.surname');
    }
    
    public function getUserWithProf(){
        return $this->hasOne(User::className(), ['id' => 'user_id'])->select('user.id, user.name, user.surname, user.show_full_name')->with('userProfession');
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

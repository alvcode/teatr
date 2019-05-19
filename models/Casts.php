<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "casts".
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property int $month
 * @property int $year
 */
class Casts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'casts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'user_id', 'month', 'year'], 'required'],
            [['event_id', 'user_id', 'month', 'year'], 'integer'],
        ];
    }
    
    public function getUnderstudy(){
        return $this->hasMany(CastUnderstudy::className(), ['cast_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_id' => 'Event ID',
            'user_id' => 'User ID',
        ];
    }
}

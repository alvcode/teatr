<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "schedule_view_hash".
 *
 * @property int $id
 * @property string $date_from
 * @property string $date_to
 * @property string $hash
 */
class ScheduleViewHash extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'schedule_view_hash';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to', 'hash'], 'required'],
            [['date_from', 'date_to'], 'safe'],
            [['hash'], 'string', 'max' => 45],
        ];
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
            'hash' => 'Hash',
        ];
    }
}

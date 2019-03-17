<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "event_type".
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property int $timesheet_hour
 * @property int $timesheet_count
 */
class EventType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'is_active', 'timesheet_hour', 'timesheet_count'], 'required'],
            [['is_active', 'timesheet_hour', 'timesheet_count'], 'integer'],
            [['name'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'timesheet_hour' => 'Участвует в расчете табелей по часам',
            'timesheet_count' => 'Участвует в расчете табелей по выходам',
        ];
    }
}

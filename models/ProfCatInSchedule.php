<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prof_cat_in_schedule".
 *
 * @property int $id
 * @property int $prof_cat_id
 * @property int $schedule_id
 */
class ProfCatInSchedule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'prof_cat_in_schedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['prof_cat_id', 'schedule_id'], 'required'],
            [['prof_cat_id', 'schedule_id'], 'integer'],
        ];
    }
    
    public function getProfCat()
    {
        return $this->hasOne(ProffCategories::className(), ['id' => 'prof_cat_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prof_cat_id' => 'Prof Cat ID',
            'schedule_id' => 'Schedule ID',
        ];
    }
}

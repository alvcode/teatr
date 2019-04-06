<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "event_categories".
 *
 * @property int $id
 * @property string $name
 */
class EventCategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 45],
        ];
    }
    
    public function getEvents()
    {
        return $this->hasMany(Events::className(), ['category_id' => 'id'])->where(['is_active' => '1']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }
}

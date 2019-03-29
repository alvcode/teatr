<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "events".
 *
 * @property int $id
 * @property string $name
 * @property string $other_name
 * @property int $is_active
 * @property int $category_id
 */
class Events extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'events';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'is_active', 'category_id'], 'required'],
            [['is_active', 'category_id'], 'integer'],
            [['name', 'other_name'], 'string', 'max' => 65],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'other_name' => 'Дополнительное название',
            'is_active' => 'Is Active',
            'category_id' => 'Категория спектакля',
        ];
    }
}

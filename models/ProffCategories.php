<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "proff_categories".
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 *
 * @property Profession[] $professions
 */
class ProffCategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'proff_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'alias'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['alias'], 'string', 'max' => 25],
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
            'alias' => 'Сокращенное название'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfessions()
    {
        return $this->hasMany(Profession::className(), ['proff_cat_id' => 'id']);
    }
}

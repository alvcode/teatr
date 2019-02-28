<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "profession".
 *
 * @property int $id
 * @property string $name
 * @property int $proff_cat_id
 *
 * @property ProffCategories $proffCat
 * @property UserProffesion[] $userProffesions
 */
class Profession extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profession';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'proff_cat_id'], 'required'],
            [['proff_cat_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['proff_cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProffCategories::className(), 'targetAttribute' => ['proff_cat_id' => 'id']],
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
            'proff_cat_id' => 'Proff Cat ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProffCat()
    {
        return $this->hasOne(ProffCategories::className(), ['id' => 'proff_cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProffesions()
    {
        return $this->hasMany(UserProffesion::className(), ['prof_id' => 'id']);
    }
}

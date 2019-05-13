<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cast_understudy".
 *
 * @property int $id
 * @property int $cast_id
 * @property int $user_id
 */
class CastUnderstudy extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cast_understudy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cast_id', 'user_id'], 'required'],
            [['cast_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cast_id' => 'Cast ID',
            'user_id' => 'User ID',
        ];
    }
}

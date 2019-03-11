<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_proffesion".
 *
 * @property int $id
 * @property int $user_id
 * @property int $prof_id
 *
 * @property Profession $prof
 */
class UserProfession extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_profession';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'prof_id'], 'required'],
            [['user_id', 'prof_id'], 'integer'],
            [['prof_id'], 'exist', 'skipOnError' => true, 'targetClass' => Profession::className(), 'targetAttribute' => ['prof_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'prof_id' => 'Prof ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProf()
    {
        return $this->hasOne(Profession::className(), ['id' => 'prof_id']);
    }
}

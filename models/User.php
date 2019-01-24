<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $surname
 * @property string $password_hash
 * @property string $auth_key
 * @property string $access_token
 * @property string $date_register
 * @property string $last_login
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    
    public $password;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'name', 'surname', 'password'], 'required'],
            [['date_register', 'last_login'], 'datetime'],
            [['email'], 'email'],
            [['name', 'surname', 'password_hash', 'auth_key', 'access_token'], 'string', 'max' => 255],
        ];
    }
    
    
    public function last_login(){
        Yii::$app->db->createCommand()->update('user', ['last_login' => date('Y-m-d H:i:s')], [
                    'id' => $this->id,
                ])->execute();
    }
    
    
    public function beforeSave($insert) {
        if (!parent::beforeSave($insert)){
            return false;
        }
        if ($this->password) {
            $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($this->password);

            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->getSecurity()->generateRandomString();
            }
            $this->date_register = date('Y-m-d H:i:s');
            return true;
        }

        return false;
    }
    
    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    
    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
    
    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }
    
    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
       return static::findOne(['email' => $email]);
    }
    

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'E-mail',
            'name' => 'Имя пользователя',
            'surname' => 'Фамилия пользователя',
            'password' => 'Пароль',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'date_register' => 'Дата регистрации',
        ];
    }
}

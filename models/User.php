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
 * @property int $number
 * @property string $password_hash
 * @property string $auth_key
 * @property string $access_token
 * @property int $show_full_name
 * @property string $date_register
 * @property string $last_login
 * @property int $is_active
 * @property int $modified_password
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    
    public $password;
    public $user_role;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }
    
    
    public function init(){
        parent::init();
        Yii::$app->user->on(Yii\web\User::EVENT_AFTER_LOGIN, [$this, 'statistics']);
    }

    public function statistics(){
        Yii::$app->db->createCommand()->update('user', ['last_login' => Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s')], [
                    'id' => $this->id,
                ])->execute();
    }
    

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'name', 'surname', 'show_full_name'], 'required'],
            [['password'], 'trim'],
            [['modified_password'], 'boolean'],
//            [['date_register', 'last_login'], 'datetime'],
            [['email'], 'email'],
            [['name', 'surname', 'password_hash', 'auth_key', 'access_token'], 'string', 'max' => 255],
            [['number', 'password', 'user_role'], 'string', 'max' => 32]
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
                 $this->date_register = date('Y-m-d H:i:s');
                 $this->is_active = 1;
                 $this->modified_password = 0;
            }
        }
        return true;
//        return false;
    }
    
    public function loginAs()
    {
        return Yii::$app->user->login($this, 0);
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
    
    
    public function getRole(){
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id']);
    }
    
    public function getUserProfession(){
        return $this->hasOne(UserProfession::className(), ['user_id' => 'id'])->with('prof');
    }
    public function getUserProfessionJoinProf(){
        return $this->hasOne(UserProfession::className(), ['user_id' => 'id'])->joinWith('prof');
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
            'number' => 'Номер телефона',
            'password' => 'Пароль',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'date_register' => 'Дата регистрации',
            'show_full_name' => 'Отображать полное имя сотрудника'
        ];
    }
}

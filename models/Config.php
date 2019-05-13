<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "config".
 *
 * @property int $id
 * @property string $name_config
 * @property array $value
 */
class Config extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_config'], 'required'],
            [['value'], 'safe'],
            [['name_config'], 'string', 'max' => 65],
        ];
    }
    
    /**
     * Устанавливает конфиг
     * 
     * @param string $configName
     * @param array $value
     * @return boolean
     */
    public static function setConfig($configName, $value){
        $findConfig = self::find()->where(['name_config' => $configName])->one();
        if(!$findConfig){
            $thisObj = new self();
            $thisObj->name_config = $configName;
            $thisObj->value = json_encode($value, JSON_FORCE_OBJECT);
            if($thisObj->save()){
                return true;
            }
        }else{
            $configArr = json_decode($findConfig->value, true);
            $configNewArr = $value;

            foreach ($configNewArr as $key => $valueL){
                if(in_array($valueL, $configArr)){
                    unset($configNewArr[$key]);
                }
            }
            $findConfig->value = json_encode(array_merge($configArr, $configNewArr), JSON_FORCE_OBJECT);
            if($findConfig->validate() && $findConfig->save()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Удаляем значение из списка конфигурации 
     * @param string $configName
     * @param string $value
     * @return boolean
     */
    public static function removeConfig($configName, $value){
        $findConfig = self::find()->where(['name_config' => $configName])->one();
        if(!$findConfig){
            return true;
        }else{
            $configArr = json_decode($findConfig->value, true);

            foreach ($configArr as $key => $valueL){
                if($value == $valueL){
                    unset($configArr[$key]);
                }
            }
            $findConfig->value = json_encode($configArr, JSON_FORCE_OBJECT);
            if($findConfig->validate() && $findConfig->save()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Получаем массив с конфигурациями
     * 
     * @param string $configName
     * @return mixed
     */
    public static function getConfig($configName){
        $findConfig = self::find()->where(['name_config' => $configName])->one();
        if(!$findConfig){
            return false;
        }else{
            $configArr = json_decode($findConfig->value, true);
            if(!$configArr) return false;
            return $configArr;
        }
    }
    

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_config' => 'Name Config',
            'value' => 'Value',
        ];
    }
}

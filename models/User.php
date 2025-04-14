<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $fio
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $access_permission
 *
 * @property GroupCount[] $groupCounts
 * @property Product[] $products
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{

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
            [['fio', 'login', 'email', 'password'], 'required'],
            [['access_permission'], 'integer'],
            [['fio'], 'string' , 'max' => 255],
            [['login'], 'string', 'max' => 60],
            [['email'],'string', 'max' => 55],
            [['email'],'unique'],
            [['email'],'email'],
            [['password'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fio' => 'ФИО',
            'login' => 'Логин',
            'email' => 'Email',
            'password' => 'Пароль',
            'access_permission' => 'Права доступа',
        ];
    }
    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|User|null
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
        return null;
        //return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null current user auth key
     */
    public function getAuthKey()
    {
        return null;
        //return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool|null if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return false;
        //return $this->getAuthKey() === $authKey;
    }


    /**
     * Gets query for [[GroupCounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupCounts()
    {
        return $this->hasMany(GroupCount::className(), ['idUser' => 'id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['idUser' => 'id']);
    }

    public static function findByUsername($email)
    {
        return User::findOne(['email' => $email]);
    }

    public function validatePassword($password)
    {
        return $this->password == md5($password);
    }
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->password = md5($this->password);
        }
        return parent::beforeSave($insert);
    }
    /**
     * @return bool if auth key is valid for current user
     */
    public function isAdmin(){
        return $this->access_permission == 1;
    }
    /**
     * @return bool if auth key is valid for current user
     */
    public function isModeration(){
        return $this->access_permission == 2;
    }
    /**
     * @return bool if auth key is valid for current user
     */
    public function isUser(){
        return $this->access_permission == 0;
    }

    /**
     * Returns the label for access_permission.
     *
     * @return string
     */
    public function getRoleLabel()
    {
        switch ($this->access_permission) {
            case 0:
                return 'Пользователь';
            case 1:
                return 'Администратор';
            case 2:
                return 'Модератор';
            default:
                return 'Неизвестно';
        }
    }

}

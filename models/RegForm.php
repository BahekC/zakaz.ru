<?php

namespace app\models;

use Yii;

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
class RegForm extends User
{
    public $passwordConfirm;
    public $agree;

    public function rules()
    {
        return [
            [['fio', 'login', 'email', 'password','passwordConfirm','agree'], 'required','message'=>'Поле обязательно для заполнение'],
            ['fio','match','pattern'=>'/^[А-Яа-я\s\-]{5,}$/u','message'=>'Только кириллица, пробелы и дефисы'],
            ['login', 'match', 'pattern'=>'/^[A-Za-z0-9]{5,}$/u','message'=>'Только латинские буквы и цифры'],
            ['login','unique','message'=>'Пользователь с таким логином уже существует'],
            ['email','unique','message'=>'Такая почта уже зарегистрирована'],
            //[['access_permission'], 'integer'],
            [['fio'], 'string' , 'max' => 255,'message'=>'Превышение максимального количества символов'],
            [['login'], 'string', 'max' => 60,'message'=>'Превышение максимального количества символов'],
            [['email'],'string', 'max' => 55,'message'=>'Превышение максимального количества символов'],
            [['email'],'email', 'message'=>'Должен быть введен адрес электронной почты'],
            [['password'],'string','min' => 8, 'tooShort'=>'Минимальный размер пароля 8 символов'],
            ['passwordConfirm','compare','compareAttribute'=>'password','message'=>'Пароли должны совпадать'],
            ['agree','boolean'],
            ['agree','compare','compareValue'=>true,'message'=>'Необходимо принять соглашение']
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
            'passwordConfirm' => 'Повтор пароля',
            'access_permission' => 'Права доступа',
            'agree'=>'Согласие на обработку персональных данных'

        ];
    }

}

<?php

namespace app\models;

use yii\base\Model;

/**
 * Модель формы логина для REST API (телефон + пароль).
 */
class ApiLoginForm extends Model
{
    public $phone;
    public $password;

    public function rules(): array
    {
        return [
            [['phone', 'password'], 'required'],
            ['phone', 'string', 'max' => 15],
            ['password', 'string', 'min' => 6],
        ];
    }
}



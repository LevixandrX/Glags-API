<?php

namespace app\models;

use yii\db\ActiveRecord;

class PasswordReset extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'password_resets';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'token', 'expires_at'], 'required'],
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['expires_at', 'created_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'token' => 'Токен',
            'expires_at' => 'Истекает',
            'created_at' => 'Создано',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}


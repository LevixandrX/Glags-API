<?php

namespace app\models;

use yii\db\ActiveRecord;

class Order extends ActiveRecord
{
    private const STATUSES = ['new', 'confirmed', 'declined', 'cancelled'];

    public static function tableName(): string
    {
        return 'orders';
    }

    public function rules(): array
    {
        return [
            [['total_sum', 'delivery_address'], 'required'],
            [['user_id'], 'integer'],
            [['comment'], 'string'],
            [['total_sum'], 'number', 'min' => 0],
            [['guest_first_name', 'guest_last_name'], 'string', 'max' => 50],
            [['guest_email'], 'email'],
            [['guest_email'], 'string', 'max' => 255],
            [['guest_phone'], 'string', 'max' => 15],
            [['delivery_address'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => self::STATUSES],
            [['status'], 'default', 'value' => 'new'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'guest_first_name' => 'Имя гостя',
            'guest_last_name' => 'Фамилия гостя',
            'guest_email' => 'Email гостя',
            'guest_phone' => 'Телефон гостя',
            'status' => 'Статус',
            'total_sum' => 'Сумма заказа',
            'delivery_address' => 'Адрес доставки',
            'comment' => 'Комментарий',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }
}


<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProductImage extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'product_images';
    }

    public function rules(): array
    {
        return [
            [['product_id', 'image_url'], 'required'],
            [['product_id'], 'integer'],
            [['image_url'], 'string', 'max' => 255],
            [['image_url'], 'url'],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'product_id' => 'Товар',
            'image_url' => 'Ссылка на изображение',
            'created_at' => 'Создано',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}


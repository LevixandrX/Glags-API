<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Поле для ввода пароля в чистом виде (не хранится в БД).
     * В таблице `users` хэш хранится в колонке `password`.
     */
    public $password_plain;

    public static function tableName(): string
    {
        return 'users';
    }

    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'email', 'phone'], 'required'],
            [['first_name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 15],
            [['avatar_url'], 'string', 'max' => 255],
            [['role'], 'in', 'range' => ['user', 'admin']],
            [['role'], 'default', 'value' => 'user'],
            [['password_plain'], 'string', 'min' => 6],
            [
                ['password_plain'],
                'required',
                'when' => static fn(self $model) => $model->isNewRecord,
                'whenClient' => null,
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['email'], 'unique'],
            [['phone'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'email' => 'Email',
            'phone' => 'Телефон',
            'password_plain' => 'Пароль',
            'password' => 'Хэш пароля',
            'avatar_url' => 'Аватар',
            'role' => 'Роль',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['password_hash']);

        $fields['full_name'] = static fn(self $model) => trim(
            $model->first_name . ' ' . $model->last_name
        );

        return $fields;
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return static::findOne(['auth_token' => $token]);
    }

    public static function findByUsername(string $username): ?self
    {
        return static::find()
            ->andWhere(['or', ['email' => $username], ['phone' => $username]])
            ->one();
    }

    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email]);
    }

    public function getId(): int|string|null
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return true;
    }

    public function validatePassword(string $password): bool
    {
        // В БД колонка называется `password` и хранит хэш
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setPassword(string $password): void
    {
        // Сохраняем хэш в колонку `password`
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!empty($this->password_plain)) {
            $this->setPassword($this->password_plain);
        }

        return true;
    }

    public function getCartItems()
    {
        return $this->hasMany(CartItem::class, ['user_id' => 'id']);
    }

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    public function getPasswordResets()
    {
        return $this->hasMany(PasswordReset::class, ['user_id' => 'id']);
    }
}

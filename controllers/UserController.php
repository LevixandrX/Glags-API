<?php

namespace app\controllers;

use app\models\ApiLoginForm;
use app\models\User;
use Yii;

class UserController extends FunctionController
{
    public $modelClass = User::class;

    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['create']);

        return $actions;
    }

    /**
     * Регистрация пользователя.
     * Ожидает JSON с полями first_name, last_name, email, phone, password.
     */
    public function actionRegistration()
    {
        $data = Yii::$app->request->getBodyParams();

        $user = new User();
        // Загружаем основные поля как есть
        $user->load($data, '');

        // Отдельно маппим пароль в сервисное поле password_plain
        if (isset($data['password'])) {
            $user->password_plain = $data['password'];
        }

        if (!$user->validate()) {
            return $this->validation($user);
        }

        if (!$user->save()) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось создать пользователя',
                ],
            ]);
        }

        // Формат ответа согласно ТЗ
        return $this->send(201, [
            'message' => 'User registered',
            'user_id' => $user->id,
            'code' => 201,
        ]);
    }

    public function actionCreate()
    {
        $user = new User();
        $user->load(Yii::$app->request->getBodyParams(), '');

        if (!$user->validate()) {
            return $this->validation($user);
        }

        if (!$user->save()) {
            return $this->send(500, ['error' => 'Не удалось создать пользователя']);
        }

        return $this->send(201, $user);
    }

    public function actionLogin()
    {
        $data = Yii::$app->request->getBodyParams();
        
        if (empty($data['email']) || empty($data['password'])) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [
                        'email' => empty($data['email']) ? ['Email обязателен'] : [],
                        'password' => empty($data['password']) ? ['Пароль обязателен'] : [],
                    ],
                ],
            ]);
        }

        $user = User::findByEmail($data['email']);

        // Проверяем пароль через модель (использует колонку password из БД)
        if ($user !== null && $user->validatePassword($data['password'])) {
            $token = Yii::$app->security->generateRandomString(64);
            $user->auth_token = $token;
            $user->save(false);

            return $this->send(200, [
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ],
                    'token' => $token,
                ],
            ]);
        }

        return $this->send(401, [
            'error' => [
                'code' => 401,
                'message' => 'Validation error',
                'errors' => [
                    'password' => ['Неверный пароль'],
                ],
            ],
        ]);
    }
}


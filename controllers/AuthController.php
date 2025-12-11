<?php

namespace app\controllers;

use app\components\BearerAuth;
use app\models\User;
use Yii;
use yii\web\Response;

class AuthController extends FunctionController
{
    // Устанавливаем modelClass для ActiveController (хотя не используем стандартные действия)
    public $modelClass = User::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        
        // Logout доступен опционально (чтобы гость тоже мог очистить гостевую корзину)
        $behaviors['authenticator'] = [
            'class' => BearerAuth::class,
            'optional' => ['logout'],
        ];
        
        return $behaviors;
    }

    public function actions(): array
    {
        // Отключаем все стандартные действия ActiveController
        return [];
    }

    /**
     * Регистрация пользователя
     * POST /api/registration
     */
    public function actionRegistration(): Response
    {
        $data = Yii::$app->request->getBodyParams();
        $user = new User();
        $user->scenario = 'registration';
        $user->load($data, '');

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

        return $this->send(201, [
            'message' => 'User registered',
            'user_id' => $user->id,
            'code' => 201,
        ]);
    }

    /**
     * Авторизация пользователя
     * POST /api/authorization
     */
    public function actionAuthorization(): Response
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

        if ($user === null || !$user->validatePassword($data['password'])) {
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

    /**
     * Выход из системы
     * POST /api/logout
     */
    public function actionLogout(): Response
    {
        $user = Yii::$app->user->identity;

        if ($user) {
            $user->auth_token = null;
            $user->save(false);
        }

        // Очищаем гостевую корзину в любом случае
        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        $session->remove('guest_cart');

        return $this->send(200, [
            'message' => 'Вы успешно вышли из системы',
            'user_logged_out' => $user !== null,
            'guest_cart_cleared' => true,
        ]);
    }
}


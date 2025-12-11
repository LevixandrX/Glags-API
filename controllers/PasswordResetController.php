<?php

namespace app\controllers;

use app\models\PasswordReset;
use app\components\BearerAuth;
use Yii;
use yii\web\Response;

class PasswordResetController extends FunctionController
{
    public $modelClass = PasswordReset::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BearerAuth::class,
        ];
        return $behaviors;
    }

    public function actions(): array
    {
        return [];
    }

    /**
     * Сброс пароля авторизованного пользователя
     * POST /api/password/reset
     * Body:
     * {
     *   "old_password": "...",
     *   "new_password": "...",
     *   "new_password_confirm": "..."
     * }
     */
    public function actionReset(): Response
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->send(403, [
                'message' => 'Login failed',
            ]);
        }

        $body = Yii::$app->request->getBodyParams();
        $old = $body['old_password'] ?? '';
        $new = $body['new_password'] ?? '';
        $newConfirm = $body['new_password_confirm'] ?? '';

        $errors = [];
        if (empty($old) || !$user->validatePassword($old)) {
            $errors['old_password'][] = 'Текущий пароль неверен';
        }
        if (empty($new) || strlen($new) < 8) {
            $errors['new_password'][] = 'Новый пароль должен быть не короче 8 символов';
        }
        if ($new !== $newConfirm) {
            $errors['new_password_confirm'][] = 'Пароли не совпадают';
        }

        if (!empty($errors)) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $errors,
                ],
            ]);
        }

        $user->setPassword($new);
        if (!$user->save(false)) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось сохранить новый пароль',
                ],
            ]);
        }

        return $this->send(200, [
            'message' => 'Пароль успешно обновлён',
        ]);
    }
}


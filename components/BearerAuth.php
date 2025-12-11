<?php

namespace app\components;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;

class BearerAuth extends HttpBearerAuth
{
    /**
     * Обработка ошибки аутентификации
     * Вызывается когда токен отсутствует, невалиден или пользователь не найден
     */
    public function handleFailure($response)
    {
        $response->format = Response::FORMAT_JSON;
        $response->statusCode = 403;
        $response->data = [
            'message' => 'Login failed',
        ];
    }
}


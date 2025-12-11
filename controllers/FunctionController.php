<?php

namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\widgets\ActiveForm;

class FunctionController extends ActiveController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        if (isset($behaviors['contentNegotiator'])) {
            $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        }

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        return $behaviors;
    }

    protected function send(int $code, $data = null): Response
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->statusCode = $code;
        $response->data = $data;

        return $response;
    }

    protected function validation($model): Response
    {
        return $this->send(422, [
            'error' => [
                'code' => 422,
                'message' => 'Validation error',
                'errors' => ActiveForm::validate($model),
            ],
        ]);
    }
}


<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Glags-API',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Alexandrrrrr12345',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => [
                'httpOnly' => true,
                'secure' => false, // Включить true для HTTPS
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'response' => [
            'class' => yii\web\Response::class,
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $response->format = yii\web\Response::FORMAT_JSON;

                // Авторизация/доступ согласно общим требованиям
                if ($response->statusCode == 401 && !isset($response->data['error'])) {
                    // Неверный логин/пароль (только если ответ не содержит структуру error)
                    $response->data = [
                        'message' => 'Login failed',
                        'code' => 401,
                    ];
                } elseif ($response->statusCode == 403 && !isset($response->data['message'])) {
                    // Доступ запрещён (гость или нет прав)
                    $response->data = [
                        'message' => 'Forbidden for you',
                        'code' => 403,
                    ];
                } elseif ($response->statusCode == 404) {
                    // Единый формат "ресурс не найден" из примера REST API
                    $response->data = [
                        'message' => 'Not found',
                        'code' => 404,
                    ];
                }
            },
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

            'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                // ВНИМАНИЕ: приложение уже размещено под /api,
                // поэтому в правилах НЕ дублируем сегмент "api"

                // Регистрация / авторизация (REST API)
                'POST registration'  => 'user/registration',
                'POST authorization' => 'user/login',
                'POST logout' => 'auth/logout',

                // Товары: /api/products, /api/products/1, и т.п.
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'product',
                    'patterns' => [
                        'GET' => 'index',
                        'GET {id}' => 'view',
                        'POST' => 'create',
                        'PUT {id}' => 'update',
                        'PATCH {id}' => 'update',
                        'DELETE {id}' => 'delete',
                    ],
                ],

                // Корзина: /api/cart, /api/cart/1
                'POST cart' => 'cart/add',
                'GET cart' => 'cart/index',
                'DELETE cart/<id:\d+>' => 'cart/delete',

                // Заказы: /api/orders
                'POST orders' => 'order/create',
                'GET orders' => 'order/index',

                // Профиль
                'GET profile' => 'profile/index',
                'PUT profile' => 'profile/update',
                'PATCH profile' => 'profile/update',
                'POST profile/avatar' => 'profile/avatar',

                // Админ
                'GET admin/orders' => 'admin/orders',
                'PATCH admin/orders/<id:\d+>' => 'admin/update-order-status',
                'GET admin/users' => 'admin/users',

                // Сброс пароля
                'POST password/reset' => 'password-reset/reset',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;

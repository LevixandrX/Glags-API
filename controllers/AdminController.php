<?php

namespace app\controllers;

use app\components\BearerAuth;
use app\models\Order;
use app\models\User;
use Yii;
use yii\web\Response;

class AdminController extends FunctionController
{
    // Устанавливаем modelClass для ActiveController (хотя не используем стандартные действия)
    public $modelClass = Order::class;

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
        // Отключаем все стандартные действия ActiveController
        return [];
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $user = Yii::$app->user->identity;
        if (!$user || $user->role !== 'admin') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = 403;
            Yii::$app->response->data = [
                'message' => 'Forbidden for you',
            ];
            return false;
        }

        return true;
    }

    /**
     * Получение списка всех заказов
     * GET /api/admin/orders
     */
    public function actionOrders(): Response
    {
        $orders = Order::find()
            ->with(['user', 'items.product'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $data = [];
        foreach ($orders as $order) {
            $userName = $order->user 
                ? $order->user->first_name . ' ' . $order->user->last_name
                : ($order->guest_first_name . ' ' . $order->guest_last_name);
            
            $data[] = [
                'id' => $order->id,
                'user_name' => $userName,
                'total_sum' => $order->total_sum,
                'status' => $order->status,
                'created_at' => $order->created_at,
            ];
        }

        return $this->send(200, [
            'data' => $data,
        ]);
    }

    /**
     * Получение списка всех пользователей
     * GET /api/admin/users
     */
    public function actionUsers(): Response
    {
        $users = User::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ];
        }

        return $this->send(200, [
            'data' => $data,
        ]);
    }

    /**
     * Изменение статуса заказа
     * PATCH /api/admin/orders/{id}
     */
    public function actionUpdateOrderStatus($id): Response
    {
        $order = Order::findOne($id);
        
        if ($order === null) {
            return $this->send(404, [
                'message' => 'Not found',
                'code' => 404,
            ]);
        }

        $data = Yii::$app->request->getBodyParams();
        $status = $data['status'] ?? null;

        $allowedStatuses = ['new', 'confirmed', 'declined', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [
                        'status' => ['Недопустимый статус'],
                    ],
                ],
            ]);
        }

        $order->status = $status;
        if (!$order->save(false)) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось обновить статус заказа',
                ],
            ]);
        }

        return $this->send(200, [
            'message' => 'Статус заказа изменён на ' . $status,
        ]);
    }
}


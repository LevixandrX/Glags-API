<?php

namespace app\controllers;

use app\components\BearerAuth;
use app\models\CartItem;
use app\models\Order;
use app\models\OrderItem;
use app\models\Product;
use app\models\User;
use Yii;
use yii\web\Response;

class OrderController extends FunctionController
{
    public $modelClass = Order::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        // Создание заказа доступно и гостям, и авторизованным пользователям
        // Просмотр заказов требует авторизации
        $behaviors['authenticator'] = [
            'class' => BearerAuth::class,
            'only' => ['index'],
            'optional' => ['create'],
        ];
        return $behaviors;
    }

    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['index']);
        return $actions;
    }

    /**
     * Список заказов текущего пользователя
     * GET /api/orders
     */
    public function actionIndex(): Response
    {
        $query = Order::find()->with(['items.product', 'user']);
        $request = Yii::$app->request;
        $user = Yii::$app->user->identity;

        // Обычные пользователи видят только свои заказы (игнорируем user_id в запросе)
        if ($user && $user->role !== 'admin') {
            $query->andWhere(['user_id' => $user->id]);
        } elseif ($user && $user->role === 'admin' && $request->get('user_id')) {
            // Админы могут фильтровать по user_id
            $query->andWhere(['user_id' => (int)$request->get('user_id')]);
        }

        if ($request->get('status')) {
            $query->andWhere(['status' => $request->get('status')]);
        }

        $orders = $query->orderBy(['created_at' => SORT_DESC])->all();

        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'status' => $order->status,
                'total_sum' => $order->total_sum,
                'delivery_address' => $order->delivery_address,
                'comment' => $order->comment,
                'created_at' => $order->created_at,
            ];
        }

        return $this->send(200, [
            'data' => $data,
        ]);
    }


    /**
     * Создание заказа из корзины текущего пользователя или гостя
     * POST /api/orders
     * 
     * Для гостей требуется передать в body:
     * {
     *   "delivery_address": "...",
     *   "comment": "...",
     *   "guest_first_name": "...",
     *   "guest_last_name": "...",
     *   "guest_email": "...",
     *   "guest_phone": "..."
     * }
     */
    public function actionCreate(): Response
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        $body = Yii::$app->request->getBodyParams();

        $cartItems = [];
        $totalSum = 0;

        if ($user !== null) {
            // Зарегистрированный пользователь - корзина из БД
            $dbCartItems = CartItem::find()
                ->with(['product'])
                ->andWhere(['user_id' => $user->id])
                ->all();

            if (empty($dbCartItems)) {
                return $this->send(422, [
                    'error' => [
                        'code' => 422,
                        'message' => 'Validation error',
                        'errors' => [
                            'cart' => ['Корзина пуста'],
                        ],
                    ],
                ]);
            }

            foreach ($dbCartItems as $item) {
                $totalSum += $item->quantity * $item->product->price;
                $cartItems[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ];
            }
        } else {
            // Гость - корзина из сессии
            $session = Yii::$app->session;
            if (!$session->isActive) {
                $session->open();
            }
            
            $guestCart = $session->get('guest_cart', []);
            
            if (empty($guestCart)) {
                return $this->send(422, [
                    'error' => [
                        'code' => 422,
                        'message' => 'Validation error',
                        'errors' => [
                            'cart' => ['Корзина пуста'],
                        ],
                    ],
                ]);
            }

            // Валидация данных гостя
            if (empty($body['guest_first_name']) || empty($body['guest_last_name']) || 
                empty($body['guest_email']) || empty($body['guest_phone'])) {
                return $this->send(422, [
                    'error' => [
                        'code' => 422,
                        'message' => 'Validation error',
                        'errors' => [
                            'guest_first_name' => empty($body['guest_first_name']) ? ['Имя обязательно'] : [],
                            'guest_last_name' => empty($body['guest_last_name']) ? ['Фамилия обязательна'] : [],
                            'guest_email' => empty($body['guest_email']) ? ['Email обязателен'] : [],
                            'guest_phone' => empty($body['guest_phone']) ? ['Телефон обязателен'] : [],
                        ],
                    ],
                ]);
            }

            foreach ($guestCart as $productId => $quantity) {
                $product = Product::findOne($productId);
                if ($product === null) {
                    continue;
                }
                
                $totalSum += $quantity * $product->price;
                $cartItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ];
            }
        }

        // Создаем заказ
        $order = new Order();
        if ($user !== null) {
            $order->user_id = $user->id;
        } else {
            $order->guest_first_name = $body['guest_first_name'];
            $order->guest_last_name = $body['guest_last_name'];
            $order->guest_email = $body['guest_email'];
            $order->guest_phone = $body['guest_phone'];
        }
        $order->total_sum = $totalSum;
        $order->delivery_address = $body['delivery_address'] ?? '';
        $order->comment = $body['comment'] ?? null;
        $order->status = 'new';

        if (!$order->validate()) {
            return $this->validation($order);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$order->save()) {
                throw new \Exception('Не удалось создать заказ');
            }

            // Создаем позиции заказа из корзины
            foreach ($cartItems as $cartItemData) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $cartItemData['product_id'];
                $orderItem->quantity = $cartItemData['quantity'];
                $orderItem->price = $cartItemData['price'];

                if (!$orderItem->save()) {
                    throw new \Exception('Не удалось создать позицию заказа');
                }
            }

            // Очищаем корзину
            if ($user !== null) {
                CartItem::deleteAll(['user_id' => $user->id]);
            } else {
                $session->remove('guest_cart');
            }

            $transaction->commit();

            return $this->send(201, [
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'total_sum' => $order->total_sum
                ],
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => $e->getMessage(),
                ],
            ]);
        }
    }
}


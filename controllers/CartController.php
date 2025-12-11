<?php

namespace app\controllers;

use app\components\BearerAuth;
use app\models\CartItem;
use app\models\Product;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Контроллер корзины, работает с таблицей cart_items.
 * Маршруты см. в config/web.php:
 *  - POST  /api/cart           -> actionAdd
 *  - GET   /api/cart           -> actionIndex
 *  - DELETE /api/cart/{id}     -> actionDelete
 */
class CartController extends FunctionController
{
     /**
      * Модель, с которой работает REST-контроллер (обязательна для ActiveController).
      */
     public $modelClass = CartItem::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // Корзина доступна и гостям, и авторизованным пользователям
        // Авторизация необязательна
        $behaviors['authenticator'] = [
            'class' => BearerAuth::class,
            'optional' => ['add', 'index', 'delete'],
        ];

        return $behaviors;
    }

    public function actions(): array
    {
        // Отключаем все стандартные действия ActiveController
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    /**
     * Список позиций корзины текущего пользователя или гостя.
     * GET /api/cart
     */
    public function actionIndex()
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        
        $items = [];
        $totalSum = 0;

        if ($user !== null) {
            // Зарегистрированный пользователь - корзина из БД
            $cartItems = CartItem::find()
                ->where(['user_id' => $user->id])
                ->with(['product'])
                ->all();

            foreach ($cartItems as $item) {
                $sum = $item->quantity * $item->product->price;
                $totalSum += $sum;
                
                $items[] = [
                    'id' => $item->id, // ID позиции корзины для удаления
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'sum' => $sum,
                ];
            }
        } else {
            // Гость - корзина из сессии
            $session = Yii::$app->session;
            $guestCart = $session->get('guest_cart', []);

            foreach ($guestCart as $productId => $quantity) {
                $product = Product::findOne($productId);
                if ($product === null) {
                    continue;
                }

                $sum = $quantity * $product->price;
                $totalSum += $sum;
                
                $items[] = [
                    'product_id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'sum' => $sum,
                ];
            }
        }

        return $this->send(200, [
            'data' => [
                'items' => $items,
                'total_sum' => $totalSum,
            ],
        ]);
    }

    /**
     * Добавить товар в корзину текущего пользователя или гостя.
     * POST /api/cart
     *
     * Body JSON:
     * {
     *   "product_id": 1,
     *   "quantity": 2
     * }
     */
    public function actionAdd()
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        
        $body = Yii::$app->request->getBodyParams();

        $productId = (int)($body['product_id'] ?? 0);
        $quantity = (int)($body['quantity'] ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [
                        'product_id' => ['product_id must be positive integer'],
                        'quantity' => ['quantity must be positive integer'],
                    ],
                ],
            ]);
        }

        $product = Product::findOne($productId);
        if ($product === null) {
            return $this->send(404, [
                'message' => 'Not found',
                'code' => 404,
            ]);
        }

        if ($user !== null) {
            // Зарегистрированный пользователь - сохраняем в БД
            $item = CartItem::find()
                ->andWhere(['user_id' => $user->id, 'product_id' => $productId])
                ->one();

            if ($item === null) {
                $item = new CartItem();
                $item->user_id = $user->id;
                $item->product_id = $productId;
                $item->quantity = $quantity;
            } else {
                $item->quantity += $quantity;
            }

            if (!$item->save()) {
                return $this->send(422, [
                    'error' => [
                        'code' => 422,
                        'message' => 'Validation error',
                        'errors' => $item->getErrors(),
                    ],
                ]);
            }
        } else {
            // Гость - сохраняем в сессии
            $session = Yii::$app->session;
            if (!$session->isActive) {
                $session->open();
            }
            
            $guestCart = $session->get('guest_cart', []);
            if (isset($guestCart[$productId])) {
                $guestCart[$productId] += $quantity;
            } else {
                $guestCart[$productId] = $quantity;
            }
            $session->set('guest_cart', $guestCart);
        }

        return $this->send(201, [
            'message' => 'Товар добавлен в корзину',
        ]);
    }

    /**
     * Удалить позицию корзины по ID (для зарегистрированных) или по product_id (для гостей).
     * DELETE /api/cart/{id}
     */
    public function actionDelete($id)
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;

        if ($user !== null) {
            // Зарегистрированный пользователь - удаляем из БД по ID
            $item = CartItem::find()
                ->andWhere(['id' => $id, 'user_id' => $user->id])
                ->one();

            if ($item === null) {
                return $this->send(404, [
                    'message' => 'Not found',
                    'code' => 404,
                ]);
            }

            $item->delete();
        } else {
            // Гость - удаляем из сессии по product_id
            $session = Yii::$app->session;
            if (!$session->isActive) {
                $session->open();
            }
            
            $guestCart = $session->get('guest_cart', []);
            $productId = (int)$id;
            
            if (!isset($guestCart[$productId])) {
                return $this->send(404, [
                    'message' => 'Not found',
                    'code' => 404,
                ]);
            }
            
            unset($guestCart[$productId]);
            $session->set('guest_cart', $guestCart);
        }

        return $this->send(200, [
            'message' => 'Товар удалён из корзины',
        ]);
    }
}



<?php

namespace app\controllers;

use app\components\BearerAuth;
use app\models\Product;
use app\models\ProductImage;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\web\UploadedFile;

class ProductController extends FunctionController
{
    public $modelClass = Product::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BearerAuth::class,
            'only' => ['create', 'update', 'delete', 'upload-image'],
        ];

        return $behaviors;
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Проверка прав администратора для создания/редактирования/удаления
        if (in_array($action->id, ['create', 'update', 'delete', 'upload-image'], true)) {
            /** @var User|null $user */
            $user = Yii::$app->user->identity;
            if (!$user || $user->role !== 'admin') {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->statusCode = 403;
                Yii::$app->response->data = [
                    'message' => 'Forbidden for you',
                ];
                return false;
            }
        }

        return true;
    }

    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    /**
     * Список товаров с форматированием согласно ТЗ
     * GET /api/products
     */
    public function actionIndex()
    {
        $dataProvider = $this->prepareProductDataProvider();
        $products = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();

        $data = [];
        foreach ($products as $product) {
            $imageUrl = null;
            if (!empty($product->images)) {
                $firstImage = $product->images[0];
                $imageUrl = Yii::$app->request->hostInfo . $firstImage->image_url;
            }

            $data[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $imageUrl,
                'category' => $product->category ? $product->category->name : null,
            ];
        }

        return $this->send(200, [
            'data' => $data,
            'pagination' => [
                'total' => $pagination->totalCount,
                'page' => $pagination->page + 1,
                'limit' => $pagination->pageSize,
            ],
        ]);
    }

    /**
     * Карточка товара с форматированием согласно ТЗ
     * GET /api/products/{id}
     */
    public function actionView($id)
    {
        $product = Product::find()
            ->with(['images'])
            ->andWhere(['id' => $id])
            ->one();

        if ($product === null) {
            return $this->send(404, [
                'message' => 'Not found',
                'code' => 404,
            ]);
        }

        $images = [];
        foreach ($product->images as $image) {
            $images[] = basename($image->image_url);
        }

        return $this->send(200, [
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'images' => $images,
                'stock_quantity' => $product->stock_quantity,
            ],
        ]);
    }

    protected function prepareProductDataProvider(): ActiveDataProvider
    {
        $query = Product::find()->with(['category', 'images']);
        $request = Yii::$app->request;

        if ($request->get('category_id')) {
            $query->andWhere(['category_id' => (int)$request->get('category_id')]);
        }

        if ($request->get('q')) {
            $query->andWhere(['like', 'name', $request->get('q')]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => (int)$request->get('per-page', 4),
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);
    }

    /**
     * Создание товара (админ)
     * POST /api/products
     */
    public function actionCreate(): Response
    {
        $product = new Product();
        $product->load(Yii::$app->request->getBodyParams(), '');

        if (!$product->validate()) {
            return $this->validation($product);
        }

        if (!$product->save()) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось создать товар',
                ],
            ]);
        }

        return $this->send(201, [
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            ],
        ]);
    }

    /**
     * Обновление товара (админ)
     * PUT/PATCH /api/products/{id}
     */
    public function actionUpdate($id): Response
    {
        $product = Product::findOne($id);
        if ($product === null) {
            return $this->send(404, [
                'message' => 'Not found',
                'code' => 404,
            ]);
        }

        $product->load(Yii::$app->request->getBodyParams(), '');

        if (!$product->validate()) {
            return $this->validation($product);
        }

        if (!$product->save()) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось обновить товар',
                ],
            ]);
        }

        // Явно устанавливаем формат ответа
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->statusCode = 200;

        return $this->send(200, [
            'message' => 'Товар успешно обновлён',
        ]);
    }

    /**
     * Удаление товара (админ)
     * DELETE /api/products/{id}
     */
    public function actionDelete($id): Response
    {
        $product = Product::findOne($id);
        if ($product === null) {
            return $this->send(404, [
                'message' => 'Not found',
                'code' => 404,
            ]);
        }

        // Загружаем связи перед удалением
        $product->refresh();
        
        // Удаляем связанные изображения
        foreach ($product->images as $image) {
            $imagePath = Yii::getAlias('@webroot') . $image->image_url;
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
            $image->delete();
        }

        $product->delete();

        // Явно устанавливаем формат и статус код перед возвратом
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->data = [
            'message' => 'Товар и связанные изображения удалены',
        ];
        
        return Yii::$app->response;
    }

    /**
     * Загрузка изображения товара (form-data, поле image).
     *
     * @param int $id ID товара
     * @return Response
     */
    public function actionUploadImage($id): Response
    {
        $product = Product::findOne((int)$id);
        if ($product === null) {
            return $this->send(404, [
                'error' => [
                    'code' => 404,
                    'message' => 'Товар не найден',
                ],
            ]);
        }

        $file = UploadedFile::getInstanceByName('image');
        if ($file === null) {
            return $this->send(400, [
                'error' => [
                    'code' => 400,
                    'message' => 'Файл image не передан (ожидается form-data)',
                ],
            ]);
        }

        // Простая валидация типа и размера файла
        if (!in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Допустимы только файлы JPG, PNG или WEBP',
                ],
            ]);
        }

        if ($file->size > 5 * 1024 * 1024) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Размер файла не должен превышать 5 МБ',
                ],
            ]);
        }

        $hash = hash('sha256', $file->baseName) . '.' . $file->extension;
        $uploadDir = Yii::$app->basePath . '/assets/upload/products';
        FileHelper::createDirectory($uploadDir);
        $filePath = $uploadDir . '/' . $hash;

        if (!$file->saveAs($filePath)) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось сохранить файл на сервере',
                ],
            ]);
        }

        $image = new ProductImage();
        $image->product_id = $product->id;
        // сохраняем относительный путь; в продакшене лучше сформировать полный URL по домену
        $image->image_url = '/assets/upload/products/' . $hash;

        if (!$image->save()) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось сохранить запись об изображении',
                ],
            ]);
        }

        return $this->send(201, $image);
    }
}


<?php

namespace app\controllers;

use app\components\BearerAuth;
use app\models\User;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\web\UploadedFile;

class ProfileController extends FunctionController
{
    public $modelClass = User::class;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BearerAuth::class,
        ];
        return $behaviors;
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $user = Yii::$app->user->identity;
        if ($user === null) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = 403;
            Yii::$app->response->data = [
                'message' => 'Login failed',
            ];
            return false;
        }

        return true;
    }

    public function actions(): array
    {
        // Отключаем все стандартные действия ActiveController
        return [];
    }

    /**
     * Просмотр профиля текущего пользователя
     * GET /api/profile
     */
    public function actionIndex(): Response
    {
        $user = Yii::$app->user->identity;

        return $this->send(200, [
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url ? Yii::$app->request->hostInfo . $user->avatar_url : null,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Редактирование профиля текущего пользователя
     * PUT/PATCH /api/profile
     */
    public function actionUpdate(): Response
    {
        $user = Yii::$app->user->identity;
        $data = Yii::$app->request->getBodyParams();

        // Разрешаем редактировать только определенные поля
        $allowedFields = ['first_name', 'last_name', 'email', 'phone'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $user->$field = $data[$field];
            }
        }

        if (!$user->validate()) {
            return $this->validation($user);
        }

        if (!$user->save()) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось обновить профиль',
                ],
            ]);
        }

        return $this->send(200, [
            'message' => 'Профиль успешно обновлён',
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Загрузка аватара профиля
     * POST /api/profile/avatar
     */
    public function actionAvatar(): Response
    {
        $user = Yii::$app->user->identity;
        
        $file = UploadedFile::getInstanceByName('avatar');
        
        if ($file === null) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [
                        'avatar' => ['Файл не передан'],
                    ],
                ],
            ]);
        }

        // Валидация расширения
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($file->extension), $allowedExtensions, true)) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [
                        'avatar' => ['Допустимы только файлы JPG и PNG'],
                    ],
                ],
            ]);
        }

        // Валидация размера (max 5 МБ)
        if ($file->size > 5 * 1024 * 1024) {
            return $this->send(422, [
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => [
                        'avatar' => ['Размер файла не должен превышать 5 МБ'],
                    ],
                ],
            ]);
        }

        // Удаляем старый аватар, если есть
        if ($user->avatar_url && file_exists(Yii::getAlias('@webroot') . $user->avatar_url)) {
            @unlink(Yii::getAlias('@webroot') . $user->avatar_url);
        }

        // Сохраняем новый аватар
        $uploadDir = Yii::getAlias('@webroot/uploads/avatars');
        FileHelper::createDirectory($uploadDir);
        
        $fileName = 'user' . $user->id . '_' . time() . '.' . $file->extension;
        $filePath = $uploadDir . '/' . $fileName;
        
        if (!$file->saveAs($filePath)) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось сохранить файл',
                ],
            ]);
        }

        $avatarUrl = '/uploads/avatars/' . $fileName;
        $user->avatar_url = $avatarUrl;
        
        if (!$user->save(false)) {
            return $this->send(500, [
                'error' => [
                    'code' => 500,
                    'message' => 'Не удалось обновить профиль',
                ],
            ]);
        }

        // Формируем полный URL
        $host = Yii::$app->request->hostInfo;
        $fullUrl = $host . $avatarUrl;

        return $this->send(200, [
            'message' => 'Аватар успешно загружен',
            'avatar_url' => $fullUrl,
        ]);
    }
}


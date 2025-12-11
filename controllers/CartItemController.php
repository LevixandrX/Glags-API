<?php

namespace app\controllers;

use app\models\CartItem;
use Yii;
use yii\data\ActiveDataProvider;

class CartItemController extends FunctionController
{
    public $modelClass = CartItem::class;

    public function actions(): array
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = fn() => $this->prepareCartDataProvider();
        return $actions;
    }

    protected function prepareCartDataProvider(): ActiveDataProvider
    {
        $query = CartItem::find()->with(['product', 'user']);
        $userId = Yii::$app->request->get('user_id');

        if ($userId) {
            $query->andWhere(['user_id' => (int)$userId]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => (int)Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }
}


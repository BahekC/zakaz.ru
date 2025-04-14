<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\GroupProduct;
use app\models\GroupCount;
use app\models\Product;
use yii\web\Response;

class CartController extends Controller
{
    /**
     * Поведения контроллера для контроля доступа.
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['index', 'add', 'update', 'remove', 'count', 'checkout','history'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только аутентифицированные пользователи
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isUser(); // Проверка роли пользователя
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Отображает корзину пользователя и историю заказов.
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;

        // Поиск или создание записи корзины для пользователя с статусом 'Новая'
        $cart = GroupProduct::findOne(['idUser' => $userId, 'status' => GroupProduct::STATUS_NEW]);
        if (!$cart) {
            $cart = new GroupProduct();
            $cart->name = 'Корзина ' . date('Y-m-d H:i:s');
            $cart->idUser = $userId;
            $cart->status = GroupProduct::STATUS_NEW;
            if (!$cart->save()) {
                throw new \Exception('Не удалось создать корзину.');
            }
        }

        // Получение всех товаров в корзине
        $items = GroupCount::find()
            ->where(['idGroup' => $cart->id, 'idUser' => $userId])
            ->with('idProduct0')
            ->all();

        // Вычисление общей суммы
        $total = 0;
        foreach ($items as $item) {
            $total += $item->idProduct0->price * $item->count;
        }

        // Получение истории заказов (статусы отличные от 'Новая')
        $orders = GroupProduct::find()
            ->where(['idUser' => $userId])
            ->andWhere(['!=', 'status', GroupProduct::STATUS_NEW])
            ->with(['groupCounts.idProduct0'])
            ->orderBy(['timestamp' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'cart' => $cart,
            'items' => $items,
            'total' => $total,
            'orders' => $orders,
        ]);
    }

    /**
     * Добавляет товар в корзину.
     * Ожидает POST запрос с 'idProduct' и 'quantity'.
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost) {
            $userId = Yii::$app->user->id;
            $idProduct = Yii::$app->request->post('idProduct');
            $quantity = Yii::$app->request->post('quantity', 1);

            // Проверка существования товара
            $product = Product::findOne($idProduct);
            if (!$product) {
                return ['success' => false, 'message' => 'Товар не найден'];
            }

            // Проверка доступности товара на складе
            if ($quantity > $product->count) {
                return ['success' => false, 'message' => 'Запрашиваемое количество превышает доступное на складе.'];
            }

            // Поиск или создание корзины пользователя с статусом 'Новая'
            $cart = GroupProduct::findOne(['idUser' => $userId, 'status' => GroupProduct::STATUS_NEW]);
            if (!$cart) {
                $cart = new GroupProduct();
                $cart->name = 'Корзина ' . date('Y-m-d H:i:s');
                $cart->idUser = $userId;
                $cart->status = GroupProduct::STATUS_NEW;
                if (!$cart->save()) {
                    return ['success' => false, 'message' => 'Не удалось создать корзину'];
                }
            }

            // Проверка, есть ли уже этот товар в корзине
            $groupCount = GroupCount::findOne(['idGroup' => $cart->id, 'idProduct' => $idProduct, 'idUser' => $userId]);
            if ($groupCount) {
                $groupCount->count += $quantity;
            } else {
                $groupCount = new GroupCount();
                $groupCount->idGroup = $cart->id;
                $groupCount->idProduct = $idProduct;
                $groupCount->idUser = $userId;
                $groupCount->count = $quantity;
            }

            if ($groupCount->save()) {
                // Получение нового количества товаров в корзине
                $cartCount = GroupCount::find()
                    ->where(['idGroup' => $cart->id, 'idUser' => $userId])
                    ->sum('count');
                $cartCount = $cartCount ? $cartCount : 0;

                return ['success' => true, 'message' => 'Товар добавлен в корзину', 'cartCount' => $cartCount];
            } else {
                return ['success' => false, 'message' => 'Не удалось добавить товар в корзину'];
            }
        }

        throw new NotFoundHttpException('Страница не найдена.');
    }

    /**
     * Обновляет количество товара в корзине.
     * Ожидает POST запрос с 'idProduct' и 'quantity'.
     */
    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost) {
            $userId = Yii::$app->user->id;
            $idProduct = Yii::$app->request->post('idProduct');
            $quantity = Yii::$app->request->post('quantity');

            // Поиск корзины
            $cart = GroupProduct::findOne(['idUser' => $userId, 'status' => GroupProduct::STATUS_NEW]);
            if (!$cart) {
                return ['success' => false, 'message' => 'Корзина не найдена'];
            }

            // Поиск товара в корзине
            $groupCount = GroupCount::findOne(['idGroup' => $cart->id, 'idProduct' => $idProduct, 'idUser' => $userId]);
            if (!$groupCount) {
                return ['success' => false, 'message' => 'Товар не найден в корзине'];
            }

            // Проверка доступности товара на складе
            $product = $groupCount->idProduct0;
            if ($quantity > $product->count) {
                return ['success' => false, 'message' => 'Запрашиваемое количество превышает доступное на складе.'];
            }

            if ($quantity < 1) {
                // Удаление товара, если количество меньше 1
                if ($groupCount->delete()) {
                    // Получение нового количества товаров в корзине
                    $cartCount = GroupCount::find()
                        ->where(['idGroup' => $cart->id, 'idUser' => $userId])
                        ->sum('count');
                    $cartCount = $cartCount ? $cartCount : 0;

                    return ['success' => true, 'message' => 'Товар удален из корзины', 'cartCount' => $cartCount];
                } else {
                    return ['success' => false, 'message' => 'Не удалось удалить товар из корзины'];
                }
            } else {
                $groupCount->count = $quantity;
                if ($groupCount->save()) {
                    // Получение нового количества товаров в корзине
                    $cartCount = GroupCount::find()
                        ->where(['idGroup' => $cart->id, 'idUser' => $userId])
                        ->sum('count');
                    $cartCount = $cartCount ? $cartCount : 0;

                    return ['success' => true, 'message' => 'Количество обновлено', 'cartCount' => $cartCount];
                } else {
                    return ['success' => false, 'message' => 'Не удалось обновить количество'];
                }
            }
        }

        throw new NotFoundHttpException('Страница не найдена.');
    }

    /**
     * Удаляет товар из корзины.
     * Ожидает POST запрос с 'idProduct'.
     */
    public function actionRemove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost) {
            $userId = Yii::$app->user->id;
            $idProduct = Yii::$app->request->post('idProduct');

            // Поиск корзины
            $cart = GroupProduct::findOne(['idUser' => $userId, 'status' => GroupProduct::STATUS_NEW]);
            if (!$cart) {
                return ['success' => false, 'message' => 'Корзина не найдена'];
            }

            // Поиск товара в корзине
            $groupCount = GroupCount::findOne(['idGroup' => $cart->id, 'idProduct' => $idProduct, 'idUser' => $userId]);
            if (!$groupCount) {
                return ['success' => false, 'message' => 'Товар не найден в корзине'];
            }

            if ($groupCount->delete()) {
                // Получение нового количества товаров в корзине
                $cartCount = GroupCount::find()
                    ->where(['idGroup' => $cart->id, 'idUser' => $userId])
                    ->sum('count');
                $cartCount = $cartCount ? $cartCount : 0;

                return ['success' => true, 'message' => 'Товар удален из корзины', 'cartCount' => $cartCount];
            } else {
                return ['success' => false, 'message' => 'Не удалось удалить товар из корзины'];
            }
        }

        throw new NotFoundHttpException('Страница не найдена.');
    }

    /**
     * Оформляет заказ.
     * Изменяет статус корзины на 'В обработке'.
     */
    public function actionCheckout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isPost) {
            $userId = Yii::$app->user->id;

            // Поиск корзины
            $cart = GroupProduct::findOne(['idUser' => $userId, 'status' => GroupProduct::STATUS_NEW]);
            if (!$cart) {
                return ['success' => false, 'message' => 'Корзина пуста'];
            }

            // Проверка наличия товаров в корзине
            $items = GroupCount::find()
                ->where(['idGroup' => $cart->id, 'idUser' => $userId])
                ->with('idProduct0')
                ->all();

            if (empty($items)) {
                return ['success' => false, 'message' => 'В корзине нет товаров'];
            }

            // Проверка доступности товаров на складе
            foreach ($items as $item) {
                if ($item->count > $item->idProduct0->count) {
                    return ['success' => false, 'message' => 'Товар "' . $item->idProduct0->name . '" превышает доступное количество на складе.'];
                }
            }

            // Начало транзакции
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Уменьшение количества товаров на складе
                foreach ($items as $item) {
                    $product = $item->idProduct0;
                    $product->count -= $item->count;
                    if (!$product->save()) {
                        throw new \Exception('Не удалось обновить количество товара "' . $product->name . '".');
                    }
                }

                // Изменение статуса корзины на 'В обработке'
                $cart->status = GroupProduct::STATUS_PROCESSING;
                if (!$cart->save(false, ['status'])) {
                    throw new \Exception('Не удалось обновить статус корзины.');
                }

                // Коммит транзакции
                $transaction->commit();

                return ['success' => true, 'message' => 'Заказ успешно оформлен'];
            } catch (\Exception $e) {
                // Откат транзакции при ошибке
                $transaction->rollBack();
                Yii::error($e->getMessage(), __METHOD__);
                return ['success' => false, 'message' => 'Произошла ошибка при оформлении заказа'];
            }
        }

        throw new NotFoundHttpException('Страница не найдена.');
    }

    /**
     * Возвращает текущее количество товаров в корзине.
     */
    public function actionCount()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'cartCount' => 0];
        }

        $cart = GroupProduct::findOne(['idUser' => Yii::$app->user->id, 'status' => GroupProduct::STATUS_NEW]);
        if (!$cart) {
            return ['success' => true, 'cartCount' => 0];
        }

        $cartCount = GroupCount::find()
            ->where(['idGroup' => $cart->id, 'idUser' => Yii::$app->user->id])
            ->sum('count');

        return ['success' => true, 'cartCount' => $cartCount ? $cartCount : 0];
    }
    /**
     * Отображает историю заказов пользователя.
     */
    public function actionHistory()
    {
        $userId = Yii::$app->user->id;

        // Получение всех заказов пользователя со статусом отличным от 'Новая'
        $orders = GroupProduct::find()
            ->where(['idUser' => $userId])
            ->andWhere(['!=', 'status', GroupProduct::STATUS_NEW])
            ->with(['groupCounts.idProduct0'])
            ->orderBy(['timestamp' => SORT_DESC])
            ->all();

        return $this->render('history', [
            'orders' => $orders,
        ]);
    }
}

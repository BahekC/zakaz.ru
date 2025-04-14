<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $orders app\models\GroupProduct[] */

$this->title = 'История заказов';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="cart-history">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (empty($orders)): ?>
        <p>У вас нет оформленных заказов.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>

            <?php
            // Определение классов стиля в зависимости от статуса
            switch ($order->status) {
                case 'Новая':
                case 'В обработке':
                    $panelClass = 'panel-info'; // Голубой фон заголовка
                    $labelClass = 'label-primary'; // Синяя плашка статуса
                    break;
                case 'Выполнено':
                    $panelClass = 'panel-success'; // Светло-зеленый фон заголовка
                    $labelClass = 'label-success'; // Зеленая плашка статуса
                    break;
                case 'Отклонено':
                    $panelClass = 'panel-danger'; // Светло-красный фон заголовка
                    $labelClass = 'label-danger'; // Красная плашка статуса
                    break;
                default:
                    $panelClass = 'panel-default';
                    $labelClass = 'label-default';
                    break;
            }
            ?>

            <div class="order-item panel <?= $panelClass ?>">
                <div class="panel-heading">
                    Заказ №<?= Html::encode($order->id) ?> от <?= Yii::$app->formatter->asDate($order->timestamp) ?>
                    <span class="label <?= $labelClass ?> pull-right"><?= Html::encode($order->status) ?></span>
                </div>
                <ul class="list-group">
                    <?php
                    $orderItems = $order->groupCounts;
                    ?>
                    <?php foreach ($orderItems as $orderItem): ?>
                        <?php $product = $orderItem->idProduct0; ?>
                        <li class="list-group-item">
                            <div class="row">
                                <!-- Картинка -->
                                <div class="col-md-2">
                                    <?= Html::img($product->photoUrl, [
                                        'alt' => Html::encode($product->name),
                                        'class' => 'img-responsive',
                                        'style' => 'max-height: 100px;'
                                    ]) ?>
                                </div>
                                <!-- Название и количество -->
                                <div class="col-md-8">
                                    <h4><?= Html::encode($product->name) ?></h4>
                                    <p>Количество: <?= Html::encode($orderItem->count) ?></p>
                                </div>
                                <!-- Цена -->
                                <div class="col-md-2 text-right">
                                    <p><strong><?= Yii::$app->formatter->asCurrency($product->price * $orderItem->count) ?></strong></p>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="panel-footer flex-footer result-zakaz">
                    <span>Общая сумма: <?= Yii::$app->formatter->asCurrency(array_sum(array_map(function($item) {
                            return $item->idProduct0->price * $item->count;
                        }, $orderItems))) ?></span>

                    <?php if ($order->receipt_filename && $order->receipt_extension): ?>
                        <?= Html::a('Скачать чек', Url::to('@web/assets/cart/' . $order->receipt_filename . '.' . $order->receipt_extension), ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

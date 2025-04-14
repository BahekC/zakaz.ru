<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $cart app\models\GroupProduct */
/* @var $items app\models\GroupCount[] */
/* @var $total integer */

$this->title = 'Ваша корзина';
$this->params['breadcrumbs'][] = $this->title;

// Определение глобальных JavaScript-переменных
$this->registerJs("
    var baseUrl = '" . Url::base() . "';
    var csrfToken = '" . Yii::$app->request->csrfToken . "';
", \yii\web\View::POS_HEAD);
?>
<div class="cart-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (empty($items)): ?>
        <p>Ваша корзина пуста.</p>
    <?php else: ?>
        <!-- Корзина -->
        <div class="cart-items">
            <div class="cart-item panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($cart->name) ?>
            </div>
            <?php foreach ($items as $item): ?>
                <?php $product = $item->idProduct0; ?>
                    <ul class="list-group">
                        <li class="list-group-item" data-product-id="<?= $product->id ?>">
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
                                    <div class="quantity-control">
                                        <?= Html::button('<span class="glyphicon glyphicon-minus"></span>', [
                                            'class' => 'btn btn-default btn-sm cart-decrease-qty',
                                            'data-product-id' => $product->id
                                        ]) ?>
                                        <?= Html::input('number', 'quantity', $item->count, [
                                            'class' => 'form-control input-sm cart-quantity-field',
                                            'data-product-id' => $product->id,
                                            'min' => 1,
                                            'style' => 'display: inline-block; width: 60px; margin: 0 5px;'
                                        ]) ?>
                                        <?= Html::button('<span class="glyphicon glyphicon-plus"></span>', [
                                            'class' => 'btn btn-default btn-sm cart-increase-qty',
                                            'data-product-id' => $product->id
                                        ]) ?>
                                    </div>
                                </div>
                                <!-- Цена и удаление -->
                                <div class="col-md-2 text-right">
                                    <p><strong><?= Yii::$app->formatter->asCurrency($product->price) ?></strong></p>
                                    <?= Html::button('<span class="glyphicon glyphicon-remove"></span>', [
                                        'class' => 'btn btn-danger btn-xs remove-item',
                                        'data-product-id' => $product->id,
                                        'title' => 'Удалить товар'
                                    ]) ?>
                                </div>
                            </div>
                        </li>
                    </ul>
            <?php endforeach; ?>
        </div>
        </div>

        <!-- Суммарная стоимость и оформление заказа -->
        <div class="cart-summary">
            <h3>Общая сумма: <?= Yii::$app->formatter->asCurrency($total) ?></h3>
            <?= Html::button('Оформить заказ', ['class' => 'btn btn-success', 'id' => 'checkout-button']) ?>
        </div>
    <?php endif; ?>
    <?= Html::a('История заказов', ['cart/history'], ['class' => 'btn btn-info', 'style' => 'margin-left: 10px;']) ?>
</div>

<?php
// Регистрация cart.js
$this->registerJsFile('@web/js/cart.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

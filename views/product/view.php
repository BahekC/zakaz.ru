<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $category array */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="product-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <!-- Левая колонка: Изображение, название и описание -->
        <div class="col-md-8">
            <div class="product-details">
                <?php if ($model->photo): ?>
                    <img src="<?= Html::encode($model->photoUrl) ?>" alt="<?= Html::encode($model->name) ?>" class="product-image img-responsive">
                <?php else: ?>
                    <img src="<?= Url::to('@web/uploads/no-image.png') ?>" alt="Нет изображения" class="product-image img-responsive">
                <?php endif; ?>

                <h2><?= Html::encode($model->name) ?></h2>
                <p><?= Html::encode($model->description) ?></p>
                <p><strong>Категория:</strong> <?= isset($category[$model->idCategory]) ? Html::encode($category[$model->idCategory]) : 'Неизвестная категория' ?></p>
            </div>
        </div>

        <!-- Правая колонка: Цена, количество в наличии, выбор количества и кнопка "В корзину" -->
        <div class="col-md-4">
            <div class="product-purchase">
                <h3>Цена: <?= Yii::$app->formatter->asCurrency($model->price, 'RUB') ?></h3>
                <p><strong>Количество в наличии:</strong> <?= Html::encode($model->count) ?> шт.</p>

                <div class="quantity-control">
                    <button type="button" class="btn btn-default decrease-qty" data-product-id="<?= $model->id ?>">-</button>
                    <input type="number" class="form-control quantity-field" data-product-id="<?= $model->id ?>" value="1" min="1" max="<?= Html::encode($model->count) ?>">
                    <button type="button" class="btn btn-default increase-qty" data-product-id="<?= $model->id ?>">+</button>
                </div>

                <br>
                <?php
                if(!Yii::$app->user->identity->isModeration()&&!Yii::$app->user->identity->isAdmin()){
                Html::button('В корзину', [
                    'class' => 'btn btn-success add-to-cart-btn',
                    'data-product-id' => $model->id,
                    'data-price' => $model->price,
                    'data-count' => $model->count,
                ]);
                }?>

                <!-- Сообщение об успехе или ошибке -->
                <div id="cart-message" style="margin-top: 10px;"></div>
            </div>
        </div>
    </div>

    <p>
        <?= Yii::$app->user->identity->isModeration() ? Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>
        <?= Yii::$app->user->identity->isModeration() ? Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить карточку товара?',
                'method' => 'post',
            ],
        ]) : '' ?>
    </p>

</div>

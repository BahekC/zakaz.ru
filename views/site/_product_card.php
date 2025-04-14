<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model app\models\Product */
?>
<div class="card flex-fill">
    <div class="product-image-wrapper">
        <?= Html::img($model->photoUrl, [
            'class' => 'card-img-top product-image',
            'alt' => Html::encode($model->name),
            'loading' => 'lazy' // Опционально: добавление ленивой загрузки
        ]) ?>
    </div>
    <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?= Html::encode($model->name) ?></h5>
        <p class="card-text">Цена: <?= Yii::$app->formatter->asCurrency($model->price) ?></p>
        <p class="card-text">Наличие: <?= Html::encode($model->count) ?></p>
        <p class="card-text"><small class="text-muted">Категория: <?= Html::encode($model->category->name) ?></small></p>
        <div class="quantity-input mb-2">
            <?= Html::button('-', ['class' => 'btn btn-secondary btn-sm decrease-qty', 'data-product-id' => $model->id]) ?>
            <?= Html::input('number', 'quantity', 1, [
                'class' => 'form-control form-control-sm d-inline-block quantity-field',
                'data-product-id' => $model->id,
                'min' => 1,
                'style' => 'width: 60px; display: inline-block;'
            ]) ?>
            <?= Html::button('+', ['class' => 'btn btn-secondary btn-sm increase-qty', 'data-product-id' => $model->id]) ?>
        </div>
        <div class="mt-auto">
            <?= Html::button('Добавить в корзину', [
                'class' => 'btn btn-success btn-block add-to-cart',
                'data-product-id' => $model->id
            ]) ?>
            <?= Html::a('Подробнее', ['product/view', 'id' => $model->id], ['class' => 'btn btn-primary btn-block mt-2']) ?>
        </div>
    </div>
</div>

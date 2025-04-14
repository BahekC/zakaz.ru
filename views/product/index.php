<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $users array */

$this->title = 'Товары';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
<div class="product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id' => 'product-pjax', 'timeout' => 5000, 'enablePushState' => false]); ?>

    <!-- Кнопка для создания нового товара -->
    <p>
        <?= Html::a('Создать карточку товара', '#', [
            'class' => 'btn btn-success',
            'id' => 'create-product-button',
            'data-toggle' => 'modal',
            'data-target' => '#product-modal',
        ]) ?>
    </p>

    <?php if ($dataProvider->getCount() === 0): ?>
        <p>Нет товаров для отображения.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($dataProvider->getModels() as $product): ?>
                <div class="col-md-4" id="product-<?= $product->id ?>">
                    <div class="card mb-4 shadow-sm">
                        <?php if ($product->photo): ?>
                            <img src="<?= Html::encode($product->getPhotoUrl()) ?>" class="card-img-top img-fluid" alt="<?= Html::encode($product->name) ?>" style="width: 100%; height: auto; display: block;">
                        <?php else: ?>
                            <img src="<?= Url::to('@web/images/no-image.png') ?>" class="card-img-top img-fluid" alt="Без изображения">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= Html::encode($product->name) ?></h5>
                            <p class="card-text"><?= Html::encode($product->description) ?></p>
                            <p class="card-text"><strong>Дата размещения:</strong> <?= Yii::$app->formatter->asDatetime($product->timestamp) ?></p>
                            <p class="card-text"><strong>Категория:</strong> <?= Html::encode($product->category ? $product->category->name : 'Не указана') ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <?= Html::a('Просмотр', ['view', 'id' => $product->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= Html::a('Редактировать', '#', [
                                        'class' => 'btn btn-sm btn-outline-secondary edit-product-button',
                                        'data-id' => $product->id,
                                        'data-toggle' => 'modal',
                                        'data-target' => '#product-modal',
                                    ]) ?>
                                    <?= Html::a('Удалить', '#', [
                                        'class' => 'btn btn-sm btn-outline-danger delete-product-button',
                                        'data-id' => $product->id,
                                        'data-toggle' => 'modal',
                                        'data-target' => '#delete-product-modal',
                                    ]) ?>
                                </div>
                                <small class="text-muted">Цена: <?= Yii::$app->formatter->asCurrency($product->price) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Пагинация -->
        <div class="text-center">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination'], // Класс для стилизации
                'linkOptions' => ['class' => 'page-link'],
                'prevPageLabel' => '&laquo;',
                'nextPageLabel' => '&raquo;',
                'firstPageLabel' => 'Первая',
                'lastPageLabel' => 'Последняя',
            ]) ?>
        </div>
    <?php endif; ?>

    <?php Pjax::end(); ?>

    <!-- Модальное окно для создания и редактирования товара -->
    <div class="modal fade" id="product-modal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Создать товар</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Содержимое формы будет загружено через AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для подтверждения удаления товара -->
    <div class="modal fade" id="delete-product-modal" tabindex="-1" role="dialog" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProductModalLabel">Удалить товар</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Вы уверены, что хотите удалить этот товар?
                </div>
                <div class="modal-footer">
                    <?= Html::button('Отмена', ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']) ?>
                    <?= Html::button('Удалить', ['class' => 'btn btn-danger', 'id' => 'confirm-delete-button']) ?>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

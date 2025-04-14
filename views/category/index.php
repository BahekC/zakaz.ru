<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $search string */

$this->title = 'Список категорий';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id' => 'category-pjax', 'timeout' => 5000, 'enablePushState' => false]); ?>

    <!-- Форма поиска с кнопкой -->
    <div class="form-group">
        <?= Html::beginForm(['category/index'], 'get', ['data-pjax' => true, 'class' => 'form-inline']) ?>
        <div class="input-group">
            <?= Html::input('text', 'search', $search, [
                'class' => 'form-control',
                'id' => 'search-input',
                'placeholder' => 'Поиск по наименованию категории...',
            ]) ?>
            <span class="input-group-btn">
                    <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
                </span>
        </div>
        <?= Html::endForm() ?>
    </div>

    <!-- Кнопка для открытия модального окна добавления категории -->
    <p>
        <?php if (Yii::$app->user->identity->isAdmin()): ?>
            <?= Html::button('Добавить категорию', [
                'class' => 'btn btn-success',
                'id' => 'add-category-button',
                'data-toggle' => 'modal',
                'data-target' => '#addCategoryModal',
            ]) ?>
        <?php endif; ?>
    </p>

    <?php if ($dataProvider->getCount() === 0): ?>
        <p>Нет категорий для отображения.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($dataProvider->getModels() as $category): ?>
                <li class="list-group-item" id="category-<?= $category->id ?>">
                    <div class="row">
                        <div class="col-md-8" id="category-name-<?= $category->id ?>">
                            <?= Html::encode($category->name) ?>
                        </div>
                        <div class="col-md-4 text-right">
                            <?php if (Yii::$app->user->identity->isAdmin()): ?>
                                <?= Html::a('<span class="glyphicon glyphicon-edit"></span>', '#', [
                                    'class' => 'btn btn-sm btn-warning edit-category',
                                    'data-id' => $category->id,
                                    'title' => 'Редактировать',
                                ]) ?>
                                <?= Html::a('<span class="glyphicon glyphicon-trash"></span>', '#', [
                                    'class' => 'btn btn-sm btn-danger delete-category',
                                    'data-id' => $category->id,
                                    'title' => 'Удалить',
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

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

</div>

<!-- Модальное окно для добавления новой категории -->
<?php if (Yii::$app->user->identity->isAdmin()): ?>
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?= Html::beginForm(['category/create'], 'post', ['id' => 'add-category-form']) ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addCategoryModalLabel">Добавить новую категорию</h4>
                </div>
                <div class="modal-body">
                    <?= Html::activeTextInput($model = new \app\models\Category(), 'name', [
                        'class' => 'form-control',
                        'placeholder' => 'Наименование категории',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="modal-footer">
                    <?= Html::button('Закрыть', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
<?php endif; ?>

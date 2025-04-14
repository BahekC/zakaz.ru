<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\bootstrap\Carousel; // Добавьте эту строку

/* @var $this yii\web\View */
/* @var $categories app\models\Category[] */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $selectedCategory string */

$this->title = 'Главная страница';
?>
<div class="site-index">
    <div class="container mt-4">
        <!--Блок -->
        <header class="jumbotron text-center bg-light">
            <h1 class="display-4">Добро пожаловать!</h1>
            <p class="lead">Найдите лучшие деревяшки для ваших нужд</p>
        </header>
        <!-- Фильтр по категориям -->
        <div class="row mb-4 filter-category">
            <div class="col-12">
                <h3>Фильтр по категориям</h3>
                <?= Html::beginForm(['site/index'], 'get', ['class' => 'form-inline']) ?>
                <div class="form-group mr-2">
                    <?php
                    // Подготавливаем массив категорий для выпадающего списка
                    $categoryItems = ['all' => 'Все'];
                    foreach ($categories as $category) {
                        $categoryItems[$category->id] = $category->name;
                    }
                    ?>
                    <?= Html::dropDownList('category', ($selectedCategory ?: 'all'), $categoryItems, [
                        'class' => 'form-control',
                        'prompt' => 'Выберите категорию',
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= Html::submitButton('Применить фильтр', ['class' => 'btn btn-primary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <!-- Карточки товаров -->
        <div class="row">
            <?php if ($dataProvider->getTotalCount() > 0): ?>
                <?= ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemOptions' => ['class' => 'col-md-4 mb-4 px-2 d-flex'], // Добавлен класс px-2 для горизонтальных отступов
                    'layout' => "{items}\n{pager}",
                    'itemView' => function ($model, $key, $index, $widget) {
                        return $this->render('_product_card', ['model' => $model]);
                    },
                    'pager' => [
                        'class' => \yii\widgets\LinkPager::class,
                        'options' => ['class' => 'pagination justify-content-center'],
                        'activePageCssClass' => 'active',
                        'disabledPageCssClass' => 'disabled',
                        'maxButtonCount' => 5,
                    ],
                ]) ?>
            <?php else: ?>
                <div class="col-12">
                    <p>Нет товаров для отображения.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
//phpinfo();
?>
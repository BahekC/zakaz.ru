<?php

use app\models\GroupProduct;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager; // Импортируем LinkPager

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $search string */

$this->title = 'Список заказов';
$this->params['breadcrumbs'][] = $this->title;

// Передаем CSRF-токен и baseUrl в JavaScript
$csrfToken = Yii::$app->request->csrfToken;
$baseUrl = Url::base(true);
$js = "var csrfToken = '$csrfToken'; var baseUrl = '$baseUrl';";
$this->registerJs($js, \yii\web\View::POS_HEAD);
?>
<div class="group-product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id' => 'group-product-pjax', 'timeout' => 5000, 'enablePushState' => false]); ?>

    <!-- Форма поиска с кнопкой -->
    <div class="form-group">
        <?= Html::beginForm(['group-product/index'], 'get', ['data-pjax' => true, 'class' => 'form-inline']) ?>
        <div class="input-group">
            <?= Html::input('text', 'search', $search, [
                'class' => 'form-control',
                'id' => 'search-input',
                'placeholder' => 'Поиск по логину или электронной почте пользователя...',
            ]) ?>
            <span class="input-group-btn">
                    <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
                </span>
        </div>
        <?= Html::endForm() ?>
    </div>

    <?php if ($dataProvider->getCount() === 0): ?>
        <p>Нет заказов для отображения.</p>
    <?php else: ?>
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <?php foreach ($dataProvider->getModels() as $index => $groupProduct): ?>
                <?php
                // Получение пользователя
                $user = $groupProduct->user; // Метод getUser()

                // Определение классов статуса
                switch ($groupProduct->status) {
                    case GroupProduct::STATUS_PROCESSING:
                        $panelClass = 'panel-info'; // Голубой фон заголовка
                        $labelClass = 'label-primary'; // Синяя плашка статуса
                        break;
                    case GroupProduct::STATUS_COMPLETED:
                        $panelClass = 'panel-success'; // Светло-зеленый фон заголовка
                        $labelClass = 'label-success'; // Зеленая плашка статуса
                        break;
                    case GroupProduct::STATUS_REJECTED:
                        $panelClass = 'panel-danger'; // Светло-красный фон заголовка
                        $labelClass = 'label-danger'; // Красная плашка статуса
                        break;
                    default:
                        $panelClass = 'panel-default';
                        $labelClass = 'label-default';
                        break;
                }

                // Расчет общей суммы
                $totalSum = 0;
                foreach ($groupProduct->groupCounts as $groupCount) {
                    $totalSum += $groupCount->idProduct0->price * $groupCount->count;
                }

                // Определение необходимости отображать кнопки изменения статуса
                $canChangeStatus = !in_array($groupProduct->status, [GroupProduct::STATUS_COMPLETED, GroupProduct::STATUS_REJECTED]);
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading <?= $panelClass ?>" role="tab" id="heading<?= $groupProduct->id ?>">
                        <h4 class="panel-title">
                            <?= Html::a(
                                Html::encode("{$user->login} - {$groupProduct->name}"),
                                "#collapse{$groupProduct->id}",
                                [
                                    'class' => 'accordion-toggle collapsed',
                                    'data-toggle' => 'collapse',
                                    'data-parent' => '#accordion',
                                    'aria-expanded' => 'false',
                                    'aria-controls' => "collapse{$groupProduct->id}",
                                ]
                            ) ?>
                            <span class="label status-label <?= $labelClass ?> pull-right"><?= Html::encode($groupProduct->status) ?></span>
                        </h4>
                    </div>
                    <div id="collapse<?= $groupProduct->id ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?= $groupProduct->id ?>">
                        <div class="panel-body">
                            <?php if (empty($groupProduct->groupCounts)): ?>
                                <p>В заказе нет товаров.</p>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($groupProduct->groupCounts as $groupCount): ?>
                                        <?php
                                        $product = $groupCount->idProduct0;
                                        $productSum = $product->price * $groupCount->count;
                                        ?>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <?= Html::img($product->photoUrl, [
                                                        'alt' => Html::encode($product->name),
                                                        'class' => 'img-responsive',
                                                        'style' => 'max-height: 100px;',
                                                    ]) ?>
                                                </div>
                                                <div class="col-md-7">
                                                    <h5><?= Html::encode($product->name) ?></h5>
                                                    <p>Количество: <?= Html::encode($groupCount->count) ?></p>
                                                    <p>Цена: <?= Yii::$app->formatter->asCurrency($product->price, 'RUB') ?></p>
                                                </div>
                                                <div class="col-md-3 text-right">
                                                    <p><strong><?= Yii::$app->formatter->asCurrency($productSum, 'RUB') ?></strong></p>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <hr>
                                <p><strong>Итоговая сумма: <?= Yii::$app->formatter->asCurrency($totalSum, 'RUB') ?></strong></p>
                                <?php if ($canChangeStatus): ?>
                                    <div class="btn-group" role="group" aria-label="Status Buttons">
                                        <?= Html::button('<span class="glyphicon glyphicon-ok"></span> Выполнено', [
                                            'class' => 'btn btn-success btn-status complete-order',
                                            'data-id' => $groupProduct->id,
                                            'title' => 'Установить статус "Выполнено"',
                                        ]) ?>
                                        <?= Html::button('<span class="glyphicon glyphicon-remove"></span> Отклонено', [
                                            'class' => 'btn btn-danger btn-status reject-order',
                                            'data-id' => $groupProduct->id,
                                            'title' => 'Установить статус "Отклонено"',
                                        ]) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($groupProduct->receipt_filename && $groupProduct->receipt_extension): ?>
                                    <?= Html::a('Скачать чек', ['download-receipt', 'id' => $groupProduct->id], ['class' => 'btn btn-primary', 'data-pjax' => '0']) ?>
                                <?php endif; ?>
                            <?php endif; ?>
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

</div>


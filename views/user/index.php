<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $search string */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id' => 'user-pjax', 'timeout' => 5000, 'enablePushState' => false]); ?>

    <!-- Форма поиска с кнопкой -->
    <div class="form-group">
        <?= Html::beginForm(['user/index'], 'get', ['data-pjax' => true, 'class' => 'form-inline']) ?>
        <div class="input-group">
            <?= Html::input('text', 'search', $search, [
                'class' => 'form-control',
                'id' => 'search-input',
                'placeholder' => 'Поиск по логину или email...',
            ]) ?>
            <span class="input-group-btn">
                    <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
                </span>
        </div>
        <?= Html::endForm() ?>
    </div>
    <?php if ($dataProvider->getCount() === 0): ?>
        <p>Нет пользователей для отображения.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($dataProvider->getModels() as $user): ?>
                <li class="list-group-item" id="user-<?= $user->id ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <strong><?= Html::encode($user->fio) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <?= Html::encode($user->login) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::encode($user->email) ?>
                        </div>
                        <div class="col-md-3">
                            <span class="label label-info"><?= Html::encode($user->getRoleLabel()) ?></span>
                            <?php if ($user->id != Yii::$app->user->id): ?> <!-- Не показываем кнопки для себя -->
                                <?php if ($user->access_permission != 2): ?>
                                    <?= Html::a('<span class="glyphicon glyphicon-star"></span>', '#', [
                                        'class' => 'btn btn-sm btn-warning assign-moderator',
                                        'data-id' => $user->id,
                                        'title' => 'Назначить модератором',
                                    ]) ?>
                                <?php else: ?>
                                    <?= Html::a('<span class="glyphicon glyphicon-star-empty"></span>', '#', [
                                        'class' => 'btn btn-sm btn-danger remove-moderator',
                                        'data-id' => $user->id,
                                        'title' => 'Снять модератора',
                                    ]) ?>
                                <?php endif; ?>
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

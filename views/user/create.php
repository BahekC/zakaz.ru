<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Устанавливаем параметр, чтобы контейнер был прозрачным
$this->params['transparent'] = true;
?>
<div class="user-create d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="registration-box p-4" style="width: 400px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); background-color: #fff;">
        <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>
<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\GroupProduct */

$this->title = 'Create Group Product';
$this->params['breadcrumbs'][] = ['label' => 'Group Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

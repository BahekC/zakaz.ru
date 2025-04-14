<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCreateForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $category array */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin([
        'id' => 'product-form',
        'options' => ['enctype' => 'multipart/form-data'],
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validationUrl' => $model->isNewRecord ? ['create'] : ['update', 'id' => $model->id],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'price')->textInput(['type' => 'number', 'min' => 0]) ?>

    <?= $form->field($model, 'count')->textInput(['type' => 'number', 'min' => 0]) ?>

    <?= $form->field($model, 'idCategory')->dropDownList($category, ['prompt' => 'Выберите категорию']) ?>

    <?= $form->field($model, 'photo')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

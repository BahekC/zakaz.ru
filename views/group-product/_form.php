<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCreateForm|app\models\Product */
/* @var $category array */

$form = ActiveForm::begin([
    'id' => 'product-form',
    'options' => ['enctype' => 'multipart/form-data']
]); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
<?= $form->field($model, 'idCategory')->dropDownList($category) ?>
<?= $form->field($model, 'price')->textInput() ?>
<?= $form->field($model, 'count')->textInput() ?>
<?= $form->field($model, 'photo')->fileInput() ?>

<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::button('Отмена', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>

<?php ActiveForm::end(); ?>

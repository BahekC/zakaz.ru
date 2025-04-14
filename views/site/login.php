<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Войти';
$this->params['breadcrumbs'][] = $this->title;

// Устанавливаем параметр, чтобы контейнер был прозрачным
$this->params['transparent'] = true;
?>
<div class="site-login d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="login-box p-4" style="width: 400px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); background-color: #fff;">
        <h1 class="text-center"><?= Html::encode($this->title) ?></h1>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
                'labelOptions' => ['class' => 'col-lg-3 control-label'],
            ],
        ]); ?>

        <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => "<div class=\"col-lg-offset-3 col-lg-9\">{input} {label}</div>\n<div class=\"col-lg-12\">{error}</div>",
        ]) ?>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
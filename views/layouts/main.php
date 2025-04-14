<?php
/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);

// Получение количества товаров в корзине
Yii::$app->name = 'ПИЛСТРОЙКОМПЛЕКТ';
$cartCount = 0;
if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isUser()) {
    $cart = \app\models\GroupProduct::findOne(['idUser' => Yii::$app->user->id, 'status' => \app\models\GroupProduct::STATUS_NEW]);
    if ($cart) {
        $cartCount = \app\models\GroupCount::find()
            ->where(['idGroup' => $cart->id, 'idUser' => Yii::$app->user->id])
            ->sum('count');
        if ($cartCount === null) {
            $cartCount = 0;
        }
    }
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script>
        // Определение базового URL приложения
        var baseUrl = "<?= Yii::$app->request->baseUrl ?>";
    </script>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap custom-warp">
    <?php
    NavBar::begin([
        'brandLabel' => '
        <div style="display:inline-block; vertical-align:middle;">
            <img src="' . Yii::$app->request->baseUrl . '/img/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
        </div>
        <div style="display:inline-block; vertical-align:middle; margin-top:10px;">' . Yii::$app->name . '</div>',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top custom-navbar',
        ],
    ]);

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Главная', 'url' => ['/site/index']],
            ['label' => 'Регистрация', 'url'=>['/user/create'],'visible'=>Yii::$app->user->isGuest],
            Yii::$app->user->isGuest ? (
            ['label' => 'Войти', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Выйти (' . Yii::$app->user->identity->fio . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            ),
            ['label'=>'Модерация Пользователей','url'=>['/user/index'],'visible'=>!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()],
            ['label'=>'Модерация Заказов','url'=>['/group-product/index'],'visible'=>!Yii::$app->user->isGuest && Yii::$app->user->identity->isModeration()],
            ['label'=>'Модерация Товаров','url'=>['/product/index'],'visible'=>!Yii::$app->user->isGuest && Yii::$app->user->identity->isModeration()],
            [
                'label' => 'Корзина' . ($cartCount > 0 ? ' (<span class="cart-count">' . $cartCount . '</span>)' : ''),
                'url' => ['/cart/index'],
                'encode' => false, // Чтобы интерпретировать HTML в label
                'visible' => !Yii::$app->user->isGuest && Yii::$app->user->identity->isUser()
            ],
            ['label'=>'Создание категорий','url'=>['/category/index'],'visible'=>!Yii::$app->user->isGuest&&Yii::$app->user->identity->isAdmin()],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container<?= isset($this->params['transparent']) && $this->params['transparent'] ? ' container-transparent' : '' ?>">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-sm-4">
                <p>&copy; ПИЛСТРОЙКОМПЛЕКТ <?= date('Y') ?></p>
            </div>
            <div class="col-sm-4">
                <p>
                    Телефоны:<br>
                    +7 123 456-78-90<br>
                    +7 987 654-32-10
                </p>
            </div>
            <div class="col-sm-4">
                <p>
                    Адрес:<br>
                    г. Москва, ул. Примерная, д. 1
                </p>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

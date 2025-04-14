<ul class="list-group">
    <?php /** @var TYPE_NAME $products */
    foreach($products as $product): ?>
        <li class="list-group-item" id="product-<?= $product->id ?>">
            <div class="row">
                <div class="col-md-2">
                    <img src="<?= $product->photoUrl ?>" class="img-thumbnail" style="max-height:100px;">
                </div>
                <div class="col-md-6">
                    <strong><?= Html::encode($product->name) ?></strong><br>
                    Категория: <?= Html::encode($product->category->name) ?><br>
                    Дата размещения: <?= Yii::$app->formatter->asDatetime($product->timestamp) ?>
                </div>
                <div class="col-md-4 text-right">
                    <a href="#" class="btn btn-sm btn-info view-product" data-id="<?= $product->id ?>" title="Просмотр">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                    <a href="#" class="btn btn-sm btn-warning edit-product" data-id="<?= $product->id ?>" title="Редактировать" style="margin-left:5px;">
                        <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="#" class="btn btn-sm btn-danger delete-product" data-id="<?= $product->id ?>" title="Удалить" style="margin-left:5px;">
                        <span class="glyphicon glyphicon-trash"></span>
                    </a>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<?php

namespace app\controllers;

use app\models\Category;
use app\models\ProductCreateForm;
use app\models\User;
use Yii;
use app\models\Product;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\web\Response;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create','update','index','view','delete'],
                'rules' => [
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isUser();
                        }
                    ],
                    [
                        'actions' => ['create','update','view','index','delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isModeration();
                        }
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Product::find()->with(['category', 'idUser0']),
            'pagination' => [
                'pageSize' => 9, // Показывать по 9 товаров на странице
            ],
            'sort' => [
                'defaultOrder' => [
                    'timestamp' => SORT_DESC,
                ],
            ],
        ]);
        $users = User::find()->select(['id','fio'])->where(['access_permission' => 2])->all();
        $users = ArrayHelper::map($users,'id','fio');
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'users'=>$users,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $category = Category::find()->all();
        $category = ArrayHelper::map($category,'id','name');
        return $this->render('view', [
            'model' => $this->findModel($id),
            'category' => $category,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, returns JSON response.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductCreateForm();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->renderAjax('_form', [
                'model' => $model,
                'category' => $this->getCategoryList(),
            ]);
        }

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->idUser = Yii::$app->user->identity->id;
            $model->photo = UploadedFile::getInstance($model,'photo');
            if ($model->validate()) {
                if ($model->photo) {
                    $fileName = md5("{$model->photo->baseName}.{$model->photo->extension}" . time());
                    $model->photo->saveAs("@app/web/uploads/{$fileName}.{$model->photo->extension}");
                    $model->photo = "{$fileName}.{$model->photo->extension}";
                }
                $model->idUser = Yii::$app->user->identity->id;
                if ($model->save(false)) { // Сохраняем без валидации, так как уже прошли валидацию
                    if (Yii::$app->request->isAjax) {
                        return ['success' => true];
                    }
                    //return $this->redirect(['view', 'id' => $model->id]);
                    return $this->redirect(['index']);
                }
            }else {
                // Посмотреть ошибки
                Yii::error($model->errors, __METHOD__);
                \yii\helpers\VarDumper::dump($model->errors, 10, true);
                exit;
            }

            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('_form', [
                    'model' => $model,
                    'category' => $this->getCategoryList(),
                ]);
            }
        }

        // Обработка стандартного обновления (не AJAX)
        $category = $this->getCategoryList();
        return $this->render('create', [
            'model' => $model,
            'category' => $category,
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, returns JSON response.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->renderAjax('_form', [
                'model' => $model,
                'category' => $this->getCategoryList(),
            ]);
        }

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $uploadedPhoto = UploadedFile::getInstance($model,'photo');
            if ($uploadedPhoto) {
                $fileName = md5("{$uploadedPhoto->baseName}.{$uploadedPhoto->extension}" . time());
                $uploadedPhoto->saveAs("@app/web/uploads/{$fileName}.{$uploadedPhoto->extension}");
                $model->photo = "{$fileName}.{$uploadedPhoto->extension}";
            }
            if ($model->validate()) {
                if ($model->save(false)) { // Сохраняем без валидации, так как уже прошли валидацию
                    if (Yii::$app->request->isAjax) {
                        return ['success' => true];
                    }
                    //return $this->redirect(['view', 'id' => $model->id]);
                    return $this->redirect(['index']);
                }
            }
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('_form', [
                    'model' => $model,
                    'category' => $this->getCategoryList(),
                ]);
            }
        }

        // Обработка стандартного обновления (не AJAX)
        $category = $this->getCategoryList();
        return $this->render('update', [
            'model' => $model,
            'category' => $category,
        ]);
    }

    /**
     * Deletes an existing Product model via AJAX.
     * If deletion is successful, returns JSON response.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $this->findModel($id)->delete();
                return ['success' => true];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Не удалось удалить товар.'];
            }
        }

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product|array|\yii\db\ActiveRecord
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::find()->with(['category', 'idUser0'])->where(['id' => $id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Возвращает список категорий для выпадающего списка.
     * @return array
     */
    protected function getCategoryList()
    {
        $categories = Category::find()->all();
        return ArrayHelper::map($categories, 'id', 'name');
    }
}

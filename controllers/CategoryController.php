<?php

namespace app\controllers;

use Yii;
use app\models\Category;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create','update','view','index'],
                'rules' => [
                    [
                        'actions' => ['create','update','view','index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin();
                        }
                    ],
                    [
                        'actions' => ['index'],
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
                    'update-status' => ['POST'],
                    'update' => ['POST'], // Разрешаем POST-запросы для обновления
                ],
            ],
        ];
    }

    /**
     * Lists all Category models with search and pagination.
     * @return mixed
     */
    public function actionIndex()
    {
        $search = Yii::$app->request->get('search');

        $query = Category::find();

        if ($search) {
            $query->where(['like', 'name', $search]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Показать по 10 категорий на странице
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Category model via AJAX or standard POST.
     * If creation is successful, returns JSON response for AJAX or redirects to 'view' for standard POST.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Category();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->save()) {
                // Возвращаем данные новой категории
                return [
                    'success' => true,
                    'id' => $model->id,
                    'name' => Html::encode($model->name),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Не удалось добавить категорию.',
                ];
            }
        }

        // Обработка стандартного создания категории (не AJAX)
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        // В случае стандартного запроса, рендерим форму создания
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Category model via AJAX.
     * If update is successful, returns JSON response.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Проверка, является ли запрос AJAX
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $newName = Yii::$app->request->post('name');
            if ($newName) {
                $model->name = $newName;
                if ($model->save(false, ['name'])) { // Сохраняем только поле 'name', без валидации
                    return ['success' => true, 'name' => $model->name];
                } else {
                    return ['success' => false, 'message' => 'Не удалось обновить название категории.'];
                }
            } else {
                return ['success' => false, 'message' => 'Не задано новое название категории.'];
            }
        }

        // Обработка стандартного обновления (не AJAX)
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Category model via AJAX.
     * If deletion is successful, returns JSON response.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        // Проверка, является ли запрос AJAX
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true];
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }
}

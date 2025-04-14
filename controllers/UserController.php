<?php

namespace app\controllers;

use app\models\RegForm;
use Yii;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','create','update','index','view','assign-moderator','remove-moderator'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['?'], // Разрешено только гостям
                    ],
                    [
                        'actions' => ['create','update','view','index','assign-moderator','remove-moderator'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin();
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'assign-moderator' => ['POST'],
                    'remove-moderator' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models with search and pagination.
     * @return mixed
     */
    public function actionIndex()
    {
        $search = Yii::$app->request->get('search');

        $query = User::find();

        if ($search) {
            $query->where(['or',
                ['like', 'login', $search],
                ['like', 'email', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Показать по 10 пользователей на странице
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
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new RegForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/site/login']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model via AJAX.
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

            $newRole = Yii::$app->request->post('role'); // ожидаем 'assign' или 'remove'
            if ($newRole === 'assign') {
                // Проверка, что пользователь не пытается изменить свою собственную роль
                if ($model->id == Yii::$app->user->id) {
                    return ['success' => false, 'message' => 'Вы не можете изменить свою собственную роль.'];
                }

                $model->access_permission = 2; // Модератор
            } elseif ($newRole === 'remove') {
                // Проверка, что пользователь не пытается изменить свою собственную роль
                if ($model->id == Yii::$app->user->id) {
                    return ['success' => false, 'message' => 'Вы не можете изменить свою собственную роль.'];
                }

                $model->access_permission = 0; // Пользователь
            } else {
                return ['success' => false, 'message' => 'Неверный параметр роли.'];
            }

            if ($model->save(false, ['access_permission'])) { // Сохраняем только поле 'access_permission' без валидации
                return [
                    'success' => true,
                    'access_permission' => $model->access_permission,
                    'role_label' => $model->getRoleLabel(),
                ];
            } else {
                return ['success' => false, 'message' => 'Не удалось обновить роль пользователя.'];
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
     * Assigns moderator role to a User via AJAX.
     * If assignment is successful, returns JSON response.
     * @return mixed
     */
    public function actionAssignModerator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        if (!$id) {
            return ['success' => false, 'message' => 'Не указан ID пользователя.'];
        }

        $model = $this->findModel($id);

        // Проверка, что администратор не изменяет свою собственную роль
        if ($model->id == Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Вы не можете изменить свою собственную роль.'];
        }

        $model->access_permission = 2; // Модератор

        if ($model->save(false, ['access_permission'])) { // Сохраняем только поле 'access_permission' без валидации
            return [
                'success' => true,
                'access_permission' => $model->access_permission,
                'role_label' => $model->getRoleLabel(),
            ];
        } else {
            return ['success' => false, 'message' => 'Не удалось назначить модератора.'];
        }
    }

    /**
     * Removes moderator role from a User via AJAX.
     * If removal is successful, returns JSON response.
     * @return mixed
     */
    public function actionRemoveModerator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        if (!$id) {
            return ['success' => false, 'message' => 'Не указан ID пользователя.'];
        }

        $model = $this->findModel($id);

        // Проверка, что администратор не изменяет свою собственную роль
        if ($model->id == Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Вы не можете изменить свою собственную роль.'];
        }

        $model->access_permission = 0; // Пользователь

        if ($model->save(false, ['access_permission'])) { // Сохраняем только поле 'access_permission' без валидации
            return [
                'success' => true,
                'access_permission' => $model->access_permission,
                'role_label' => $model->getRoleLabel(),
            ];
        } else {
            return ['success' => false, 'message' => 'Не удалось снять модератора.'];
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }
}

<?php

namespace app\controllers;

use Yii;
use app\models\GroupProduct;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Url;

/**
 * GroupProductController implements the CRUD actions for GroupProduct model.
 */
class GroupProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create','update','index','view', 'update-status', 'download-receipt'],
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isUser();
                        }
                    ],
                    [
                        'actions' => ['update','view','index', 'update-status', 'download-receipt'],
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
                    'download-receipt' => ['GET'], // Разрешаем GET-запросы для скачивания
                ],
            ],
        ];
    }

    /**
     * Lists all GroupProduct models, excluding orders with status "New".
     * Added handling of search parameter by user login and email.
     * @return mixed
     */
    public function actionIndex()
    {
        $search = Yii::$app->request->get('search');

        $query = GroupProduct::find()
            ->with('user', 'groupCounts.idProduct0') // Eager loading of related models
            ->where(['<>', 'status', GroupProduct::STATUS_NEW]);

        if ($search) {
            $query->joinWith('user')
                ->andWhere(['or',
                    ['like', 'user.login', $search],
                    ['like', 'user.email', $search],
                ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Adjust as needed
            ],
            'sort' => [
                'defaultOrder' => [
                    'timestamp' => SORT_DESC,
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
        ]);
    }

    /**
     * Displays a single GroupProduct model.
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
     * Creates a new GroupProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new GroupProduct();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing GroupProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing GroupProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Updates the status of an order.
     * Available only to moderators.
     *
     * @return mixed
     */
    public function actionUpdateStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');

        // Check for required parameters
        if (!$id || !$status) {
            return ['success' => false, 'message' => 'Необходимые параметры отсутствуют.'];
        }

        // Find the model
        $model = $this->findModel($id);

        // Check if status can be changed
        if (in_array($model->status, [GroupProduct::STATUS_COMPLETED, GroupProduct::STATUS_REJECTED])) {
            return ['success' => false, 'message' => 'Нельзя изменить статус завершенного или отклоненного заказа.'];
        }

        // Set new status
        if (!$model->setStatus($status)) {
            return ['success' => false, 'message' => 'Не удалось установить новый статус.'];
        }

        // Generate receipt if status is "Completed"
        $receiptUrl = null;
        if ($status === GroupProduct::STATUS_COMPLETED) {
            if ($model->generateReceipt()) {
                // Form the URL to download the receipt
                $receiptUrl = Url::to(['download-receipt', 'id' => $model->id], true);
            } else {
                return ['success' => false, 'message' => 'Статус обновлен, но не удалось сгенерировать квитанцию.'];
            }
        }

        return [
            'success' => true,
            'message' => 'Статус заказа успешно обновлен.',
            'receipt_url' => $receiptUrl,
        ];
    }

    /**
     * Downloads the receipt for the specified order.
     * Available only to moderators.
     *
     * @param integer $id Order ID
     * @return Response
     * @throws NotFoundHttpException if the order or file is not found
     */
    public function actionDownloadReceipt($id)
    {
        // Find the order
        $model = $this->findModel($id);

        // Check if receipt exists
        if (!$model->receipt_filename || !$model->receipt_extension) {
            throw new NotFoundHttpException('Чек для данного заказа не найден.');
        }

        // Form the file path
        $filePath = Yii::getAlias('@webroot/assets/cart/') . $model->receipt_filename . '.' . $model->receipt_extension;

        // Check if file exists
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Файл чека не найден на сервере.');
        }

        // Determine MIME type
        $mimeType = mime_content_type($filePath);
        if ($mimeType === false) {
            $mimeType = 'application/octet-stream';
        }

        // Send the file to the user with proper headers
        return Yii::$app->response->sendFile($filePath, 'Чек_' . $model->receipt_filename . '.' . $model->receipt_extension, [
            'mimeType' => $mimeType,
            'inline' => false, // Set to false to prompt download
        ]);
    }

    /**
     * Finds the GroupProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return GroupProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GroupProduct::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }
}

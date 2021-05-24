<?php


namespace app\controllers;


use app\modules\admin\controllers\RequestController;
use app\modules\admin\models\RequestSearch;
use app\modules\admin\models\Request;

class FrontController extends RequestController
{
    /**
     * Lists all Request models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RequestSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['created_by'=> \Yii::$app->user->id])->orderBy('created_at DESC '); // Выбор всех записей текущего рпользователя и сортировка

        $count = Request::find()->where(['status' => 'Решена'])->count();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'count' => $count
        ]);
    }

}
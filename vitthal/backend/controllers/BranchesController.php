<?php

namespace backend\controllers;

use Yii;
use backend\models\Branches;
use backend\models\BranchesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

class BranchesController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new BranchesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        if( Yii::$app->user->can( 'create-branch' ) ) {
            $model = new Branches();

            if ($model->load(Yii::$app->request->post())) {
                $model->branch_created_date = date('Y-m-d h:m:s');
                if($model->save())
                {
                    echo 1;
                }
                else
                {
                    echo 0;
                }
            } else {
                return $this->renderAjax('create', [
                    'model' => $model,
                ]);
            }
        }
        else {
             throw new ForbiddenHttpException('You do not have permission to create Branch', 403, null);
        }
        
    }  
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->branch_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    public function actionLists($id)
    {
        $countBranches = Branches::find()
                ->where(['companies_company_id' => $id])
                ->count();
 
        $branches = Branches::find()
                ->where(['companies_company_id' => $id])
                ->all();
 
        if($countBranches > 0 )
        {
            foreach($branches as $branch ){
                echo "<option value='".$branch->branch_id."'>".$branch->branch_name."</option>";
            }
        }
        else{
            echo "<option> - </option>";
        }
 
    }

    protected function findModel($id)
    {
        if (($model = Branches::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

<?php

namespace backend\controllers;

use Yii;
use backend\models\Companies;
use backend\models\Branches;
use backend\models\CompaniesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\web\ForbiddenHttpException;

class CompaniesController extends Controller
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
        $searchModel = new CompaniesSearch();
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
        if( Yii::$app->user->can('create-company') )
        {
            $model = new Companies();
            $branch = new Branches();

            if ($model->load(Yii::$app->request->post()) &&  $branch->load(Yii::$app->request->post())) 
            {

                // get the instance of the uploaded file 
                $imageName = $model->company_name;
                if(!empty($model->file))
                {
                    $model->file = UploadedFile::getInstance($model,'file');
                    $model->file->saveAs( 'uploads/'.$imageName.'.'.$model->file->extension );

                    // save the path in the db column
                    $model->logo =  'uploads/'.$imageName.'.'.$model->file->extension;    
                }
                
                $model->company_created_date = date('Y-m-d h:m:s');
                $model->save();

                $branch->companies_company_id = $model->company_id;
                $branch->branch_created_date = date('Y-m-d H:m:s');
                $branch->save();

                return $this->redirect(['view', 'id' => $model->company_id]);

            } else {
                return $this->render('create', [
                    'model' => $model,
                    'branch'=>$branch,
                ]);
            }
        }
        else {
             throw new ForbiddenHttpException('You do not have permission to create Company', 403, null);
        }

        
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->company_id]);
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

    protected function findModel($id)
    {
        if (($model = Companies::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

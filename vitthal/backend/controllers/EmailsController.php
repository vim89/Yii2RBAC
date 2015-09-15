<?php

namespace backend\controllers;

use Yii;
use backend\models\Emails;
use backend\models\EmailsSearch;
use yii\web\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class EmailsController extends Controller
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
        $searchModel = new EmailsSearch();
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
        $model = new Emails();

        if ($model->load(Yii::$app->request->post())) 
        {
            // upload the attachment 
            $model->attachment = UploadedFile::getInstance($model, 'attachment');

            if ( $model->attachment ) 
            {    
                $time = time();
                $model->attachment->saveAs('attachments/' .$time. '.' . $model->attachment->extension);
                $model->attachment='attachments/' .$time. '.' . $model->attachment->extension;
            }
            if( $model->attachment )
            {
                $value = Yii::$app->mailer->compose()
                ->setFrom([ 'vitthalmirji@gmail.com' => 'parasseuxVitths' ])
                ->setTo($model->receiver_email)
                ->setSubject($model->subject)
                ->setHtmlBody($model->content )
                ->attach($model->attachment)
                ->send();    
            }else
            {
                $value = Yii::$app->mailer->compose()
                ->setFrom([ 'vitthal10@yahoo.in' => 'Vitthal Mirji' ])
                ->setTo($model->receiver_email)
                ->setSubject($model->subject)
                ->setHtmlBody($model->content)
                ->send();    
            }

            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
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
        if (($model = Emails::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

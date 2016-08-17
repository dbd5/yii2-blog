<?php

namespace dongrim\blog\admin\controllers;

use dongrim\blog\models\BlogCatPos;
use dongrim\blog\models\Post;
use dongrim\blog\models\PostSearch;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use Yii;


class PostController extends Controller
{

    public function actionIndex($term = false)
    {
        $searchModel = new PostSearch();
        $req = Yii::$app->request->queryParams;
        if ($term) { $req[basename(str_replace("\\","/",get_class($searchModel)))]["term"] = $term;}
        $dataProvider = $searchModel->search($req);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Post model.
     * @param integer $id
     * @additionalParam string $format
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);

    }

    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Post();
        $model->time = date("Y-m-d H:i:s");
        $model->author_id = Yii::$app->user->id;
        $model->isdel = 0;

        if (Yii::$app->request->post())
        {
            $post = Yii::$app->request->post();
            $category = [];
            if (isset($post['Post']['category']))
            {
                $category = $post['Post']['category'];
            }

            if (is_array($post['Post']['tags']))
            {
                $post['Post']['tags'] = implode(",",$post['Post']['tags']);
            }

            $model->load($post);

            $transaction = Yii::$app->db->beginTransaction();
            try {

                if ($model->save()) {

                    $cs = BlogCatPos::deleteAll("post_id = :id",["id"=>$model->id]);

                    foreach ($category as $d)
                    {
                        $c = new BlogCatPos();
                        $c->post_id = $model->id;
                        $c->category_id = $d;
                        $c->isdel = 0;
                        $c->save();
                    }

                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                else
                {
                    $model->id = array_merge($category,[]);
                    $transaction->rollBack();
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->tags = !empty($model->tags)?explode(",",$model->tags):[];

        if (Yii::$app->request->post())
        {
            $post = Yii::$app->request->post();
            $category = [];
            if (isset($post['Post']['category']))
            {
                $category = $post['Post']['category'];
            }

            if (is_array($post['Post']['tags']))
            {
                $post['Post']['tags'] = implode(",",$post['Post']['tags']);
            }

            $model->load($post);

            $transaction = Yii::$app->db->beginTransaction();
            try {

                if ($model->save()) {

                    $cs = BlogCatPos::deleteAll("post_id = :id",["id"=>$model->id]);

                    foreach ($category as $d)
                    {
                        //$c = BlogCatPos::find()->where("post_id = :id AND category_id = :aid",["id"=>$model->id,"aid"=>intval($d)])->one();
                        //if (!$c)
                        //{
                        $c = new BlogCatPos();
                        //}
                        $c->post_id = $model->id;
                        $c->category_id = $d;
                        $c->isdel = 0;
                        $c->save();
                    }

                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                else
                {
                    $transaction->rollBack();
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->isdel = 1;
        $model->save();
        //$model->delete(); //this will true delete

        return $this->redirect(['admin']);
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
<?php
namespace app\controllers;

use app\components\Elastic;
use app\models\AppIosFlat;
use yii\data\Pagination;
use yii\web\Controller;

class ElasticController extends Controller{
    
    public function actionIndex(){
        
        $elastic = new Elastic();
        
        $index = "tutuapp-ios-zh";
        
        $response = $elastic->getIndex($index);

        return  json_encode($response);
    }

    public function actionCreateindex(){
        $elastic = new Elastic();

        $index = "tutuapp-ios-zh";

        $response = $elastic->createIndex($index);

        return  json_encode($response);
    }
    
    public function actionSearch(){
        $elastic = new Elastic();
        
        $index = "tutuapp-ios-zh";
        
        $queryString = "doe";
        
        $queryBody = $elastic->getQueryBody($queryString);
        $response = $elastic->search($queryBody, $index);
        
        return  json_encode($response);
    }
    
    public function actionBatchindexdata(){
        try {
            $query = AppIosFlat::find()->select(["id","entity_id","app_name","app_introduction"]);

            $pagination = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $query->count(),
            ]);

            $apps = $query->orderBy('id')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

            $app = $apps[0];

            $elastic = new Elastic();
            $index = "tutuapp-ios-zh";

            //foreach ($apps as $index => $app) {
//                $app= $apps[1];
//                $body = array(
//                    "entity_id"=>$app->entity_id,
//                    "app_name"=>$app->app_name,
//                    "app_introduction"=>$app->app_introduction,
//                    "app_current_newfunction"=>$app->app_current_newfunction
//                );
//
//                $id = $app->entity_id;
//
//                $response = $elastic->createDocument($index,$id,$body);
//
//                return  json_encode($response);
            //}

            foreach ($apps as $index => $app){
                $params['body'][] = [
                    'index' => [
                        '_index' => $index,
                        '_type' => '_doc',
                        '_id' => $app->entity_id
                    ]
                ];

                $params['body'][] = [
                    "entity_id"=>$app->entity_id,
                    "app_name"=>$app->app_name,
                    "app_introduction"=>$app->app_introduction,
                    "app_current_newfunction"=>$app->app_current_newfunction
                ];
            }

            $response = $elastic->bulkDocument($params);
            return  json_encode($response);

        } catch (Exception $e) {

            return $e->getMessage();

        }
        
      
    }
    
}
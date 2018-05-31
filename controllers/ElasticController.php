<?php
namespace app\controllers;

use yii\web\Controller;

use app\components\Elastic;
use app\models\AppIosFlat;
use yii\data\Pagination;

class ElasticController extends Controller{
    
    public function actionIndex(){
        
        $elastic = new Elastic();
        
        $index = "tutuapp-ios-zh";
        
        $response = $elastic->getIndex($index);

        $response = $elastic->createIndex($index);
        
//         $index = "tutuapp-ios";
        
//         $body= ["name"=>"wang","desc"=>"chao ji wang"];
        
        //$response = $elastic->createDocument($index, $body);
        
        
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
   
        
            $query = AppIosFlat::find();
            
            $pagination = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $query->count(),
            ]);
            
            $apps = $query->orderBy('id')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
            
            $app = $apps[0];
            
            return json_encode($app->attributes);
 
            
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
      
    }
    
}
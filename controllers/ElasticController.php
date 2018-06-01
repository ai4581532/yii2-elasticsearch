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
    
    public function actionDeleteindex(){
        $elastic = new Elastic();

        $index = "tutuapp-ios-zh";

        $response = $elastic->deleteIndex($index);

        return  json_encode($response);
    }
    
    public function actionGetindexmapping(){
        $elastic = new Elastic();
        
        $index = "tutuapp-ios-zh";
        
        $response = $elastic->getIndexMapping($index);
        
        return  json_encode($response);
    }
    
    public function actionSetindexmapping(){
        $elastic = new Elastic();
        
        $index = "tutuapp-ios-zh";
        
        $properties =[
            //entity_id app_name app_category_first_name  app_version app_language app_rating app_system app_current_score  app_introduction app_current_newfunction  app_free_limit
            
            'entity_id' => [
                'type' => 'integer',
                "boost"=> 1,
                //'analyzer' => 'standard'
            ],
            'app_name' => [
                'type' => 'text',
                'boost'=> 10,
                'analyzer' => 'ik_max_word'
            ],
            'app_category_first_name' => [
                'type' => 'text',
                'boost'=> 2,
                'analyzer' => 'ik_max_word'
            ],
            'app_category_first_code' => [
                'type' => 'text',
            ],
            'app_category_first_id' => [
                'type' => 'integer',
                'boost'=> 1,
            ],
            'app_introduction' => [
                'type' => 'text',
                "boost"=> 8,
                'analyzer' => 'ik_max_word'
            ],
            'app_current_newfunction' => [
                'type' => 'text',
                'boost'=> 6,
                'analyzer' => 'ik_max_word'
            ],
            
        ];
        
        $response = $elastic->setIndexMapping($index,$properties);
        
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
            
            $elastic = new Elastic();
            
            $rows = 20000;
 
            $count= $rows/5;
            
            for($i=0; $i<$count; $i++) {
                
                //每次循环建立5条循环
                $elastic->batchIndexData($i);
                
            }
            
            //return  json_encode($response);

            return "ok";
            
        } catch (\Exception $e) {

            return $e->getMessage();

        }
        
      
    }
    
}
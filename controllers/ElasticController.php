<?php
namespace app\controllers;

use yii\web\Controller;

use app\components\elastic;

class ElasticController extends Controller{
    
    public function actionIndex(){
        $elastic = new Elastic();
        
        $index = "*";
        $queryString = "doe";
        
        $queryBody = $elastic->getQueryBody($queryString);
        
//         $response = $elastic->search($queryBody, $index);
        
//         $response = $elastic->getIndex($index);
        
        $index = "tutuapp-ios";
        
        $body= ["name"=>"wang","desc"=>"chao ji wang"];
        
        $response = $elastic->createDocument($index, $body);
        
        $params['body'][] = [
            'index' => [
                '_index' => 'my_index',
                '_type' => 'my_type',
            ]
        ];
        
        $params['body'][] = [
            'my_field' => 'my_value',
            'second_field' => 'some more values'
        ];
        
        
        return  json_encode($response);
    }
    
    
}
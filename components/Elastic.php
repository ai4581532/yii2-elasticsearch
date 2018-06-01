<?php

namespace app\components;

use Yii;
use Elasticsearch\ClientBuilder;
 

/**
 * Elastic组件类
 * 
 * @author charley.wang
 *
 */
class Elastic {
    
    private static $client;
    
    
    public function __construct(){
        
        $esParam = Yii::$app->params['elastic'];
        
        self::$client = ClientBuilder::fromConfig($esParam);
    }
    
    /**
     * 获取client
     * @return \Elasticsearch\Client
     */
    public function getClient(){
        return self::$client;        
    }
    
    /**
     * 创建索引      
     */
    public function createIndex($index,$properties=array(),$type="_doc"){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        if(empty($properties)){
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
            
        }
        
        $params = [
            'index' => $index,
            'body' => [
//                 'settings' => [
//                     'number_of_shards' => 3,
//                     'number_of_replicas' => 3
//                 ],
                'mappings' => [
                    $type => [
                        'properties' => $properties
                    ]
                ]
            ]
        ];
 
        try {
            $response = $this->getClient()->indices()->create($params);
            $result["data"] = $response;
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
        
    }
    
    /**
     * 删除索引 
     */
    public function deleteIndex($index){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = ['index' => $index];
        
        try {
            $response = $this->getClient()->indices()->delete($params);
            $result["data"] = $response;
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * 获取索引
     * @return array
     */
    public function getIndex($index){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = ['index' => $index];
        
        try {
            $response = $this->getClient()->indices()->get($params);
            $result["data"] = $response;
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
            
        return $result;
    }
    
    /**
     * 获取索引mapping 
     * @param unknown $index
     * @return boolean[]|string[]|NULL[]|unknown[]
     */
    public function getIndexMapping($index){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = ['index' => $index];
        
        try {
            $response = $this->getClient()->indices()->getMapping($params);
            
            $result["data"] = $response;
            
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * 设置索引mapping
     * 
     * @param string $index
     * @param array $properties
     * @param string $type
     * @return array
     */
    public function setIndexMapping($index,$properties,$type="_doc"){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = [
            'index' => 'my_index',
            'type' => $type,
            'body' => [
                $type => [
 
                    'properties' => $properties
                ]
            ]
        ];
        
        try {
            $response = $this->getClient()->indices()->putMapping($params);
            
            $result["data"] = $response;
            
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * 获取索引列表
     */
    public function getIndexList(){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = ['index' => '*'];
        
        try {
            $response = $this->getClient()->indices()->get($params);
            
            $result["data"] = $response;
            
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }

    /**
     * 创建文档
     * @param $index
     * @param $id
     * @param array $body
     * @param string $type
     * @return array
     */
    public function createDocument($index, $id, $body, $type = "_doc"){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = [
            'index' => $index,
            'type' => $type,
            'id' => $id,
            'body' => $body
        ];
        
        try {
            
            $response = $this->getClient()->index($params);
            $result["data"] = $response;
            
        } catch (\Exception $e) {

            $result["status"]=false;
            $result["message"]=$e->getMessage();

        }
        
        return $result;
    }

    /**
     * 更新文档
     * @param $index
     * @param $id
     * @param $body
     * @param string $type
     * @return array
     */
    public function updateDocument($index, $id, $body, $type = "_doc"){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = [
            'index' => $index,
            'type' => $type,
            'id' => $id,
            'body' => [
                'doc' => $body
            ]
        ];
        
        try {
            
            $response = $this->getClient()->update($params);
            $result["data"] = $response;
            
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * 删除文档 
     */
    public function deleteDocument($index,$id,$type="_doc"){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = [
            'index' => $index,
            'type' => $type,
            'id' => $id
        ];
        
        try {
            $response = $this->getClient()->delete($params);
            $result["data"] = $response;
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }

    /**
     * 获取document
     */
    public function getDocument($index,$id,$type="_doc"){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        $params = [
            'index' => $index,
            'type' => $type,
            'id' => $id
        ];
        
        try {
            $response = $this->getClient()->get($params);
            $result["data"] = $response;
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }
        
        return $result;
    }

    public function bulkDocument($params){
        $result = array("status"=>true,"message"=>"success","data"=>"");

        try {
            $response = $this->getClient()->bulk($params);
            $result["data"] = $response;
        } catch (\Exception $e) {
            $result["status"]=false;
            $result["message"]=$e->getMessage();
        }

        return $result;

    }

    /**
     * 获取搜索body
     * @param string $queryString 搜索词/语句
     * @param array $queryFileds  搜索字段
     * @param string $queryType 搜索类型
     * @return NULL|array
     */
    public function getQueryBody($queryString,$queryFileds=array(),$queryType="simple_query_string"){
        $query =[];
        
        if(empty($queryString)){
            return null;
        }
        
        if(empty($queryFileds)){
            $queryFileds = ["app_name","app_introduction","app_current_newfunction"];

            //entity_id app_name app_category_first_name  app_version app_language app_rating app_system app_current_score  app_introduction app_current_newfunction  app_free_limit
        }
        
        switch ($queryType){
            case 'multi_match':
                $query = [
                    "multi_match" => [
                        "query" => $queryString,
                        "fields" => $queryFileds
                    ]
                ];
                
                break;
            case 'query_string':
                $query = [
                    "query_string" => [
                        "query" => $queryString,
                        "fields" => $queryFileds,
                        //"analyzer" =>,
                    ]
                ];
                
                break;
            default:
                $query = [
                    "simple_query_string"=>[
                        "query" => $queryString,
                        "fields" => $queryFileds,
                        //"analyzer" =>,
                    ]
                ];
                
        }
        
        return $query;
    }
    

    /**
     * 搜索
     * 
     * @param array $queryBody 搜索主体参数
     * @param string $index 索引名称
     * @param string $type doc分组
     * @param array $sourceFileds 返回搜索的源字段
     * @param array $page 分页参数
     * @param array $order 排序参数
     * @return array
     * 
     * $sourceFileds = ["account_number", "balance"];
     * $page = ["pageNum"=>0,"pageSize" =>10];
     * $order = ["balance"=>["order"=>"desc"]];
     * 
     */
    public function search($queryBody, $index, $type='_doc', $sourceFileds=array(), $page=array(), $order=array()){
        
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }
        
        if(empty($page)){
            $pages["pageNum"] = 0;
            $pages["pageSize"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,
                
                "_source"=>$sourceFileds,
                
                "from"=>$pages["pageNum"],
                
                "size"=>$pages["pageSize"],
                
                'sort'=>$order
                
            ]
        ];
        
        try {
            
            $result["data"] = $this->getClient()->search($params);
            
        } catch (\Exception $e) {
            
            $result["status"]=false;
            
            $result["message"]=$e->getMessage();
            
        }
        
        return $result;
        
    }
    
    /**
     * 聚合搜索 
     */
    public function searchGroup(){
        
    }
    
    public function batchIndexData($page=0){
        
        $filedMap = [
            "id"=>"id",
            "entity_id"=>"entity_id",
            "app_name"=>"app_name",
            "app_category_first_name"=>"app_category_first_name",
            "app_category_first_code"=>"app_category_first_code",
            "app_category_first_id"=>"app_category_first_id",
            "app_introduction"=>"app_introduction",
        ];
        
        //查询上线显示的app
        $query = AppIosFlat::find()->select(array_keys($filedMap))->where();
        
        $pagination = new Pagination([
            'page' => $page,
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);
        
        $apps = $query->orderBy('id')
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();
        
        $elastic = new Elastic();
        $index = "tutuapp-ios-zh";
        
        $params = array();
        
        foreach ($apps as $i => $app){
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
                
                "app_category_first_name"=>$app->app_category_first_name,
                "app_category_first_code"=>$app->app_category_first_code,
                "app_category_first_id"=>$app->app_category_first_id,
                
                "app_introduction"=>$app->app_introduction,
                "app_current_newfunction"=>$app->app_current_newfunction
            ];
        }
        
        $response = $elastic->bulkDocument($params);
        
        return $response;
    }
    
}
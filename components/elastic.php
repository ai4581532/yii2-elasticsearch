<?php

namespace app\components;

use Yii;
use Elasticsearch\ClientBuilder;
 

class elastic {
    
    private static $client;
    
    public function __construct(){
        
        $esParam = Yii::$app->params['elastic'];
        
        self::$client = ClientBuilder::fromConfig($esParam);
    }
    
    public function getClient(){
        return self::$client;        
    }
    
    /**
     * 创建索引      
     */
    public function createIndex(){
        
    }
    
    /**
     * 删除索引 
     */
    public function deleteIndex(){
        
    }
    
    /**
     * 列出index
     */
    public function getIndexList(){
        
    }
    
    /**
     * 创建文档 
     */
    public function createDocument(){
        
    }
    
    /**
     * 更新文档 
     */
    public function updateDocument(){
        
    }
    
    /**
     * 删除文档 
     */
    public function deleteDocument(){
        
    }
    
    /**
     * 普通搜索
     * 
     * 可指定indx
     * 可分页
     * 可排序
     * 可指定返回字段
     * 
     * $fileds = array("account_number", "balance");
     * $order = ["balance"=>["order"=>"desc"]];
     *  
     */
    public function search($index,$fileds,$page,$order,$type='_doc'){
        $result = array("status"=>true,"message"=>"success","data"=>"");
        
        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
        }
        
        if(empty($fileds)){
            $fileds = array();
        }
        
        if(empty($page)){
            $pages["pageNum"] = 0;
            $pages["pageSize"] = 10;
        }
        
        if(empty($order)){
            $order = array();
        }
        
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => [
                    'match' => [
                        'firstname' => 'Price'
                    ]
                ],
                
                "_source"=>$fileds,
                
                "from"=>$pages["pageNum"]?$pages["pageNum"]:10,
                
                "size"=>$pages["pageSize"]?$pages["pageSize"]:10,
                
                'sort'=>$order
                
            ]
        ];
        
        
        try {
            
            $result["data"] = $this->getClient()->search($params);
            
        } catch (\Exception $e) {
            
            $result["status"]=false;
            
            $result["message"]="param index is null!";
            
        }
        
        return $result;
        
    }
    
    /**
     * 聚合搜索 
     */
    public function searchGroup(){
        
    }
}
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

    const TUTUAPP_IOS_ZH = "tutuapp-ios-zh";
    const TUTUAPP_IOS_EN = "tutuapp-ios-en";
    const TUTUAPP_SEARCH_LOG = "tutuapp-search-log";

    const TUTUAPP_IOS_PROPS = [
        'entity_id' => [
            'type' => 'integer',
            "boost"=> 1,
            //'analyzer' => 'standard'
        ],
        'app_name' => [
            'type' => 'text',
            'fields'=> [
                'keyword'=>[
                    'type'=> 'keyword'
                ],
                'english'=>[
                    'type'=> 'text',
                    'analyzer'=> 'english'
                ],
            ],
            'boost'=> 8,
            'analyzer' => 'ik_max_word'
        ],
        'app_category_first_name' => [
            'type' => 'text',
            'boost'=> 2,
            'analyzer' => 'ik_max_word'
        ],
        'app_category_first_code' => [
            'type' => 'keyword',
        ],
        'app_category_first_id' => [
            'type' => 'integer',
            'boost'=> 1,
        ],
        'app_introduction' => [
            'type' => 'text',
            'fields'=> [
                'english'=>[
                    'type'=> 'text',
                    'analyzer'=> 'english'
                ],
            ],
            'boost'=> 7,
            'analyzer' => 'ik_max_word'
        ],
        'app_current_newfunction' => [
            'type' => 'text',
            'fields'=> [
                'english'=>[
                    'type'=> 'text',
                    'analyzer'=> 'english'
                ],
            ],
            'boost'=> 6,
            'analyzer' => 'ik_max_word'
        ],
        'app_name_we' => [
            'type' => 'text',
            'fields'=> [
                'english'=>[
                    'type'=> 'text',
                    'analyzer'=> 'english'
                ],
            ],
            'boost'=> 10,
            'analyzer' => 'ik_max_word'
        ],
        'apptype' => [
            'type' => 'integer',
        ],
        'update_date' => [
            'type' => 'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
        'create_date' => [
            'type' => 'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],

        'week_download_count' => [
            'type' => 'integer',
            'boost'=> 8,
        ],
        'month_download_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'year_download_count' => [
            'type' => 'integer',
            'boost'=> 2,
        ],
        'week_view_count' => [
            'type' => 'integer',
            'boost'=> 8,
        ],
        'month_view_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'year_view_count' => [
            'type' => 'integer',
            'boost'=> 2,
        ],
        'comment_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'download_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'score_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'look_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'favorite_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
        'share_count' => [
            'type' => 'integer',
            'boost'=> 5,
        ],
    ];

    const TUTUAPP_SEARCH_LOG_PROPS =[

    ];

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

    public function getIndexName($lang,$platform){
        switch($lang){
            case "zh":
                return self::TUTUAPP_IOS_ZH;
                break;
            case "zh-cn":
                return self::TUTUAPP_IOS_ZH;
                break;
            case "en":
                return self::TUTUAPP_IOS_EN;
                breack;
            default:
                return self::TUTUAPP_IOS_EN;
        }
    }

    /**
     * 创建索引
     */
    public function createIndex($index,$properties=[],$type="_doc"){
        $result = ["status" => true,"message"=>"success","data"=>""];

        if(empty($properties)){
            $properties = self::TUTUAPP_IOS_PROPS;
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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
    public function setIndexMapping($index,$properties=[],$type="_doc"){
        $result = ["status" => true,"message"=>"success","data"=>""];

        if(empty($properties)){
            $properties = self::TUTUAPP_IOS_PROPS;
        }

        $params = [
            'index' => $index,
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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
        $result = ["status" => true,"message"=>"success","data"=>""];

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
     * @param $index
     * @param $id
     * @param string $type
     * @return array
     */
    public function getDocument($index,$id,$type="_doc"){
        $result = ["status" => true,"message"=>"success","data"=>""];

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

    /**
     * 批量创建更新document
     * @param $params
     * @return array
     */
    public function bulkDocument($params){
        $result = ["status" => true,"message"=>"success","data"=>""];

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
    public function getQueryBody($queryString,$queryFileds=[],$queryType="simple_query_string"){
        $query =[];

        if(empty($queryString)){
            return null;
        }

        if(empty($queryFileds)){
            $queryFileds = ["app_name","app_name_we","app_introduction","app_current_newfunction","app_category_first_name"];
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
     * @param array $sourceFiled 返回搜索的源字段
     * @param array $pages 分页参数
     * @param array $order 排序参数
     * @return array
     *
     * $sourceFileds = ["account_number", "balance"];
     * $pages = ["page"=>0,"pageCount" =>10];
     * $order = ["balance"=>["order"=>"desc"]];
     *
     */
    public function search($queryBody, $index, $type='_doc', $sourceFiled=[], $pages=[], $order=[]){

        $result = ["status" => true,"message"=>"success","data"=>""];

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 0;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>$pages["page"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];

        try {

            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealHitsResponse($response);

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

    /**
     * @param $lang
     * @param string $platform
     * @param array $pages
     * @param null $index
     * @param string $type
     * @return array
     */
    public function searchHotWord($lang, $platform='ios', $pages=[]){
        $result = array("status" => true,"message"=>"success","data"=>"");

        $index = self::TUTUAPP_SEARCH_LOG;

        $sourceFiled = [];

        $queryBody = [
            "size"=> 0,
            "aggs"=> [
                "group_by_queryText"=> [
                    "terms"=> [
                        "field"=> "queryText.keyword"
                    ]
                ]
            ]
        ];

        $order = [];

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 0;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => "_doc",
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>$pages["page"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {

            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealAggregationResponse($response,'queryText');

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=$e->getMessage();

        }

        return $result;
    }

    /**
     *
     * @param $lang
     * @param string $platform
     * @param array $pages
     * @param null $index
     * @param string $type
     * @return array
     */
    public function searchRank($lang, $platform='ios',$type='app', $pages=[]){
        $result = array("status" => true,"message"=>"success","data"=>"");

        $index = "";
        $sourceFiled = [];
        $queryBody = [];
        $order = [];

        if($type=="global"){

        }else{
            $index = $this->getIndexName($lang,$platform);

            $sourceFiled = [];

            $queryBody = ["match_all" => new \stdClass()];

            $queryBody = ["bool" =>
                ["must"=>[
                    [ 'match' => [ 'apptype' => $type=='app'?1:0 ] ]
                ]
                ]
            ];

            $order = ["download_count" => ["order"=>"desc"]];
        }

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 0;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => "_doc",
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>$pages["page"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {

            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealHitsResponse($response);

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=$e->getMessage();

        }

        return $result;
    }

    public function searchRelatedKeys($queryString, $lang, $platform="ios", $pages=[], $order=[]){
        $result = ["status" => true,"message"=>"success","data"=>""];

        $type='_doc';

        $index = $this->getIndexName($lang,$platform);

        $sourceFiled = [];
        $queryFileds = ["app_name","app_name_we"];

        $queryBody = $this->getQueryBody($queryString,$queryFileds);

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 0;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>$pages["page"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {
            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealHitsResponse($response);

            //return $response;

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=$e->getMessage();

        }

        return $result;
    }

    public function searchByRelatedKey($key,$lang,$platform="ios", $pages=[], $order=[]){
        $result = ["status" => true,"message"=>"success","data"=>""];

        $type='_doc';

        $index = $this->getIndexName($lang,$platform);

        $sourceFiled = [];

        $queryFileds = ["app_name","app_name_we","app_introduction","app_current_newfunction"];
        $queryBody = $this->getQueryBody($key,$queryFileds);

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 0;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>$pages["page"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {
            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealHitsResponse($response);

            //return $response;

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=json_decode($e->getMessage());

        }

        return $result;
    }

    /**
     * @param $lang
     * @param $categoryCode
     * @param string $platform
     * @param array $pages
     * @param array $order
     * @return array
     */
    public function searchByCategoryCode($categoryCode, $lang, $platform="ios", $pages=[], $order=[]){
        $result = ["status" => true,"message"=>"success","data"=>""];

        $type='_doc';

        $index = $this->getIndexName($lang,$platform);

        $sourceFiled = [];

        $queryBody = ["bool" =>
            ["must"=>[
                [ 'match' => [ 'app_category_first_code' => $categoryCode ] ]
            ]
            ]
        ];

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 0;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>$pages["page"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {
            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealHitsResponse($response);

            //return $response;

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=$e->getMessage();

        }

        return $result;
    }

    /**
     * @param $apps
     * @param $fileds
     * @param string $idName
     * @param string $index
     * @return array
     */
    public function batchIndexData($apps, $fileds, $idName="entity_id", $index = "tutuapp-ios-zh"){
        $params = ['body' => []];

        foreach ($apps as $i => $app){
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                    '_type' => '_doc',
                    '_id' => $app[$idName]
                ]
            ];

            $bodyArray = [];

            foreach ($fileds as $filed){
                $bodyArray[$filed]= $app[$filed];
            }

            $params['body'][] = $bodyArray;
        }

        //return $params;
        return $this->bulkDocument($params);
    }

    /**
     * 处理hits格式的响应
     * @param $hits
     * @return array
     */
    public function dealHitsResponse($response){
        $list = [];
        if($response["hits"]){
            $hits = $response["hits"]["hits"];
            foreach($hits as $index=>$one){
                $list[]=$one["_source"];
            }
        }
        return $list;
    }

    /**
     * 处理聚合格式的响应
     * @param $response
     * @param $groupName
     * @return array
     */
    public function dealAggregationResponse($response, $groupName){
        $list = [];
        if($response["aggregations"]){
            $buckets = $response["aggregations"]["group_by_".$groupName]["buckets"];

        }
        return $list;
    }

}
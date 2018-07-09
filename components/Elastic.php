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
     * 获取索引名称
     * @param $lang
     * @param $platform
     * @return string
     */
    public function getIndexName($lang, $platform){
        switch($lang){
            case "zh":
                return IndexConstant::TUTUAPP_IOS_ZH;
                break;
            case "zh-cn":
                return IndexConstant::TUTUAPP_IOS_ZH;
                break;
            case "en":
                return IndexConstant::TUTUAPP_IOS_ZH;
                breack;
            default:
                return IndexConstant::TUTUAPP_IOS_ZH;
        }
    }

    /**
     * 创建索引
     */
    public function createIndex($index,$properties=[],$type="_doc"){
        $result = ["status" => true,"message"=>"success","data"=>""];

        if(empty($index)){
            $result["status"] = false;
            $result["message"] = "index is empty";
        }

        if(empty($properties)){
            $result["status"] = false;
            $result["message"] = "properties is empty";
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
     * @param $index
     * @return array
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
     * @param $index
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
     * @param string $index
     * @return array
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
            $properties = IndexConstant::TUTUAPP_IOS_PROPS;
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
     * @param $index
     * @param $id
     * @param string $type
     * @return array
     */
    public function deleteDocument($index, $id, $type="_doc"){
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

        if(empty($queryString)&&$queryType!="match_all"){
            return $query;
        }

        if(empty($queryFileds)){
            $queryFileds = ["app_name","app_name_we","app_introduction","app_current_newfunction",
                "app_name.english","app_name_we.english","app_introduction.english","app_current_newfunction.english",];
        }

        switch ($queryType){
            case 'match_all':
                $query = [
                    "match_all"=>new \stdClass(),
                ];
                break;
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

    public function getQueryParams($index,$body,$type='_doc'){
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => $body
        ];
        return $params;
    }

    public function getAggBody($aggName,$field,$size,$filter){
        $body = [
            'size'=>0,
            'query'=>[
                'bool'=>[
                    'must'=>[
                        "match_all"=>new \stdClass(),
                    ],
                    'filter'=>$filter
                ]
            ],
            'aggs'=> [
                $aggName=>[
                    'terms'=>[
                        'field'=>$field,
                        'size'=>$size
                    ]
                ]
            ]
        ];
        return $body;
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
            $pages["page"] = 1;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

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

    public function searchAll($index, $type='_doc', $sourceFiled=[], $order=[]){
        $result = ["status" => true,"message"=>"success","data"=>""];

        $queryBody = [
            "match_all"=>new \stdClass(),
        ];

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

//        if(empty($pages)){
//            $pages["page"] = 1;
//            $pages["pageCount"] = 10;
//        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                //"from"=>($pages["page"]-1)*$pages["pageCount"],

                //"size"=>$pages["pageCount"],

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
     * @return array
     */
    public function searchHotWord($lang, $platform='ios', $pages=[]){
        $result = array("status" => true,"message"=>"success","data"=>"");

        $index = IndexConstant::TUTUAPP_SEARCH_LOG;

        $sourceFiled = [];

        $queryBody = [
            "size"=> 1,

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
            $pages["page"] = 1;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => "_doc",
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {

            $response = $this->getClient()->search($params);

            $result["data"] = $this->dealAggregationResponse($response,'group_by_queryText');

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

            $sourceFiled = ["entity_id","app_name","download_count"];

            $queryBody = [
                "match_all"=>new \stdClass(),
            ];

            if(!empty($type)){
                $queryBody = ["bool" =>
                    ["must"=>[
                        [ 'match' => [ 'apptype' => $type=='app'?1:2 ] ]
                    ]
                    ]
                ];
            }

            $order = ["download_count" => ["order"=>"desc"]];
        }

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 1;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => "_doc",
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

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

        $sourceFiled = ["entity_id","app_name","app_name_we"];
        $queryFileds = ["app_name^2","app_name_we^3","app_name.english^2","app_name_we.english^3"];

        $queryBody = $this->getQueryBody($queryString,$queryFileds);

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 1;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

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

        $sourceFiled = ["entity_id"];

        $queryFileds = ["app_name","app_name.english","app_name_we","app_name_we.english","app_introduction","app_introduction.english"];
        $queryBody = $this->getQueryBody($key,$queryFileds);

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 1;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {
            $response = $this->getClient()->search($params);

            $result["data"]=[
                "dataList" => $this->dealHitsResponse($response),
                "pageInfo"=>[
                    "totalPage"=>ceil($response["hits"]["total"]/$pages["pageCount"]),
                    "totalNum"=> $response["hits"]["total"],
                    "pageCount"=> $pages["pageCount"],
                    "page"=>$pages["page"]
                ]
            ];

            //return $response;

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=json_decode($e->getMessage());

        }

        return $result;
    }

    public function searchMoreRelatedApp($appId, $appName, $lang, $platform="ios", $pages=[], $order=[]){
        $result = ["status" => true,"message"=>"success","data"=>""];

        $type='_doc';

        $index = $this->getIndexName($lang,$platform);

        $sourceFiled = ["entity_id"];

        $queryFileds = ["app_name","app_name.english","app_name_we","app_name_we.english","app_introduction","app_introduction.english","app_current_newfunction","app_current_newfunction.english"];
        $queryBody = $this->getQueryBody($appName,$queryFileds);

        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 1;
            $pages["pageCount"] = 8;
        }

        if(empty($order)){
            $order = ["download_count" => ["order"=>"desc"],"_score" => ["order"=>"desc"]];
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

                "size"=>$pages["pageCount"],

                'sort'=>$order

            ]
        ];
        //return $params;
        try {
            $response = $this->getClient()->search($params);

            $list = $this->dealHitsResponse($response);

            //如果没有相关联的更多下载则获取排行前几的应用
            if(empty($list)){
                $response = $this->searchRank($lang, $platform,null, $pages);
                $list = $response["data"];
            }

            $entityIdList = [];
            foreach ($list as $index=>$one){
                if($one["entity_id"]==$appId){
                    continue;
                }
                $entityIdList[]=$one["entity_id"];
            }

            $result["data"]= $entityIdList;

            //return $response;

        } catch (\Exception $e) {

            $result["status"]=false;

            $result["message"]=json_decode($e->getMessage());

        }

        return $result;
    }

    /**
     * 通过分类获code取应用
     * @param $lang
     * @param $firstCategoryCode
     * @param $categoryCode
     * @param string $platform
     * @param array $pages
     * @param array $order
     * @return array
     */
    public function searchByCategoryCode($firstCategoryCode, $categoryCode, $lang, $platform="ios", $pages=[], $order=[]){
        $result = ["status" => true,"message"=>"success","data"=>""];

        $type='_doc';
        $index = $this->getIndexName($lang,$platform);

        $sourceFiled = ["entity_id"];

        $queryBody = ["bool" =>
            ["must"=>[
                [ 'match' => [ 'app_category_first_code' => $firstCategoryCode ] ]
            ]
            ]
        ];

        if(!empty($categoryCode)){
            $queryBody["bool"]["must"][]=[ 'match' => [ 'app_category_code' => $categoryCode ] ];
        }
        //return $queryBody;
        if(empty($index)){
            $result["status"]=false;
            $result["message"]="param index is null!";
            return $result;
        }

        if(empty($pages)){
            $pages["page"] = 1;
            $pages["pageCount"] = 10;
        }

        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => $queryBody,

                "_source"=>$sourceFiled,

                "from"=>($pages["page"]-1)*$pages["pageCount"],

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
     * @param $response
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
    public function dealAggregationResponse($response, $aggName){
        $list = [];
        if($response["aggregations"]){
            $buckets = $response["aggregations"][$aggName]["buckets"];
            $list = $buckets;
        }
        return $list;
    }

}

class IndexConstant {

    const TUTUAPP_IOS_ZH = "tutuapp_ios_zh";
    const TUTUAPP_IOS_ZH_TW = "tutuapp_ios_zh_tw";
    const TUTUAPP_IOS_EN = "tutuapp_ios_en";
    const TUTUAPP_IOS_KO = "tutuapp_ios_ko";
    const TUTUAPP_IOS_AR = "tutuapp_ios_ar";
    const TUTUAPP_IOS_JA = "tutuapp_ios_ja";

    const TUTUAPP_SEARCH_LOG = "tutuapp_search_log";
    const TUTUAPP_VIEW_LOG = "tutuapp_view_log";
    const TUTUAPP_DOWNLOAD_LOG = "tutuapp_download_log";
    const TUTUAPP_INSTALL_LOG = "tutuapp_install_log";
    const TUTUAPP_SHARE_LOG = "tutuapp_share_log";
    const TUTUAPP_DAY_LOG = "tutuapp_day_log";

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
        'app_category_name' => [
            'type' => 'text',
            'boost'=> 2,
            'analyzer' => 'ik_max_word'
        ],
        'app_category_code' => [
            'type' => 'keyword',
        ],
        'app_category_id' => [
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

    const TUTUAPP_VIEW_LOG_PROPS=[
        'appId'=>[
            'type'=>'integer',
        ],
        'dateTime'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
        'tutuVersion'=>[
            'type'=>'keyword'
        ],
        'tutuBundleId'=>[
            'type'=>'keyword'
        ],
        'tutuUid'=>[
            'type'=>'keyword'
        ],
        'channel'=>[
            'type'=>'keyword'
        ],
        'lang'=>[
            'type'=>'keyword'
        ],
        'platform'=>[
            'type'=>'keyword'
        ],
        'identifier'=>[
            'type'=>'keyword',
        ],
        'deviceType'=>[
            'type'=>'keyword',
        ],
        'deviceMode'=>[
            'type'=>'keyword',
        ],
        'system'=>[
            'type'=>'keyword',
        ],
        'screen'=>[
            'type'=>'keyword',
        ],
        'network'=>[
            'type'=>'keyword',
        ],
        'ip'=>[
            'type'=>'keyword',
        ],
        'countryCode'=>[
            'type'=>'keyword',
        ],
        'countryName'=>[
            'type'=>'keyword',
        ],
        'region'=>[
            'type'=>'keyword',
        ],
        'city'=>[
            'type'=>'keyword',
        ]
    ];

    const TUTUAPP_DOWNLOAD_LOG_PROPS=[
        'appId'=>[
            'type'=>'integer',
        ],
        'dateTime'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
        'tutuVersion'=>[
            'type'=>'keyword'
        ],
        'tutuBundleId'=>[
            'type'=>'keyword'
        ],
        'tutuUid'=>[
            'type'=>'keyword'
        ],
        'channel'=>[
            'type'=>'keyword'
        ],
        'lang'=>[
            'type'=>'keyword'
        ],
        'platform'=>[
            'type'=>'keyword'
        ],
        'identifier'=>[
            'type'=>'keyword',
        ],
        'deviceType'=>[
            'type'=>'keyword',
        ],
        'deviceMode'=>[
            'type'=>'keyword',
        ],
        'system'=>[
            'type'=>'keyword',
        ],
        'screen'=>[
            'type'=>'keyword',
        ],
        'network'=>[
            'type'=>'keyword',
        ],
        'ip'=>[
            'type'=>'keyword',
        ],
        'countryCode'=>[
            'type'=>'keyword',
        ],
        'countryName'=>[
            'type'=>'keyword',
        ],
        'region'=>[
            'type'=>'keyword',
        ],
        'city'=>[
            'type'=>'keyword',
        ]
    ];

    const TUTUAPP_INSTALL_LOG_PROPS=[
        'appId'=>[
            'type'=>'integer',
        ],
        'dateTime'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
        'tutuVersion'=>[
            'type'=>'keyword'
        ],
        'tutuBundleId'=>[
            'type'=>'keyword'
        ],
        'tutuUid'=>[
            'type'=>'keyword'
        ],
        'channel'=>[
            'type'=>'keyword'
        ],
        'lang'=>[
            'type'=>'keyword'
        ],
        'platform'=>[
            'type'=>'keyword'
        ],
        'identifier'=>[
            'type'=>'keyword',
        ],
        'deviceType'=>[
            'type'=>'keyword',
        ],
        'deviceMode'=>[
            'type'=>'keyword',
        ],
        'system'=>[
            'type'=>'keyword',
        ],
        'screen'=>[
            'type'=>'keyword',
        ],
        'network'=>[
            'type'=>'keyword',
        ],
        'ip'=>[
            'type'=>'keyword',
        ],
        'countryCode'=>[
            'type'=>'keyword',
        ],
        'countryName'=>[
            'type'=>'keyword',
        ],
        'region'=>[
            'type'=>'keyword',
        ],
        'city'=>[
            'type'=>'keyword',
        ]
    ];

    const TUTUAPP_SHARE_LOG_PROPS=[
        'appId'=>[
            'type'=>'integer',
        ],
        'shareTo'=>[
            'type'=>'keyword',
        ],
        'dateTime'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
        'tutuVersion'=>[
            'type'=>'keyword'
        ],
        'tutuBundleId'=>[
            'type'=>'keyword'
        ],
        'tutuUid'=>[
            'type'=>'keyword'
        ],
        'channel'=>[
            'type'=>'keyword'
        ],
        'lang'=>[
            'type'=>'keyword'
        ],
        'platform'=>[
            'type'=>'keyword'
        ],
        'identifier'=>[
            'type'=>'keyword',
        ],
        'deviceType'=>[
            'type'=>'keyword',
        ],
        'deviceMode'=>[
            'type'=>'keyword',
        ],
        'system'=>[
            'type'=>'keyword',
        ],
        'screen'=>[
            'type'=>'keyword',
        ],
        'network'=>[
            'type'=>'keyword',
        ],
        'ip'=>[
            'type'=>'keyword',
        ],
        'countryCode'=>[
            'type'=>'keyword',
        ],
        'countryName'=>[
            'type'=>'keyword',
        ],
        'region'=>[
            'type'=>'keyword',
        ],
        'city'=>[
            'type'=>'keyword',
        ]
    ];

    const TUTUAPP_SEARCH_LOG_PROPS=[
        'queryText'=>[
            'type'=>'keyword',
        ],
        'resultNum'=>[
            'type'=>'integer'
        ],
        'pages'=>[

        ],
        'clickApp'=>[

        ],
        'dateTime'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
        'tutuVersion'=>[
            'type'=>'keyword'
        ],
        'tutuBundleId'=>[
            'type'=>'keyword'
        ],
        'tutuUid'=>[
            'type'=>'keyword'
        ],
        'channel'=>[
            'type'=>'keyword'
        ],
        'lang'=>[
            'type'=>'keyword'
        ],
        'platform'=>[
            'type'=>'keyword'
        ],
        'identifier'=>[
            'type'=>'keyword',
        ],
        'deviceType'=>[
            'type'=>'keyword',
        ],
        'deviceMode'=>[
            'type'=>'keyword',
        ],
        'system'=>[
            'type'=>'keyword',
        ],
        'screen'=>[
            'type'=>'keyword',
        ],
        'network'=>[
            'type'=>'keyword',
        ],
        'ip'=>[
            'type'=>'keyword',
        ],
        'countryCode'=>[
            'type'=>'keyword',
        ],
        'countryName'=>[
            'type'=>'keyword',
        ],
        'region'=>[
            'type'=>'keyword',
        ],
        'city'=>[
            'type'=>'keyword',
        ]
    ];

    const TUTUAPP_DAY_LOG_PROPS = [
        'appId'=> [
            'type'=>'integer',
        ],
        'viewCount'=>[
            'type'=>'integer',
        ],
        'downloadCount'=>[
            'type'=>'integer',
        ],
        'installCount'=>[
            'type'=>'integer',
        ],
        'shareCount'=>[
            'type'=>'integer',
        ],
        'reportDate'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
    ];

    const TUTUAPP_SEARCH_DAY_LOG_PROPS = [
        'queryText'=>[
            'type'=>'text',
            'fields'=> [
                'keyword'=>[
                    'type'=> 'keyword'
                ],
            ]
        ],
        'searchCount'=>[
            'type'=>'integer',
        ],
        'reportDate'=>[
            'type'=>'date',
            'format'=>'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis'
        ],
    ];

}
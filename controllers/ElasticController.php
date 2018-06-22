<?php
namespace app\controllers;

use app\components\Elastic;
use app\components\IndexConstant;
use yii\web\Controller;
use app\models\AppIosFlat;
use yii\data\Pagination;
use Yii;

class ElasticController extends Controller{
    
    public function actionIndex(){
        
        $elastic = new Elastic();
        
        $index = IndexConstant::TUTUAPP_IOS_ZH;
        
        $response = $elastic->getIndex($index);

        return  json_encode($response);
    }

    public function actionCreateindex(){
        $elastic = new Elastic();

        $index = IndexConstant::TUTUAPP_IOS_ZH;

        $response = $elastic->createIndex($index);

        return  json_encode($response);
    }
    
    public function actionDeleteindex(){
        $elastic = new Elastic();

        $index = IndexConstant::TUTUAPP_IOS_ZH;

        $response = $elastic->deleteIndex($index);

        return  json_encode($response);
    }
    
    public function actionGetindexmapping(){
        $elastic = new Elastic();
        
        $index = IndexConstant::TUTUAPP_IOS_ZH;
        
        $response = $elastic->getIndexMapping($index);
        
        return  json_encode($response);
    }
    
    public function actionSetindexmapping(){
        $elastic = new Elastic();
        
        $index = IndexConstant::TUTUAPP_IOS_ZH;
        
        $properties =[];
        
        $response = $elastic->setIndexMapping($index,$properties);
        
        return  json_encode($response);
    }
    
    public function actionSearch(){
        $elastic = new Elastic();
        
        $index = IndexConstant::TUTUAPP_IOS_ZH;
        
        $queryString = "doe";
        
        $queryBody = $elastic->getQueryBody($queryString);
        $response = $elastic->search($queryBody, $index);
        
        return  json_encode($response);
    }
    
    public function actionBatchindexdata(){
        try {
            $elastic = new Elastic();

            $fileds = array_keys(Elastic::PROPS);


            //return  json_encode($fileds);

            //查询上线显示的app
            //$query = AppIosFlat::find()->select($fileds)->where(["is_show"=>"y","is_delete"=>"n"]);

            $sqlCount = "SELECT  count(id)  FROM app_ios_flat a  WHERE a.is_show='y' AND a.is_delete='n' ";

            $sql = "SELECT a.entity_id,a.app_name,a.app_category_first_name,a.app_category_first_code,a.app_category_first_id,a.app_introduction,a.app_current_newfunction,a.app_name_we,a.update_date,a.create_date
                FROM app_ios_flat a 
                WHERE a.is_show='y' AND a.is_delete='n' order by a.id limit :limit offset :offset  ";

            $sqlExten = "SELECT b.apptype, b.comment_count,b.download_count,b.score_count,b.look_count,b.favorite_count,b.share_count
                FROM app_ios_flat_exten b 
                WHERE b.entity_id = :entity_id  limit 1";

            $sqlReport = "SELECT c.week_download_count,c.month_download_count,c.year_download_count,c.week_view_count,c.month_view_count,c.year_view_count 
                FROM report_app c 
                WHERE c.entity_id = :entity_id limit 1";

            $count = Yii::$app->db->createCommand($sqlCount)->queryScalar();
            //return  json_encode($count);

            $rows = $count;
            $pageSize = 100;
            $totalPageNum= $rows/$pageSize;

            for($page=1; $page<=1; $page++) {

//                $pagination = new Pagination([
//                    'page' => $page,
//                    'defaultPageSize' => $pageSize,
//                    'totalCount' => $query->count(),
//                ]);

//                $pagination = new Pagination([
//                    'page' => $page,
//                    'defaultPageSize' => $pageSize,
//                    'totalCount' => $count,
//                ]);

                $offset = ($page-1)*$pageSize;

                $apps = Yii::$app->db->createCommand($sql)->bindParam(":limit",$pageSize)->bindParam(":offset",$offset)->queryAll();

                foreach ($apps as $index=>$app){
//                    if(empty($app["app_name_we"])){
//                        $app["app_name_we"] = $app["app_name"];
//                    }

                    $exten = Yii::$app->db->createCommand($sqlExten)->bindParam(":entity_id",$app["entity_id"])->queryOne();
                    $report = Yii::$app->db->createCommand($sqlReport)->bindParam(":entity_id",$app["entity_id"])->queryOne();

                    if(!is_array($exten)){
                        $exten =["apptype"=>"0",
                            "comment_count"=>"0",
                            "download_count"=>"0",
                            "score_count"=>"0",
                            "look_count"=>"0",
                            "favorite_count"=>"0",
                            "share_count"=>"0"
                        ];
                    }

                    if(!is_array($report)){
                        $report =['week_download_count' => '0' ,
                            'month_download_count' =>  '0',
                            'year_download_count' =>  '0',
                            'week_view_count' =>  '0',
                            'month_view_count' =>  '0',
                            'year_view_count' =>  '0'
                        ];
                    }

                    $app = $app +$exten + $report;
                    $apps[$index]=$app;
                }

//                $apps = $query->orderBy('id')
//                    ->offset($pagination->offset)
//                    ->limit($pagination->limit)
//                    ->all();

                //return  json_encode($apps);

                //$elastic->batchIndexData($apps,$fileds);


                $params = ['body' => []];
                $index = IndexConstant::TUTUAPP_IOS_ZH;

                foreach ($apps as $i => $app){
                    $params['body'][] = [
                        'index' => [
                            '_index' => $index,
                            '_type' => '_doc',
                            '_id' => $app["entity_id"]
                        ]
                    ];

                    $bodyArray = [];
                    foreach ($fileds as $filed){
                        $bodyArray[$filed]= $app[$filed];
                    }

                    $params['body'][] = $bodyArray;
                }

                //return json_encode($params);

                $elastic->bulkDocument($params);

                //$response = $elastic->batchIndexData($apps,$fileds);

                //return  json_encode($response);
            }

            return "ok";
            
        } catch (\Exception $e) {

            return $e->getMessage();

        }
        
      
    }
    
}
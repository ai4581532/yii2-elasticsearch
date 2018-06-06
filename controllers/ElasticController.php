<?php
namespace app\controllers;

use app\components\Elastic;
use yii\web\Controller;
use app\models\AppIosFlat;
use yii\data\Pagination;
use Yii;

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
            
            $rows = 30000;
            $pageSize = 100;
            $totalPageNum= $rows/$pageSize;

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

            for($page=1; $page<=$totalPageNum; $page++) {

//                $pagination = new Pagination([
//                    'page' => $page,
//                    'defaultPageSize' => $pageSize,
//                    'totalCount' => $query->count(),
//                ]);

                $pagination = new Pagination([
                    'page' => $page,
                    'defaultPageSize' => $pageSize,
                    'totalCount' => $count,
                ]);

                $offset = ($page-1)*$pageSize;
                $apps = Yii::$app->db->createCommand($sql)->bindParam(":limit",$pageSize)->bindParam(":offset",$offset)
                    ->queryAll();

                foreach ($apps as $index=>$app){
                    $exten = Yii::$app->db->createCommand($sqlExten)->bindParam(":entity_id",$app["entity_id"])->queryOne();
                    $report = Yii::$app->db->createCommand($sqlReport)->bindParam(":entity_id",$app["entity_id"])->queryOne();

                    if(!is_array($exten)){
                        $exten =["apptype"=>"0",
                            "comment_count"=>"0",
                            "download_count"=>"0",
                            "score_count"=>"0",
                            "look_count"=>"0",
                            "favorite_count"=>"0",
                            "share_count"=>"0" ];
                    }

                    if(!is_array($report)){
                        $report =['week_download_count' => '0' ,
                      'month_download_count' =>  '0',
                      'year_download_count' =>  '0',
                      'week_view_count' =>  '0'  ,
                      'month_view_count' =>  '0',
                      'year_view_count' =>  '0' ];
                    }

                    $app = $app +$exten + $report;
                    $apps[$index]=$app;
                }

//                $apps = $query->orderBy('id')
//                    ->offset($pagination->offset)
//                    ->limit($pagination->limit)
//                    ->all();

                return  json_encode($apps);
                //每次循环建立5条循环
                $response = $elastic->batchIndexData($apps,$fileds);

                return  json_encode($response);
            }

            return "ok";
            
        } catch (\Exception $e) {

            return $e->getMessage();

        }
        
      
    }
    
}
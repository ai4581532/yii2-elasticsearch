<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\components\Elastic;
use app\components\IndexConstant;
use app\models\AppIosFlat;
use yii\data\Pagination;
use Yii;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author  charley.wang
 *
 */
class BatchIndexDataController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionInitData($createDate=null){
        try {
            $elastic = new Elastic();
            //echo "ok";
            $fileds = array_keys(IndexConstant::TUTUAPP_IOS_PROPS);
            //echo "ok";
            //return  json_encode($fileds);

            //查询上线显示的app
            //$query = AppIosFlat::find()->select($fileds)->where(["is_show"=>"y","is_delete"=>"n"]);

            $condition = "AND a.is_show='y' AND a.is_delete='n'";
            if($createDate){
                $condition.="AND a.create_date>'{$createDate}' ";
            }

            $sqlCount = "SELECT  count(id)  FROM app_ios_flat a  WHERE 1=1 ".$condition;

            $sql = "SELECT a.entity_id,a.app_name,a.app_category_first_name,a.app_category_first_code,a.app_category_first_id,a.app_category_name,a.app_category_code,a.app_category_id,a.app_introduction,a.app_current_newfunction,a.app_name_we,a.update_date,a.create_date
                FROM app_ios_flat a 
                WHERE 1=1 ".$condition." order by a.id limit :limit offset :offset  ";

            $sqlExten = "SELECT b.apptype, b.comment_count,b.download_count,b.score_count,b.look_count,b.favorite_count,b.share_count
                FROM app_ios_flat_exten b 
                WHERE b.entity_id = :entity_id  limit 1";

            $sqlReport = "SELECT c.week_download_count,c.month_download_count,c.year_download_count,c.week_view_count,c.month_view_count,c.year_view_count 
                FROM report_app c 
                WHERE c.entity_id = :entity_id limit 1";
            //echo "ok";
            $count = Yii::$app->db->createCommand($sqlCount)->queryScalar();
            echo $count."\n";
            //echo "ok";
            $rows = $count;
            $pageSize = 100;
            //$pageSize = 1;
            $totalPageNum= $rows/$pageSize;
            //$totalPageNum= 1;

            for($page=1; $page<=$totalPageNum; $page++) {
                echo $page."\n";
                $offset = ($page - 1) * $pageSize;
                //echo $sql;
                $apps = Yii::$app->db->createCommand($sql)->bindParam(":limit", $pageSize)->bindParam(":offset", $offset)->queryAll();

                foreach ($apps as $index => $app) {

                    $exten = Yii::$app->db->createCommand($sqlExten)->bindParam(":entity_id", $app["entity_id"])->queryOne();
                    $report = Yii::$app->db->createCommand($sqlReport)->bindParam(":entity_id", $app["entity_id"])->queryOne();

                    if (!is_array($exten)) {
                        $exten = ["apptype" => "0",
                            "comment_count" => "0",
                            "download_count" => "0",
                            "score_count" => "0",
                            "look_count" => "0",
                            "favorite_count" => "0",
                            "share_count" => "0"
                        ];
                    }

                    if (!is_array($report)) {
                        $report = ['week_download_count' => '0',
                            'month_download_count' => '0',
                            'year_download_count' => '0',
                            'week_view_count' => '0',
                            'month_view_count' => '0',
                            'year_view_count' => '0'
                        ];
                    }

                    $app = $app + $exten + $report;
                    $apps[$index] = $app;
                }

//                $apps = $query->orderBy('id')
//                    ->offset($pagination->offset)
//                    ->limit($pagination->limit)
//                    ->all();

                //return  json_encode($apps);

                //$elastic->batchIndexData($apps,$fileds);
 
                $params = ['body' => []];
                $indexName = "tutuapp-ios-zh"; 
 
                foreach ($apps as $i => $app) {
					 
                    $params['body'][] = [
                        'index' => [
                            '_index' => "tutuapp-ios-zh",
                            '_type' => '_doc',
                            '_id' => $app["entity_id"]
                        ]
                    ];
					 
                    $bodyArray = [];
                    foreach ($fileds as $filed) {
						 
                        $bodyArray[$filed] = $app[$filed];
                    }
 
                    $params['body'][] = $bodyArray;
                }
 
                //return json_encode($params);

                $elastic->bulkDocument($params);
 
                //return  json_encode($response);
            }

            $update = date("Y-m-d H:i:s");
            $body = [ 'indexName' => 'tutuapp-update-info','updateNum'=>$count,'updated'=>$update];
            $response = $elastic->createDocument('tutuapp-update-info', null, $body);

            return ExitCode::OK;

        } catch (\Exception $e) {

            return $e->getMessage();

        }

        return ExitCode::OK;
    }

    /**
     * 定时更新索引数据
     */
    public function actionUpdateData(){
        //获取上次更新任务的信息，扫描更新时间
        $elastic = new Elastic();
        //var_dump($elastic->getClient());
        $fileds = array_keys(IndexConstant::TUTUAPP_IOS_PROPS);
        $queryBody =$elastic->getQueryBody(null,[],"match_all");
        //var_dump($queryBody);

        $index = "tutuapp-update-info";
        $type ="_doc";
        $order = ["updated" => ["order"=>"desc"]];

        $res = $elastic->search($queryBody, $index, $type, [], [], $order);
        $lastUpdate = $res["data"][0];

        //根据上次的扫描时间，获取之后更新过的应用
        //批量更新索引数据
        //var_dump($lastUpdate);

        $updateDate = $lastUpdate["updated"];

        if(empty($updateDate)){
            return ExitCode::OK;
        }

        //$this->actionInitData($lastUpdateDate);

        $sqlMaxUpdate = "SELECT MAX(update_date) FROM app_ios_flat a WHERE 1=1 AND a.is_show='y' AND a.is_delete='n'";

        $maxUpdate = Yii::$app->db->createCommand($sqlMaxUpdate)->queryScalar();

        if($maxUpdate<=$updateDate){
            return ExitCode::OK;
        }

        $condition = "AND a.is_show='y' AND a.is_delete='n'";

        $condition.="AND a.update_date>'{$updateDate}'";

        $condition.=" AND a.update_date<='{$maxUpdate}'";

        $sqlCount = "SELECT  count(id)  FROM app_ios_flat a  WHERE 1=1 ".$condition;

        $sql = "SELECT a.entity_id,a.app_name,a.app_category_first_name,a.app_category_first_code,a.app_category_first_id,a.app_category_name,a.app_category_code,a.app_category_id,a.app_introduction,a.app_current_newfunction,a.app_name_we,a.update_date,a.create_date
                FROM app_ios_flat a 
                WHERE 1=1 ".$condition." order by a.id limit :limit offset :offset  ";

        $sqlExten = "SELECT b.apptype, b.comment_count,b.download_count,b.score_count,b.look_count,b.favorite_count,b.share_count
                FROM app_ios_flat_exten b 
                WHERE b.entity_id = :entity_id  limit 1";

        $sqlReport = "SELECT c.week_download_count,c.month_download_count,c.year_download_count,c.week_view_count,c.month_view_count,c.year_view_count 
                FROM report_app c 
                WHERE c.entity_id = :entity_id limit 1";

        $count = Yii::$app->db->createCommand($sqlCount)->queryScalar();
        var_dump($count);

        $rows = $count;
        $pageSize = 100;
        $totalPageNum= $rows/$pageSize;

        for($page=1; $page<=$totalPageNum; $page++) {

            $offset = ($page - 1) * $pageSize;

            $apps = Yii::$app->db->createCommand($sql)->bindParam(":limit", $pageSize)->bindParam(":offset", $offset)->queryAll();

            foreach ($apps as $index => $app) {

                $exten = Yii::$app->db->createCommand($sqlExten)->bindParam(":entity_id", $app["entity_id"])->queryOne();
                $report = Yii::$app->db->createCommand($sqlReport)->bindParam(":entity_id", $app["entity_id"])->queryOne();

                if (!is_array($exten)) {
                    $exten = ["apptype" => "0",
                        "comment_count" => "0",
                        "download_count" => "0",
                        "score_count" => "0",
                        "look_count" => "0",
                        "favorite_count" => "0",
                        "share_count" => "0"
                    ];
                }

                if (!is_array($report)) {
                    $report = ['week_download_count' => '0',
                        'month_download_count' => '0',
                        'year_download_count' => '0',
                        'week_view_count' => '0',
                        'month_view_count' => '0',
                        'year_view_count' => '0'
                    ];
                }

                $app = $app + $exten + $report;
                $apps[$index] = $app;
            }

            $params = ['body' => []];
            $index = "tutuapp-ios-zh";

            foreach ($apps as $i => $app) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $index,
                        '_type' => '_doc',
                        '_id' => $app["entity_id"]
                    ]
                ];

                $bodyArray = [];
                foreach ($fileds as $filed) {
                    $bodyArray[$filed] = $app[$filed];
                }

                $params['body'][] = $bodyArray;
            }

            $elastic->bulkDocument($params);

        }

        $body = [ 'indexName' => 'tutuapp-update-info','updateNum'=>$count,'updated'=>$maxUpdate];
        $response = $elastic->createDocument('tutuapp-update-info', null, $body);

        return ExitCode::OK;
    }

}

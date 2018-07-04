<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\components\Elastic;
use app\components\IndexConstant;
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
    public function actionQuickInitData($entityId,$maxEntityId=null,$size=100){
        if(empty($maxEntityId)){
            $maxEntityId = $this->getMaxEntityId();
            echo $maxEntityId."\n";
        }
        $elastic = new Elastic();
        $indexName = IndexConstant::TUTUAPP_IOS_ZH;
        $tempEntityId =$entityId+$size;

        while($tempEntityId<=$maxEntityId+$size){
            echo $tempEntityId."\n";

            $condition = "AND a.is_show='y' AND a.is_delete='n' AND a.entity_id>={$entityId} AND a.entity_id<{$tempEntityId}";

            $apps = $this->getAppIosFlatDataByEntityId($condition);

            foreach ($apps as $index => $app) {
                $exten = $this->getAppIosFlatExtenData($app["entity_id"]);
                $report = $this->getAppIosReportData($app["entity_id"]);
                $app = $this->getFinalAppData($app,$exten,$report);
                $apps[$index] = $app;
            }

            $this->batchCreateDocument($apps,$indexName);
            $tempEntityId+=$size;
        };

        return ExitCode::OK;
    }

    public function actionInitData($startCreateDate=null, $endCreateDate=null){
        $condition = "AND a.is_show='y' AND a.is_delete='n'";

        if(empty($startCreateDate)){
            //开始时间为空，则获取上次的截止时间
            $startCreateDate = $this->getStartCreateDate();
        }

        if($startCreateDate){
            $condition.="AND a.create_date>='{$startCreateDate}' ";
        }

        //如果没有设置截止时间，则设置为当前时间
        if(empty($endCreateDate)){
            $endCreateDate = date("Y-m-d H-m-s");
        }

        if($endCreateDate){
            $condition.="AND a.create_date<'{$endCreateDate}' ";
        }

        //echo $condition;return ExitCode::OK;
        $count = $count = $this->getAppIosFlatDataCount($condition);
        echo $count."\n";

        $pageSize = 100;
        //$pageSize = 1;
        $totalPageNum= $count/$pageSize;
        //$totalPageNum= 1;

        for($page=1; $page<=$totalPageNum; $page++) {
            echo $page."\n";
            $offset = ($page - 1) * $pageSize;

            $apps = $this->getAppIosFlatData($condition,$pageSize,$offset);

            foreach ($apps as $index => $app) {
                $exten = $this->getAppIosFlatExtenData($app["entity_id"]);
                $report = $this->getAppIosReportData($app["entity_id"]);
                $app = $this->getFinalAppData($app,$exten,$report);
                $apps[$index] = $app;
            }

            $this->batchCreateDocument($apps);

        }

        $this->addCreatedInfo($count,$endCreateDate);

        return ExitCode::OK;
    }

    public function actionUpdateData(){
        //获取上次更新任务的信息
        $lastUpdate = $this->getLastUpdateInfo();

        //根据上次的扫描时间，获取之后更新过的应用
        //批量更新索引数据
        if($lastUpdate){
            $updateDate = $lastUpdate["updated"];
        }

        if(empty($updateDate)){
            return ExitCode::OK;
        }

        $maxUpdate = $this->getAppMaxUpdateDate();

        if($maxUpdate<=$updateDate){
            return ExitCode::OK;
        }

        $condition = "AND a.is_show='y' AND a.is_delete='n'";

        $condition.="AND a.update_date>'{$updateDate}'";

        $condition.=" AND a.update_date<='{$maxUpdate}'";

        $count = $this->getAppIosFlatDataCount($condition);

        $pageSize = 100;
        $totalPageNum= $count/$pageSize;

        for($page=1; $page<=$totalPageNum; $page++) {

            $offset = ($page - 1) * $pageSize;

            $apps = $this->getAppIosFlatData($condition,$pageSize,$offset);

            foreach ($apps as $index => $app) {

                $exten = $this->getAppIosFlatExtenData($app["entity_id"]);
                $report = $this->getAppIosReportData($app["entity_id"]);

                $app = $this->getFinalAppData($app,$exten,$report);

                $apps[$index] = $app;
            }

            $this->batchCreateDocument($apps);

        }

        $this->createLastUpdateInfo($count,$maxUpdate);

        return ExitCode::OK;
    }

    public function getFinalAppData($app,$exten,$report){
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

        return $app;
    }

    public function batchCreateDocument($apps,$index = "tutuapp-ios-zh"){
        $elastic = new Elastic();

        $fileds = array_keys(IndexConstant::TUTUAPP_IOS_PROPS);

        $params = ['body' => []];

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

    public function getStartCreateDate(){
        $elastic = new Elastic();
        //开始时间为空，则获取上次的截止时间
        $queryBody =$elastic->getQueryBody(null,[],"match_all");
        $order = ["created" => ["order"=>"desc"]];

        $res = $elastic->search($queryBody, "tutuapp_ios_create_info", "_doc", [], [], $order);
        $lastCreated = $res["data"][0];
        if($lastCreated){
            $startCreateDate = $lastCreated["created"];
        }else{
            $startCreateDate = '2000-01-01 00:00:00';
        }
        return $startCreateDate;
    }

    public function addCreatedInfo($count,$endCreateDate){
        $elastic = new Elastic();
        $body = [ 'indexName' => 'tutuapp_ios_create_info','createdNum'=>$count,'created'=>$endCreateDate];
        $elastic->createDocument('tutuapp_ios_create_info', null, $body);
    }

    public function getLastUpdateInfo(){
        $elastic = new Elastic();

        $queryBody =$elastic->getQueryBody(null,[],"match_all");
        $index = "tutuapp_ios_update_info";
        $type ="_doc";
        $order = ["updated" => ["order"=>"desc"]];

        $res = $elastic->search($queryBody, $index, $type, [], [], $order);
        $lastUpdate = $res["data"][0];
        return $lastUpdate;
    }

    public function createLastUpdateInfo($count,$maxUpdate){
        $elastic = new Elastic();
        $body = [ 'indexName' => 'tutuapp_ios_update_info','updatedNum'=>$count,'updated'=>$maxUpdate];
        $elastic->createDocument('tutuapp_ios_update_info', null, $body);
    }

    public function getAppMaxUpdateDate(){
        $sqlMaxUpdate = "SELECT MAX(update_date) FROM app_ios_flat a WHERE 1=1 AND a.is_show='y' AND a.is_delete='n'";
        try{
            $maxUpdate = Yii::$app->db->createCommand($sqlMaxUpdate)->queryScalar();
        }catch (\yii\db\Exception $e){
            return 0;
        }
        return $maxUpdate;
    }

    public function getAppIosFlatDataCount($condition){
        $sqlCount = "SELECT  count(id)  FROM app_ios_flat a  WHERE 1=1 ".$condition;
        try{
            $count = Yii::$app->db->createCommand($sqlCount)->queryScalar();
        }catch (\yii\db\Exception $e){
            return 0;
        }
        return $count;
    }

    public function getMaxEntityId(){
        $sql = "SELECT  max(entity_id)  FROM app_ios_flat a  WHERE 1=1 ";
        try{
            $maxId = Yii::$app->db->createCommand($sql)->queryScalar();
        }catch (\yii\db\Exception $e){
            return 0;
        }
        return $maxId;
    }

    public function getAppIosFlatDataByEntityId($condition){
        $sql = "SELECT a.entity_id,a.app_name,a.app_category_first_name,a.app_category_first_code,a.app_category_first_id,
                  a.app_category_name,a.app_category_code,a.app_category_id,a.app_introduction,a.app_current_newfunction,a.app_name_we,a.update_date,a.create_date
                FROM app_ios_flat a 
                WHERE 1=1 ".$condition;
        try{
            $apps = Yii::$app->db->createCommand($sql)->queryAll();
        }catch (\yii\db\Exception $e){
            return [];
        }
        return $apps;
    }

    public function getAppIosFlatData($condition,$pageSize,$offset){
        $sql = "SELECT a.entity_id,a.app_name,a.app_category_first_name,a.app_category_first_code,a.app_category_first_id,
                  a.app_category_name,a.app_category_code,a.app_category_id,a.app_introduction,a.app_current_newfunction,a.app_name_we,a.update_date,a.create_date
                FROM app_ios_flat a 
                WHERE 1=1 ".$condition." order by a.id limit :limit offset :offset  ";
        try{
            $apps = Yii::$app->db->createCommand($sql)->bindParam(":limit", $pageSize)->bindParam(":offset", $offset)->queryAll();
        }catch (\yii\db\Exception $e){
            return [];
        }
        return $apps;
    }

    public function getAppIosFlatExtenData($entityId){
        $sqlExten = "SELECT b.apptype, b.comment_count,b.download_count,b.score_count,b.look_count,b.favorite_count,b.share_count
                FROM app_ios_flat_exten b 
                WHERE b.entity_id = :entity_id  limit 1";
        try{
            $exten = Yii::$app->db->createCommand($sqlExten)->bindParam(":entity_id", $entityId)->queryOne();
        }catch (\yii\db\Exception $e){
            return [];
        }
        return $exten;
    }

    public function getAppIosReportData($entityId){
        $sqlReport = "SELECT c.week_download_count,c.month_download_count,c.year_download_count,c.week_view_count,c.month_view_count,c.year_view_count 
                FROM report_app c 
                WHERE c.entity_id = :entity_id limit 1";
        try{
            $report = Yii::$app->db->createCommand($sqlReport)->bindParam(":entity_id", $entityId)->queryOne();
        }catch (\yii\db\Exception $e){
            return [];
        }

        return $report;
    }
}

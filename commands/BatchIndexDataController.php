<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\components\Elastic;
use app\models\AppIosFlat;
use yii\data\Pagination;
/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BatchIndexDataController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        try {
            $elastic = new Elastic();

            $rows = 30000;
            $pageSize = 5;
            $count= $rows/$pageSize;

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
            $query = AppIosFlat::find()->select(array_keys($filedMap))->where(["is_show"=>"y","is_delete"=>"n"]);

            for($page=4100; $page<$count; $page++) {

                $pagination = new Pagination([
                    'page' => $page,
                    'defaultPageSize' => $pageSize,
                    'totalCount' => $query->count(),
                ]);

                $apps = $query->orderBy('id')
                    ->offset($pagination->offset)
                    ->limit($pagination->limit)
                    ->all();

                //每次循环建立5条循环
                $elastic->batchIndexData($apps);

            }

            //return  json_encode($response);

            return ExitCode::OK;

        } catch (\Exception $e) {

            return $e->getMessage();

        }

        return ExitCode::OK;
    }

}

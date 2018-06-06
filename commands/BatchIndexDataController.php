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
            $pageSize = 3;
            $count= $rows/$pageSize;

            for($i=1200; $i<$count; $i++) {

                //每次循环建立5条循环
                $elastic->batchIndexData($i,$pageSize);

            }

            //return  json_encode($response);

            return ExitCode::OK;

        } catch (\Exception $e) {

            return $e->getMessage();

        }

        return ExitCode::OK;
    }

}

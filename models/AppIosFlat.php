<?php

namespace app\models;

use yii\db\ActiveRecord;

class AppIosFlat extends ActiveRecord
{
    public static function tableName( ){
        return "app_ios_flat";
    }
}
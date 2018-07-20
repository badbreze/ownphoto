<?php
namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class BaseRecord extends ActiveRecord
{
    public function behaviors()
    {
        $behaviorsParent = parent::behaviors();

        $behaviors = [
            "TimestampBehavior" => [
                'class' => TimestampBehavior::className(),
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
            "BlameableBehavior" => [
                'class' => BlameableBehavior::className(),
            ]
        ];

        return ArrayHelper::merge($behaviorsParent, $behaviors);
    }
}

<?php
/**
 * List of all photos of a date
 */

namespace app\modules\v1\actions\browse;

use app\models\Photo;
use app\models\PhotoData;
use yii\data\ActiveDataProvider;
use yii\rest\Action;

class ActionDate extends Action
{
    /**
     * @return mixed
     */
    public function run()
    {
        //GET params
        $params = \Yii::$app->request->get();

        //Date object to filter database rows
        $date = new \DateTime();
        $date->setDate($params['year'], $params['month'], $params['day']);

        //Mysql Date
        $mysqlDate = $date->format('Y-m-d');

        $q = Photo::find();
        $q->alias('photo');
        $q->where(['LIKE', 'created_at', "{$mysqlDate}%", false]);

        $dataProvider = new ActiveDataProvider([
            'query' => $q,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                ]
            ]
        ]);

        return $dataProvider;
    }
}
<?php
/**
 * List of all photos grouped by date
 */
namespace app\modules\v1\actions\browse;

use app\models\Photo;
use yii\rest\Action;
use yii\data\ActiveDataProvider;

class ActionIndex extends Action
{
    /**
     * Renders the full user photo collection ordered by upload (created_at)
     * @return mixed
     */
    public function run() {
        $q = Photo::findGrouped();

        $photos = $q->asArray()->all();
/*
        foreach ($photos as $key=>$photo) {
            $gq = Photo::findByIdentiffierDate($photo['id']);

            $dataProvider = new ActiveDataProvider([
                'query' => $gq,
                'sort' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC
                    ]
                ]
            ]);

            $photos[$key]['photos'] = $dataProvider->getModels();
        }
*/
        return $photos;
    }
}
<?php
/**
 * View single photo
 */
namespace app\modules\v1\actions\browse;

use yii\rest\Action;

class ActionView extends Action
{
    /**
     * @return array
     */
    public function run() {
        $photo = Photo::findOne(['identifier' => $id]);

        if(!$photo || !$photo->id) {
            throw new Exception('Content Not Found');
        }

        return [
            'photo' => $photo,
            'album' => $album
        ];
    }
}
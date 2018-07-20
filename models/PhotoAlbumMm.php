<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "photo_album_mm".
 *
 * @property int $photo_id
 * @property int $album_id
 */
class PhotoAlbumMm extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photo_album_mm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['photo_id', 'album_id'], 'required'],
            [['photo_id', 'album_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'photo_id' => Yii::t('app', 'Photo ID'),
            'album_id' => Yii::t('app', 'Album ID'),
        ];
    }
}

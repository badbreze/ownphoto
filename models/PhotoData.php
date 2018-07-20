<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "photo_data".
 *
 * @property int $photo_id
 * @property string $attribute
 * @property string $value
 *
 * @property Photo $photo
 */
class PhotoData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photo_data';
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [
            'attribute',
            'value'
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['photo_id', 'attribute', 'value'], 'required'],
            [['photo_id'], 'integer'],
            [['attribute'], 'string', 'max' => 64],
            [['value'], 'string', 'max' => 256],
            [['photo_id', 'attribute'], 'unique', 'targetAttribute' => ['photo_id', 'attribute']],
            [['photo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Photo::className(), 'targetAttribute' => ['photo_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'photo_id' => Yii::t('app', 'Photo ID'),
            'attribute' => Yii::t('app', 'Attribute'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoto()
    {
        return $this->hasOne(Photo::className(), ['id' => 'photo_id']);
    }

    /**
     * Store photo metadata by array of datas
     * @param array $metadata
     * @return bool
     */
    public static function storeMetadata(array $metadata, Photo $photo) {
        if(!$photo->id) {
            print_r('loool');die;
            return false;
        }

        foreach ($metadata as $metaKey => $metaValue) {
            $photoMeta = new PhotoData();
            $photoMeta->photo_id = $photo->id;
            $photoMeta->attribute = $metaKey;
            $photoMeta->value = $metaValue;
            $photoMeta->save(false);
        }

        return true;
    }
}

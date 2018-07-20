<?php
namespace app\models;

use elitedivision\amos\attachments\Module;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "photo_thumbnails".
 *
 * @property integer $id
 * @property string $hash
 * @property integer $photo_id
 * @property string $crop
 * @property integer $protected
 * @property File $photo
 * @property int $created_by
 * @property string $created_at
 * @property int $updated_by
 * @property string $updated_at
 * @property int $deleted_by
 * @property string $deleted_at
 */
class PhotoThumbnails extends ActiveRecord
{
    const MAIN = 1;
    const NOT_MAIN = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'photo_thumbnails';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className()
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['photo_id', 'hash'], 'required'],
            [['protected'], 'safe'],
            [['hash', 'crop'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('app', 'ID'),
            'hash' => Module::t('app', 'Hash'),
            'photo_id' => Module::t('app', 'Photo ID'),
            'crop' => Module::t('app', 'Crop'),
            'protected' => Module::t('app', 'Is Protected')
        ];
    }

    /**
     * @param string $size
     * @return string
     */
    public function getUrl($size = 'original')
    {
        return Url::to(['/' . Module::getModuleName() . '/file/view', 'id' => $this->id, 'hash' => $this->hash, 'size' => $size]);
    }

    /**
     * @param $size
     * @return string
     */
    public function getWebUrl($size)
    {
        return \Yii::$app->getUrlManager()->createAbsoluteUrl(Url::to(['/' . Module::getModuleName() . '/file/download', 'id' => $this->id, 'hash' => $this->hash, 'size' => $size]));
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getModule()->getFilesDirPath($this->photo->hash) . DIRECTORY_SEPARATOR . $this->photo->hash . '.' . $this->photo->type;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoto() {
        return $this->hasOne(Photo::className(), ['id' => 'photo_id']);
    }

    /**
     * @param Photo $photo
     * @param string $crop
     * @return bool|string
     */
    public static function getHashByPhoto(Photo $photo, $crop, $protected = true) {
        $result = PhotoThumbnails::findOne([
            'photo_id' => $photo->id,
            'crop' => $crop,
            'protected' => $protected
        ]);

        // If the photo crop exists return it
        if($result && $result->id) {
            return $result->hash;
        }

        /**
         * Mew record data
         */
        $data = [
            'photo_id' => $photo->id,
            'crop' => $crop,
            'protected' => $protected
        ];

        $newFileRef = new PhotoThumbnails();
        $newFileRef->load(['PhotoThumbnails' => $data]);
        $newFileRef->hash = md5(json_encode($data));

        //Validate and store, and prey it works
        if($newFileRef->validate()) {
            $newFileRef->save();

            return $newFileRef->hash;
        }

        return false;
    }
}

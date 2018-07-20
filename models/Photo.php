<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "photo".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $identifier
 * @property string $filename
 * @property string $type
 * @property string $mime
 * @property int $sort
 * @property int $created_by
 * @property string $created_at
 * @property int $updated_by
 * @property string $updated_at
 * @property int $deleted_by
 * @property string $deleted_at
 *
 * @property PhotoData[] $photoDatas
 * @property PhotoData[] $computedDatas
 * @property PhotoThumbnail $photoThumbnail
 */
class Photo extends BaseRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photo';
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [
            'id',
            'name',
            'url',
            'description',
            'created_at',
            'created_by',
            'computedDatas'
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'identifier', 'filename', 'type', 'mime'], 'required'],
            [['description'], 'string'],
            [['sort', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 128],
            [['identifier'], 'string', 'max' => 64],
            [['filename'], 'string', 'max' => 256],
            [['type'], 'string', 'max' => 16],
            [['mime'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'identifier' => Yii::t('app', 'Identifier'),
            'filename' => Yii::t('app', 'Filename'),
            'type' => Yii::t('app', 'Type'),
            'mime' => Yii::t('app', 'Mime'),
            'sort' => Yii::t('app', 'Sort'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhotoDatas()
    {
        return $this->hasMany(PhotoData::className(), ['photo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComputedDatas()
    {
        return $this->hasMany(PhotoData::className(), ['photo_id' => 'id'])
            ->andWhere(['LIKE', 'attribute', "COMPUTED::%", false]);
    }

    /**
     * Override Default find to filter only user Photos
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        $q = parent::find();
        $q->andWhere(['created_by' => Yii::$app->user->id]);

        return $q;
    }

    /**
     * This Method is a insecure find, used to find any photo
     * @return \yii\db\ActiveQuery
     */
    public static function findUnfiltered() {
        return parent::find();
    }

    public static function findGrouped() {
        $q = self::find();
        $q->select([
            'YEAR(created_at) photo_year',
            'MONTH(created_at) photo_month',
            'DAY(created_at) photo_day',
            'COUNT(*) photo_count',
            'CONCAT(YEAR(created_at),MONTH(created_at),DAY(created_at)) id',
        ]);
        $q->groupBy(['photo_year', 'photo_month', 'photo_day']);
        $q->orderBy(['photo_year' => SORT_DESC, 'photo_month' => SORT_DESC, 'photo_day' => SORT_DESC]);

        return $q;
    }

    public static function findByIdentiffierDate($id) {
        $q = self::find();
        $q->andWhere(['CONCAT(YEAR(created_at),MONTH(created_at),DAY(created_at))' => $id]);

        return $q;
    }

    /**
     * Return the url of this file protected by access
     * @param string $size
     * @return string
     */
    public function getUrl($size = 'original', $absolute = true, $canCache = false)
    {
        $hash = PhotoThumbnails::getHashByPhoto($this, $size);
        return $this->generateUrlForHash($hash, $absolute, $canCache);
    }

    /**
     * Create an Unprotected photo url (is prefered to not use, this content will be public)
     * @param $size
     * @return string
     */
    public function getPublicUrl($size = 'original', $absolute = false, $canCache = false)
    {
        $hash = PhotoThumbnails::getHashByPhoto($this, $size, false);
        return $this->generateUrlForHash($hash, $absolute, $canCache);
    }

    /**
     * Return the url absolute or not
     * @param type $hash
     * @param type $absolute
     * @param type $canCache
     * @return type
     */
    public function generateUrlForHash($hash, $absolute, $canCache = false) {
        $baseUrl = Url::to(['/files/file/view', 'hash' => $hash, 'canCache' => $canCache]);

        if (!$absolute)
            return $baseUrl;
        else
            return \Yii::$app->getUrlManager()->createAbsoluteUrl($baseUrl);
    }

    /**
     * Return the disk path of the photo
     * @return string
     */
    public function getPath()
    {
        return Yii::$app->getModule('files')->getFilesDirPath($this->identifier) . DIRECTORY_SEPARATOR . $this->identifier . '.' . $this->type;
    }

    /**
     * Return all crops of this photo
     * @return \yii\db\ActiveQuery
     */
    public function getPhotoThumbnail()
    {
        return $this->hasOne(PhotoThumbnails::className(), ['photo_id' => 'id']);
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "photo_shares".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $identiffier
 * @property int $public
 * @property int $created_by
 * @property string $created_at
 * @property int $updated_by
 * @property string $updated_at
 * @property int $deleted_by
 * @property string $deleted_at
 */
class PhotoShares extends BaseRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photo_shares';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'identiffier', 'public', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'], 'required'],
            [['description'], 'string'],
            [['public', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 128],
            [['identiffier'], 'string', 'max' => 64],
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
            'identiffier' => Yii::t('app', 'Identiffier'),
            'public' => Yii::t('app', 'Public'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
        ];
    }
}

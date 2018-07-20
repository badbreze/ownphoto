<?php
/**
 * @property string $access_token
 * @property integer $user_id
 * @property string $device_info
 * @property string $ip
 * @property string $location
 * @property string $fcm_token
 * @property string $device_os
 * @property string $logout_at
 * @property integer $logout_by
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 */
namespace app\models;

use common\components\helpers\UserHelper;
use yii\db\ActiveRecord;
use yii\db\Expression;

class AccessTokens extends BaseRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_tokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'logout_by', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['device_info'], 'string'],
            [['logout_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['access_token', 'ip'], 'string', 'max' => 32],
            [['location'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'access_token' => Yii::t('app', 'Access Token'),
            'user_id' => Yii::t('app', 'User id'),
            'device_info' => Yii::t('app', 'Device info'),
            'ip' => Yii::t('app', 'IP info'),
            'location' => Yii::t('app', 'Location'),
            'logout_at' => Yii::t('app', 'Logout At'),
            'logout_by' => Yii::t('app', 'Logout By'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
        ];
    }

    public static function primaryKey()
    {
        return [
            'access_token'
        ];
    }

    public function logout()
    {
        $this->logout_at = new Expression('NOW()');
        $this->logout_by = UserHelper::get()->getId();
        $this->save(false);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}

<?php
/**
 * Thumbnails controller
 * Maybe even more than thumbnails
 *
 * @license GPLv3
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3
 *
 * @package    ownphoto
 * @category   Platform
 * @author     Damian Gomez <racksoftgmail.com>
 */
namespace app\modules\files\controllers;

use app\modules\v1\controllers\base\BaseController;
use Yii;
use app\models\Photo;
use app\models\PhotoThumbnails;
use yii\image\ImageDriver;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class FileController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'delete',
                            'view'
                        ],
                        'allow' => true,
                        'matchCallback' => [$this,'checkAccess']
                    ],
                ],
            ]
        ]);
    }

    /**
     * @param $rule
     * @param $action
     * @return bool
     */
    public function checkAccess($rule, $action)
    {
        switch ($action->id) {
            case 'view' :
                {
                    // Fire ref
                    $thumb = PhotoThumbnails::findOne(['hash' => \Yii::$app->request->get('hash')]);

                    //If file exists
                    if (!$thumb || !$thumb->photo) {
                        return false;
                    }

                    /**
                     * If the file is not under protection
                     */
                    if (!$thumb->protected) {
                        return true;
                    }

                    // Find file
                    $file = $thumb->photo;
                }
                break;
            default:
                {
                    // Find file
                    $file = Photo::findOne(['id' => \Yii::$app->request->get('id')]);
                }
                break;
        }

        //If file exists
        if (!$file || !$file->id) {
            return false;
        }

        return true;
    }

    /**
     * @param $hash
     * @return $this|bool
     * @throws \Exception
     */
    public function actionView($hash, $canCache = false)
    {
       /* if (!empty($module->cache_age) && $canCache) {
            \Yii::$app->response->headers->set("Cache-Control", "max-age=" . $module->cache_age . ", public");
        } else {
            \Yii::$app->response->headers->set('Pragma', 'no-cache');
            \Yii::$app->response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        }*/

        /**
         * @var $fileRef PhotoThumbnails
         */
        $thumb = PhotoThumbnails::findOne(['hash' => $hash]);

        if ($thumb && $thumb->id) {
            /**
             * @var $size string Crop name
             */
            $size = $thumb->crop;

            /** @var File $file */
            $file = $thumb->photo;

            /**
             * @var $filePath string Where is located file
             */
            $filePath = $file->getPath();

            /**
             * File stream from source server
             */
            $fileStream = Yii::$app->fs->readStream($filePath);

            if ($fileStream) {
                if ($size == 'original' || !in_array(strtolower($file->type), ['jpg', 'jpeg', 'png', 'gif'])) {
                    return \Yii::$app->response->sendStreamAsFile($fileStream, "$file->filename");
                } else {
                    $moduleConfig = Yii::$app->getModule('files')->config;
                    $crops = $moduleConfig['crops'] ?: [];

                    if (array_key_exists($size, $crops)) {
                        return $this->getCroppedImage($file, $crops[$size]);
                    } else {
                        throw new \Exception('Size not found - ' . $size);
                    }
                }
            } else
                return false;
        } else {
            return false;
        }
    }

    /**
     * @param $file
     * @param $cropSettings
     * @return $this
     */
    public function getCroppedImage($file, $cropSettings)
    {
        $fileDir = $this->module->getFilesDirPath($file->identifier) . DIRECTORY_SEPARATOR;
        $filePath = $fileDir . $file->identifier . '.' . $file->type;
        $cropPath = $fileDir . $file->identifier . '.' . $cropSettings['width'] . '.' . $cropSettings['height'] . '.' . $file->type;

        if (file_exists($cropPath)) {
            // return \Yii::$app->response->sendFile($cropPath, "$file->name.$file->type");
        }
        //Crop and return
        $cropper = new ImageDriver();

        $configStack = [
            'width' => null,
            'height' => null,
            'master' => null,
            'crop_width' => $cropSettings['width'],
            'crop_height' => $cropSettings['height'],
            'crop_offset_x' => null,
            'crop_offset_y' => null,
            'rotate_degrees' => null,
            'refrect_height' => null,
            'refrect_opacity' => null,
            'refrect_fade_in' => null,
            'flip_direction' => null,
            'bg_color' => null,
            'bg_opacity' => null,
            'quality' => 100
        ];

        $cropConfig = ArrayHelper::merge($configStack, $cropSettings);

        /**
         * Extract All settings
         * Eg.
         * $cr_width
         * $cr_height
         * $cr_quality
         */
        extract($cropConfig, EXTR_PREFIX_ALL, 'cr');

        /**
         * @var $image Image_GD | Image_Imagick
         */
        $image = $cropper->load($filePath);

        $image->resize($cr_width, $cr_height, $cr_master);

        if ($cr_crop_width && $cr_crop_height) {
            $image->crop($cr_crop_width, $cr_crop_height, $cr_crop_offset_x, $cr_crop_offset_y);
        }
        if ($cr_rotate_degrees) {
            $image->rotate($cr_rotate_degrees);
        }
        if ($cr_refrect_height) {
            $image->reflection($cr_refrect_height, $cr_refrect_opacity, $cr_refrect_fade_in);
        }
        if ($cr_flip_direction) {
            $image->flip($cr_flip_direction);
        }
        if ($cr_bg_color) {
            $image->background($cr_bg_color, $cr_bg_opacity);
        }
        $image->save($cropPath, $cr_quality);
        //Return the new image
        return \Yii::$app->response->sendFile($cropPath, "$file->filename");
    }

    /**
     * @param $id
     * @param $item_id
     * @param $model
     * @param $attribute
     * @return bool|Response
     */
    public function actionDelete($id, $item_id, $model, $attribute)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($this->getModule()->detachFile($id)) {
            if (Yii::$app->request->isAjax) {
                return true;
            } else {
                return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : null));
            }
        } else {
            if (Yii::$app->request->isAjax) {
                return false;
            } else {
                return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : null));
            }
        }
    }

    /**
     *
     * @param type $action
     * @return type
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = ($action->id !== "upload-files");
        return parent::beforeAction($action);
    }
}

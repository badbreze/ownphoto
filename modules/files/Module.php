<?php
/**
 * File Management Module
 * Used for crops, thumbs and so
 *
 * @license GPLv3
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3
 *
 * @package    ownphoto
 * @category   Platform
 * @author     Damian Gomez <racksoftgmail.com>
 */
namespace app\modules\files;

use app\models\Photo;
use app\models\PhotoData;
use dosamigos\flysystem\SftpFsComponent;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\log\Logger;
use Imagine\Image\Metadata\ExifMetadataReader;
use app\modules\files\helpers\FilesHelper;

class Module extends \yii\base\Module
{
    public $storePath = '@app/storage/store';

    public $tempPath = '@app/storage/temp';

    public $config = [];

    /**
     * @throws Exception
     */
    public function init()
    {
        parent::init();

        if (empty($this->storePath) || empty($this->tempPath)) {
            throw new Exception(Module::t('app', 'Setup {storePath} and {tempPath} in module properties'));
        }

        //Configuration
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        \Yii::configure($this, ArrayHelper::merge($config, $this));
    }

    /**
     * @param string $suffix
     * @return string
     */
    public function getUserDirPath($suffix = '')
    {
        $sessionId = md5(0);

        if(\Yii::$app->has('session')) {
            \Yii::$app->session->open();
            $sessionId = \Yii::$app->session->id;
            \Yii::$app->session->close();
        }

        $userDirPath = $this->getTempPath() . DIRECTORY_SEPARATOR . $sessionId . $suffix;
        FileHelper::createDirectory($userDirPath, 0777);

        return $userDirPath . DIRECTORY_SEPARATOR;
    }

    /**
     * @return bool|string
     */
    public function getTempPath()
    {
        return \Yii::getAlias($this->tempPath);
    }

    /**
     * @param $fileHash
     * @param $useStorePath
     * @return string
     */
    public function getFilesDirPath($fileHash)
    {
        //Generate subdirs name on the hash
        $path = $this->getSubDirs($fileHash);

        //Create the dir if not exists
        \Yii::$app->fs->createDir($path);

        //The created path
        return $path;
    }

    /**
     * Based on hash name generate subdirs
     * @param $fileHash
     * @param int $depth
     * @return string
     */
    public function getSubDirs($fileHash, $depth = 3)
    {
        $depth = min($depth, 9);
        $path = '';

        for ($i = 0; $i < $depth; $i++) {
            $folder = substr($fileHash, $i * 3, 2);
            $path .= $folder;
            if ($i != $depth - 1) $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    /**
     * @param File $file
     * @return string
     */
    public function getWebPath(Photo $file)
    {
        $fileName = $file->hash . '.' . $file->type;
        $webPath = '/' . $this->webDir . '/' . $this->getSubDirs($file->hash) . '/' . $fileName;
        return $webPath;
    }

    /**
     * @param $filePath string
     * @return bool|Photo
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function storePhoto($filePath, $filename = null)
    {
        if (!file_exists($filePath)) {
            throw new \Exception(\Yii::t('app', 'File not exist :') . $filePath);
        }

        //Shell enabled path for image
        $shellPath = escapeshellarg($filePath);

        //SystemMd5Sum
        exec("md5sum {$shellPath}", $fileHash);

        $fileHash = md5(reset($fileHash));
        $fileType = pathinfo($filename ?: $filePath, PATHINFO_EXTENSION);
        $fileName = pathinfo($filename ?: $filePath, PATHINFO_FILENAME);
        $newFileName = $fileHash . '.' . $fileType;
        $fileDirPath = $this->getFilesDirPath($fileHash);

        //Where to store file on FS configured location
        $newFilePath = $fileDirPath . DIRECTORY_SEPARATOR . $newFileName;

        if (!file_exists($filePath)) {
            throw new \Exception(Yii::t('app', 'Cannot copy file! ') . $filePath . Yii::t('app', ' to ') . $newFilePath);
        }

        $exists = Photo::findOne([
            'identifier' => $fileHash
        ]);

        if (true /*!$exists*/) {
            //Open the file to write remotelly
            $stream = fopen($filePath, 'r+');

            //Push the file
            \Yii::$app->fs->writeStream($newFilePath, $stream);

            //Close trem not more needed, can be dropped
            fclose($stream);

            $file = new Photo();

            $file->name = $fileName;
            $file->filename = $fileName;
            $file->identifier = $fileHash;
            $file->type = $fileType;
            $file->mime = FileHelper::getMimeType($filePath);

            if ($file->save()) {
                //Get file metadata
                $metadata = FilesHelper::extractMetadata($filePath);

                //Store metadata to db
                PhotoData::storeMetadata($metadata, $file);

                //Remove temporary file
                unlink($filePath);

                try {
                    $stat = \Yii::createObject($this->statistics);
                    $ok = $stat->save($file);
                    if (!$ok) {
                        \Yii::getLogger()->log(FileModule::t('app', 'Statistics: error while saving'), Logger::LEVEL_WARNING);
                    }
                } catch (\Exception $exception) {
                    \Yii::getLogger()->log($exception->getMessage(), Logger::LEVEL_ERROR);
                }

                return $file;
            } else {
                print_r($file->getErrors());die;
                throw new \Exception(\Yii::t('app', "Cannot store file!"));
            }
        } else {
            //Remove temporary file
            unlink($filePath);

            return $exists;
        }
    }
}

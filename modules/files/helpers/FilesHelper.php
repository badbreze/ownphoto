<?php

namespace app\modules\files\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class FilesHelper extends FileHelper
{
    /**
     * @param $path
     * @return array
     */
    public static function extractMetadata($path)
    {
        if (false === $exifData = @exif_read_data($path, null, true, false)) {
            return array();
        }

        return self::parseMetadataSection($exifData);
    }

    /**
     * Create single level array
     * @param $section
     * @param null $key
     * @return array
     */
    public static function parseMetadataSection($section, $key = null)
    {
        $metadata = [];

        foreach ($section as $prop => $value) {
            $arrKey = $key ? "{$key}::{$prop}" : $prop;

            if (is_array($value)) {
                $subitems = self::parseMetadataSection($value, $arrKey);
                $metadata = ArrayHelper::merge($metadata, $subitems);
            } else {
                $metadata[$arrKey] = trim($value);
            }
        }

        return $metadata;
    }
}
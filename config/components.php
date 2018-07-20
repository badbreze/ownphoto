<?php
/**
 * @property \League\Flysystem\Adapter\AbstractAdapter Yii::$app->fs
 */

$db = require __DIR__ . '/db.php';

return [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'db' => $db,
    'errorHandler' => [
        'errorAction' => 'site/error',
    ],
    'fs' => [
        'class' => 'dosamigos\flysystem\SftpFsComponent',
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 5022,
        'timeout' => 60,
        'root' => '/storage/photos',
        'permPrivate' => 0700,
        'permPublic' => 0744,
        'privateKey' => '/var/www/photos/config/keys/photos'
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
        'useFileTransport' => true,
    ],
    'request' => [
        'class' => 'yii\web\Request',
        'enableCookieValidation' => false,
        'parsers' => [
            'application/json' => 'yii\web\JsonParser',
        ],
    ],
    'urlManager' => [
        'enablePrettyUrl' => true,
        //'enableStrictParsing' => true,
        'showScriptName' => false,
        'normalizer' => [
            'class' => 'yii\web\UrlNormalizer',
            // use temporary redirection instead of permanent for debugging
            'action' => \yii\web\UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
        ]
    ],
    'user' => [
        'identityClass' => 'app\models\User',
        'enableSession' => false
    ],
];
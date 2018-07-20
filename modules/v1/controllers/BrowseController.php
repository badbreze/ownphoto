<?php
/**
 * Media Browser Controller
 * Actions to esplore your photo world
 *
 * @license GPLv3
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3
 *
 * @package    ownphoto
 * @category   Platform
 * @author     Damian Gomez <racksoftgmail.com>
 */
namespace app\modules\v1\controllers;

use app\models\Photo;
use app\modules\v1\actions\browse\ActionDate;
use app\modules\v1\actions\browse\ActionIndex;
use app\modules\v1\controllers\base\BaseController;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\filters\VerbFilter;

class BrowseController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return [
            'index' => ['get'],
            'date' => ['get'],
            'view' => ['get'],
        ];
    }

    public function actions()
    {
        return [
            'index' => [
                'class' => ActionIndex::className(),
                'modelClass' => Photo::className()
            ],
            'date' => [
                'class' => ActionDate::className(),
                'modelClass' => Photo::className()
            ],
            'view' => [
                'class' => ActionDate::className(),
                'modelClass' => Photo::className()
            ],
        ];
    }
}
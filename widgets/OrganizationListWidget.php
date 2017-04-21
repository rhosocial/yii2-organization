<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\organization\widgets;

use rhosocial\organization\grid\OrganizationListActionColumn;
use Yii;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\web\ServerErrorHttpException;

/**
 * Class OrganizationListWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationListWidget extends Widget
{
    /**
     * @var ActiveDataProvider
     */
    public $dataProvider;

    const ACTION_COLUMN_DEFAULT = 'default';
    /**
     * @var array|null|string ActionColumn class configuration. Null if you do not need it.
     * 'default' if you want to use the OrganizationListActionColumn.
     * Note: If you want to use your own ActionColumn class configuration, please do not
     * forget to attach the 'class' key.
     */
    public $actionColumn;

    /**
     * @var array|null Additional columns' configuration arrays.
     * It will be appended after the existed columns.
     * If you do not need additional columns, please set null.
     */
    public $additionalColumns;

    public $showGUID = false;
    public $showType = true;
    public $showParent = true;
    public $showChildren = true;
    public $showCreator = true;
    public $showAdmin = true;
    public $showMember = true;

    /**
     * @var boolean|array Tips.
     * If you do not want to show tips, please set false.
     * If you want to show default tips, please set true.
     * If you want to show tips including default ones and your owns, please set an array contains all tip text.
     */
    public $tips = true;
    public $showCreatedAt = true;
    public $showUpdatedAt = false;
    /**
     * @var string|boolean
     */
    public $gridCaption = true;

    public function init()
    {
        if (empty($this->dataProvider)) {
            throw new ServerErrorHttpException('Invalid Organization Provider.');
        }
        if (is_string($this->actionColumn) && strtolower($this->actionColumn) == self::ACTION_COLUMN_DEFAULT) {
            $this->actionColumn = [
                'class' => OrganizationListActionColumn::class,
            ];
        }
        if ($this->gridCaption === true) {
            $this->gridCaption = Yii::t('organization', "Here are all the organizations / departments you have joined:");
        }
    }

    public function run()
    {
        return $this->render('organization-list', [
            'id' => 'organization-grid-view',
            'gridCaption' => $this->gridCaption,
            'dataProvider' => $this->dataProvider,
            'showGUID' => $this->showGUID,
            'showType' => $this->showType,
            'showParent' => $this->showParent,
            'showChildren' => $this->showChildren,
            'showCreator' => $this->showCreator,
            'showAdmin' => $this->showAdmin,
            'showMember' => $this->showMember,
            'additionalColumns' => $this->additionalColumns,
            'actionColumn' => $this->actionColumn,
            'tips' => $this->tips,
            'showCreatedAt' => $this->showCreatedAt,
            'showUpdatedAt' => $this->showUpdatedAt,
        ]);
    }
}

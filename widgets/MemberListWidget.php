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

use rhosocial\organization\Organization;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\web\ServerErrorHttpException;

/**
 * Class MemberListWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberListWidget extends Widget
{
    /**
     * @var Organization
     */
    public $organization;
    /**
     * @var ActiveDataProvider
     */
    public $dataProvider;
    public $tips = true;

    public function init()
    {
        if (empty($this->dataProvider)) {
            throw new ServerErrorHttpException('Invalid Member Provider.');
        }
    }

    public function run()
    {
        return $this->render('member-list', [
            'organization' => $this->organization,
            'dataProvider' => $this->dataProvider,
            'tips' => $this->tips,
        ]);
    }
}

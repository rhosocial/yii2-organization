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

use rhosocial\organization\forms\SettingsForm;
use rhosocial\organization\Organization;
use yii\base\Widget;

/**
 * Class SettingsFormWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SettingsFormWidget extends Widget
{
    /**
     * @var Organization
     */
    public $organization;
    /**
     * @var SettingsForm
     */
    public $model;

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('settings-form', [
            'organization' => $this->organization,
            'model' => $this->model,
        ]);
    }
}

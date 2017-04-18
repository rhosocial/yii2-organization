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
use rhosocial\organization\Profile;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ProfileFormWidget extends Widget
{
    /**
     * @var Organization 
     */
    public $organization;
    /**
     * @var Profile 
     */
    public $model;

    public function init()
    {
        if (is_null($this->model) || !($this->model instanceof Profile)) {
            if (!$this->organization) {
                throw new InvalidConfigException("Organization or Profile should either be valid.");
            }
            $this->model = $this->organization->createProfile(['scenario' => Profile::SCENARIO_UPDATE]);
        }
    }

    public function run()
    {
        return $this->render('profile-form', ['model' => $this->model]);
    }
}

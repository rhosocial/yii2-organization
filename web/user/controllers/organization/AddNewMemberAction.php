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

namespace rhosocial\organization\web\user\controllers\organization;

use yii\base\Action;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AddNewMemberAction extends Action
{
    /**
     * Add new member.
     * @param Organization|string|integer $organization
     * @return string rendering result.
     */
    public function run($organization)
    {
        return $this->controller->render($this->controller->viewBasePath . 'add-new-member');
    }
}

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

namespace rhosocial\organization\web\organization\controllers\my;

use rhosocial\organization\web\organization\Module;
use rhosocial\organization\Organization;
use rhosocial\user\User;
use Yii;
use yii\base\Action;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AddMemberAction extends Action
{
    public function run($org, $user = null)
    {
        $organization = Module::getOrganization($org);
        $user = Yii::$app->user->identity;
        MemberAction::checkAccess($organization, $user);
        return $this->controller->render('add-member');
    }
}

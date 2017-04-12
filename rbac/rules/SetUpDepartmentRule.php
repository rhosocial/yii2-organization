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

namespace rhosocial\organization\rbac\rules;

use rhosocial\user\User;
use rhosocial\user\rbac\Item;
use rhosocial\organization\Organization;
use Yii;
use yii\rbac\Rule;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpDepartmentRule extends Rule
{
    public $name = "canSetUpDepartment";

    /**
     * 
     * @param User $user
     * @param Item $item
     * @param array $params
     */
    public function execute($user, $item, $params)
    {
        $class = Yii::$app->user->identityClass;
        if (is_numeric($user) || is_int($user)) {
            $user = $class::find()->id($user)->one();
        }
        if (is_string($user)) {
            $user = $class::find()->guid($user)->one();
        }
        $organization = $params['organization'];
        /* @var $organization Organization */// The Organization or department which is parent.
        // If current user is creator of organization or department, he is allowed to set up a child.
        if (!$user->isOrganizationCreator($organization) && !$user->isOrganizationAdministrator($organization)) {
            return false;
        }
        if ($organization->hasReachedSubordinateLimit()) {
            return false;
        }
        return true;
    }
}

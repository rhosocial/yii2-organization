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
 * Class SetUpOrganizationRule
 * @package rhosocial\organization\rbac\rules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpOrganizationRule extends Rule
{
    public $name = 'canSetUpOrganization';

    /**
     * @param User|int|string $user
     * @param Item $item
     * @param array $params
     * @return boolean
     */
    public function execute($user, $item, $params)
    {
        $class = Yii::$app->user->identityClass;
        if (is_numeric($user) || is_int($user) || is_string($user)) {
            $user = $class::find()->guidOrId($user)->one();
        }
        /* @var $user User */
        if ($user->hasReachedOrganizationLimit()) {
            return false;
        }
        return true;
    }
}

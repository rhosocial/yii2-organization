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

namespace rhosocial\organization\tests\data\ar\user;

use rhosocial\organization\UserOrganizationTrait;
use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\org\Organization;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class User extends \rhosocial\user\User
{
    use UserOrganizationTrait;

    public function init()
    {
        $this->memberClass = Member::class;
        $this->organizationClass = Organization::class;
        parent::init();
    }
}

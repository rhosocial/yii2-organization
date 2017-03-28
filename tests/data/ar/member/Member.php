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

namespace rhosocial\organization\tests\data\ar\member;

use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\user\User;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Member extends \rhosocial\organization\Member
{
    public $hostClass = Organization::class;
    public $memberUserClass = User::class;
}

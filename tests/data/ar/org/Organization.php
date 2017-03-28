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

namespace rhosocial\organization\tests\data\ar\org;

use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\member\Member;

/**
 * @version 1.0
 * @author vistart <i@vistart.name>
 */
class Organization extends \rhosocial\organization\Organization
{
    public $profileClass = Profile::class;
    public $memberClass = Member::class;
}

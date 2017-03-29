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

/**
 * @version 1.0
 * @author vistart <i@vistart.name>
 */
class CreateInvalidOrganizationUser extends User
{
    public function createOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        return [];
    }
}

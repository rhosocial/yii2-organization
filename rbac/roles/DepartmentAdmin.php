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

namespace rhosocial\organization\rbac\roles;

use rhosocial\user\rbac\Role;

/**
 * This class described a `departAdmin` role, which assigned to the user who could
 * manage a department.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class DepartmentAdmin extends Role
{
    /**
     * @inheritdoc
     */
    public $name = 'departAdmin';
    
    /**
     * @inheritdoc
     */
    public $description = 'Department Administrator';
}

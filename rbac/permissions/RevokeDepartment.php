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

namespace rhosocial\organization\rbac\permissions;

use rhosocial\user\rbac\Permission;
use rhosocial\organization\rbac\rules\RevokeDepartmentRule;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RevokeDepartment extends Permission
{
    /**
     * @inheritdoc
     */
    public $name = 'revokeDepartment';
    
    /**
     * @inheritdoc
     */
    public $description = 'Revoke department.';

    public function init()
    {
        $this->ruleName = empty($this->ruleName) ? (new RevokeDepartmentRule)->name : $this->ruleName;
    }
}

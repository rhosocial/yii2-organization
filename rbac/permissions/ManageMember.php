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
use rhosocial\organization\rbac\rules\ManageMemberRule;

/**
 * This class described a `ManageMember` permission, which allows user
 * who had it to manage members of organization or department.
 * This permission will be automatically assigned to creator or administrators
 * of organization or department.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ManageMember extends Permission
{
    /**
     * @inheritdoc
     */
    public $name = 'manageOrganizationMember';
    
    /**
     * @inheritdoc
     */
    public $description = 'Manage organization member.';

    public function init()
    {
        $this->ruleName = empty($this->ruleName) ? (new ManageMemberRule)->name : $this->ruleName;
    }
}

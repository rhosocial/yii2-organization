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
use rhosocial\organization\rbac\rules\ManageProfileRule;

/**
 * This class described a `ManageProfile` permission, which allows user
 * who had it to manage profile of organization or department.
 * This permission will be automatically assigned to creator and administrators
 * of organization or department.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ManageProfile extends Permission
{
    /**
     * @inheritdoc
     */
    public $name = 'manageOrganizationProfile';
    
    /**
     * @inheritdoc
     */
    public $description = 'Manage organization profile.';

    public function init()
    {
        $this->ruleName = empty($this->ruleName) ? (new ManageProfileRule)->name : $this->ruleName;
    }
}

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

/**
 * This class described a `viewOrganization` permission, which allows user
 * who had it to view all organizations and their departments, as well as their
 * members.
 * This permission will not be assigned to anyone by default.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ViewOrganization extends Permission
{
    /**
     * @inheritdoc
     */
    public $name = 'viewOrganization';

    /**
     * @inheritdoc
     */
    public $description = 'View all organization(s), including their department(s).';
}

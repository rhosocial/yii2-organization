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

namespace rhosocial\organization\widgets;

use rhosocial\organization\Organization;
use yii\base\Widget;

/**
 * Class OrganizationChildrenModalWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationChildrenModalWidget extends Widget
{
    /**
     * @var Organization
     */
    public $organization;

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('organization-children-modal', [
            'organization' => $this->organization,
        ]);
    }
}

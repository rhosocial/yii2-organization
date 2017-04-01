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

namespace rhosocial\organization\tests\depart;

use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;
use Yii;

class NestedDepartmentTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     *
     * @var Organization[] 
     */
    protected $organizaitons;

    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $this->user->register([$this->user->createProfile(['nickname' => 'vistart'])]);
        Yii::$app->authManager->assign(new SetUpOrganization, $this->user);
    }

    protected function tearDown()
    {
        Organization::deleteAll();
        User::deleteAll();
        parent::tearDown();
    }

    /**
     * @group department
     */
    public function testNew()
    {
        
    }
}

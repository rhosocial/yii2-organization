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

use rhosocial\organization\tests\data\ar\depart\Department;
use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class DepartmentTest extends TestCase
{
    /**
     * @var User 
     */
    protected $user;
    protected $orgName;
    /**
     * @var Organization 
     */
    protected $organization;
    /**
     * @var Department 
     */
    protected $department;

    protected function setUp()
    {
        parent::setUp();
        $this->organization = new Organization();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));
    }

    protected function tearDown()
    {
        if ($this->organization instanceof Organization)
        {
            try {
                if (!$this->organization->getIsNewRecord()) {
                    $this->organization->deregister();
                }
            } catch (\Exception $ex) {

            }
            $this->organization = null;
        }
        Organization::deleteAll();
        if ($this->user instanceof User)
        {
            try {
                if (!$this->user->getIsNewRecord()) {
                    $this->user->deregister();
                }
            } catch (\Exception $ex) {

            }
            $this->user = null;
        }
        User::deleteAll();
        parent::tearDown();
    }

    /**
     * @group department
     * @group profile
     */
    public function testNew()
    {
        $this->orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($this->orgName));
        $this->assertTrue($this->user->setUpDepartment($this->user->lastSetUpOrganization, $this->orgName . '-department'));
    }
}

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

namespace rhosocial\organization\tests\member;

use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use Yii;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AdministratorTest extends TestCase
{
    /**
     * @var User 
     */
    protected $user;
    protected $user1;
    protected $orgName;
    /**
     * @var Organization 
     */
    protected $organization;

    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));

        $this->user1 = new User(['password' => '123456']);
        $profile1 = $this->user1->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user1->register([$profile1]));

        $this->orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($this->orgName));
        $this->organization = $this->user->lastSetUpOrganization;
    }

    /**
     * @group member
     * @group admin
     */
    public function testNew()
    {
        $this->assertFalse($this->user1->isOrganizationAdministrator($this->organization));
        $member = $this->user1;
        $this->assertTrue($this->organization->addMember($member));
        $this->assertFalse($this->user1->isOrganizationAdministrator($this->organization));
        //$this->assertTrue($this->organization->addAdministrator($this->user1));
        //$this->assertTrue($this->user1->isOrganizationAdministrator($this->organization));
    }

    /**
     * @group member
     * @group admin
     */
    public function testAddAdministratorDirectly()
    {
        $this->assertFalse($this->user1->isOrganizationAdministrator($this->organization));
        $this->assertTrue($this->organization->addAdministrator($this->user1));
        $this->assertTrue($this->user1->isOrganizationAdministrator($this->organization));
    }
}

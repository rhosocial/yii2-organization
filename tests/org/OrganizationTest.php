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

namespace rhosocial\organization\tests\org\models;

use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationTest extends TestCase
{
    /**
     * @var User 
     */
    protected $user;
    /**
     * @var Organization; 
     */
    protected $organization;

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
     * @group organization
     * @group register
     */
    public function testNew()
    {
        try {
            $result = $this->organization->register();
            if ($result instanceof \Exception) {
                throw $result;
            }
            $this->assertTrue($result);
        } catch (\Exception $ex) {
            $this->fail(get_class($ex) . ' : ' . $ex->getMessage());
        }
    }

    /**
     * @group organization
     * @group user
     * @group member
     */
    public function testUser()
    {
        try {
            $result = $this->user->setUpOrganization('Organization');
            if ($result !== true) {
                throw new \Exception('Failed to set up.');
            }
        } catch (\Exception $ex) {
            $this->fail(get_class($ex), ' : ' . $ex->getMessage());
        }
        $this->assertTrue($result);
        
        $member = $this->user->getOfMembers()->one();
        $this->assertInstanceOf(Member::class, $member);
        $organization = $this->user->getAtOrganizations()->one();
        $this->assertInstanceOf(Organization::class, $organization);
    }

    /**
     * @group organization
     * @group profile
     * @group user
     * @group member
     * @depends testUser
     */
    public function testOrganizationProfile()
    {
        $orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($orgName));
        $organization = $this->user->getAtOrganizations()->one();
        /* @var $organization Organization */
        $this->assertInstanceOf(Organization::class, $organization);
        $profile = $organization->profile;
        /* @var $profile Profile */
        $this->assertEquals($orgName, $profile->name);
    }

    /**
     * @group organization
     * @group profile
     * @group user
     * @group member
     * @depends testUser
     */
    public function testOrganizationMember()
    {
        $orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($orgName));
        $member = $this->user->getOfMembers()->one();
        /* @var $member Member */
        $this->assertInstanceOf(Member::class, $member);
        
        $organization = $this->user->getAtOrganizations()->one();
        /* @var $organization Organization */
        $this->assertInstanceOf(Organization::class, $organization);
        
        $this->assertEquals($organization->getGUID(), $member->{$member->createdByAttribute});
        $this->assertEquals($this->user->getGUID(), $member->{$member->memberAttribute});
    }

    /**
     * @group organization
     * @group profile
     * @group user
     * @group member
     * @depends testUser
     */
    public function testFindOrganization()
    {
        $orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($orgName));
        $organization = $this->user->getAtOrganizations()->one();
        /* @var $organization Organization */
        $foundModel = $this->user->getAtOrganizations()->andWhere([$organization->guidAttribute => $organization->getGUID()])->one();
        $this->assertEquals($organization->getGUID(), $foundModel->getGUID());
    }
}

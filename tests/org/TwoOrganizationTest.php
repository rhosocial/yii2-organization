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

namespace rhosocial\organization\tests\data\org\models;

use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;

/**
 * The test case simulates the registration of two organizations.
 * Most test cases are premised on the fact that the two organizations have
 * successfully registered, and both organizations have their own profiles.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class TwoOrganizationTest extends TestCase
{
    /**
     * @var User 
     */
    protected $user;

    /**
     * @var Organization; 
     */
    protected $organization1;

    /**
     * @var Organization
     */
    protected $organization2;

    /**
     * @var string 
     */
    protected $orgName1;

    /**
     * @var string 
     */
    protected $orgName2;

    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));
        $this->orgName1 = $this->faker->lastName;
        $this->orgName2 = $this->faker->lastName;
        if ($this->orgName1 == $this->orgName2) {
            $this->orgName2 .= '1';
        }
        $this->assertTrue($this->user->setUpOrganization($this->orgName1));
        $this->organization1 = $this->user->lastSetUpOrganization;
        $this->assertTrue($this->user->setUpOrganization($this->orgName2));
        $this->organization2 = $this->user->lastSetUpOrganization;
    }

    protected function tearDown()
    {
        if ($this->organization1 instanceof Organization)
        {
            try {
                if (!$this->organization1->getIsNewRecord()) {
                    $this->organization1->deregister();
                }
            } catch (\Exception $ex) {

            }
            $this->organization = null;
        }
        if ($this->organization2 instanceof Organization)
        {
            try {
                if (!$this->organization2->getIsNewRecord()) {
                    $this->organization2->deregister();
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
     * Registration must be successful.
     * At the point, there should be two registered organizations.
     * Each organization has its own profile.
     *
     * @group organization
     * @group profile
     * @group user
     * @group member
     */
    public function testSetUp()
    {
        $organizations = $this->user->atOrganizations;
        $this->assertCount(2, $organizations);
        foreach ($organizations as $org)
        {
            $this->assertInstanceOf(Organization::class, $org);
            $this->assertInstanceOf(Profile::class, $org->profile);
        }
    }

    /**
     * @group organization
     * @group profile
     * @group user
     * @group member
     * @depends testSetUp
     */
    public function testFind()
    {
        $results = $this->user->getAtOrganizations()->andWhere([$this->organization1->guidAttribute => $this->organization1->getGUID()])->all();
        $this->assertCount(1, $results);
        foreach ($results as $org)
        {
            $this->assertInstanceOf(Organization::class, $org);
            $this->assertInstanceOf(Profile::class, $org->profile);
            $this->assertEquals($this->orgName1, $org->profile->name);
        }

        $results = $this->user->getAtOrganizations()->andWhere([$this->organization2->guidAttribute => $this->organization2->getGUID()])->all();
        $this->assertCount(1, $results);
        foreach ($results as $org)
        {
            $this->assertInstanceOf(Organization::class, $org);
            $this->assertInstanceOf(Profile::class, $org->profile);
            $this->assertEquals($this->orgName2, $org->profile->name);
        }

        $results = $this->user->getAtOrganizations()->andWhere([$this->organization2->guidAttribute => $this->faker->lastName])->all();
        $this->assertCount(0, $results);
    }
}

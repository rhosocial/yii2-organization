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

namespace rhosocial\organization\tests\org;

use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use Yii;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationChildrenTest extends TestCase
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
        Yii::$app->authManager->assign(new SetUpOrganization, $this->user);
        $this->orgName1 = $this->faker->lastName;
        $this->orgName2 = $this->faker->lastName;
        if ($this->orgName1 == $this->orgName2) {
            $this->orgName2 .= '1';
        }
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
            $this->organization1 = null;
        }
        if ($this->organization2 instanceof Organization)
        {
            try {
                if (!$this->organization2->getIsNewRecord()) {
                    $this->organization2->deregister();
                }
            } catch (\Exception $ex) {

            }
            $this->organization1 = null;
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
     * @group parent
     * @group child
     */
    public function testSetUp()
    {
        $this->assertTrue($this->user->setUpOrganization($this->orgName1));
        $this->organization1 = $this->user->lastSetUpOrganization;
        $this->assertInstanceOf(Organization::class, $this->organization1);
        $this->assertEquals($this->orgName1, $this->organization1->profile->name);
        $this->assertTrue($this->user->setUpOrganization($this->orgName2, $this->user->lastSetUpOrganization));
        $this->organization2 = $this->user->lastSetUpOrganization;
        $this->assertInstanceOf(Organization::class, $this->organization2);
        $this->assertEquals($this->orgName2, $this->organization2->profile->name);
    }

    /**
     * @group organization
     * @group parent
     * @group child
     */
    public function testChildren()
    {
        $this->assertTrue($this->user->setUpOrganization($this->orgName1));
        $this->organization1 = $this->user->lastSetUpOrganization;
        $this->assertTrue($this->user->setUpDepartment($this->orgName2, $this->user->lastSetUpOrganization));
        $this->organization2 = $this->user->lastSetUpOrganization;
        $children = $this->organization1->children;
        $this->assertCount(1, $children);
        foreach ($children as $child)
        {
            $this->assertInstanceOf(Organization::class, $child);
            $this->assertEquals($this->orgName2, $child->profile->name);
        }
    }

    /**
     * @group organization
     * @group parent
     * @group child
     */
    public function testParent()
    {
        $this->assertTrue($this->user->setUpOrganization($this->orgName1));
        $this->organization1 = $this->user->lastSetUpOrganization;
        $this->assertTrue($this->user->setUpDepartment($this->orgName2, $this->user->lastSetUpOrganization));
        $this->organization2 = $this->user->lastSetUpOrganization;
        $parent = $this->organization2->parent;
        $this->assertInstanceOf(Organization::class, $parent);
        $this->assertEquals($this->orgName1, $parent->profile->name);
    }
}

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

use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\profile\Profile;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\data\ar\user\CreateInvalidDepartmentUser;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\organization\tests\TestCase;
use Yii;
use yii\base\InvalidConfigException;

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

    protected function setUp()
    {
        parent::setUp();
        $this->organization = new Organization();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));
        Yii::$app->authManager->assign(new SetUpOrganization, $this->user);
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
        $this->assertTrue($this->user->lastSetUpOrganization->isOrganization());
        $this->assertFalse($this->user->lastSetUpOrganization->isDepartment());
        $this->assertTrue($this->user->setUpDepartment($this->orgName . '-department', $this->user->lastSetUpOrganization));
        $this->assertFalse($this->user->lastSetUpOrganization->isOrganization());
        $this->assertTrue($this->user->lastSetUpOrganization->isDepartment());
    }

    /**
     * @group department
     * @group profile
     */
    public function testSetUpInvalid()
    {
        $this->user = new CreateInvalidDepartmentUser(['password' => '123456']);
        $this->assertTrue($this->user->register([$this->user->createProfile(['nickname' => 'vistart'])]));
        Yii::$app->authManager->assign(new SetUpOrganization, $this->user);
        $this->orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($this->orgName));
        try {
            $this->user->setUpDepartment($this->orgName . '-department', $this->user->lastSetUpOrganization);
            $this->fail();
        } catch (InvalidConfigException $ex) {
            $this->assertEquals('Invalid Organization Model.', $ex->getMessage());
        }
    }

    /**
     * @group department
     * @group profile
     */
    public function testInvalidParent()
    {
        $this->orgName = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($this->orgName));
        try {
            $this->assertTrue($this->user->setUpDepartment($this->orgName . 'department'));
            $this->fail();
        } catch (\yii\base\InvalidParamException $ex) {
            $this->assertEquals('Invalid Parent Parameter.', $ex->getMessage());
        }
    }
}

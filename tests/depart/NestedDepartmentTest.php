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
use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;
use Yii;
use yii\base\InvalidParamException;

class NestedDepartmentTest extends TestCase
{
    /**
     * @var User
     */
    protected $users;

    /**
     * @var integer 
     */
    public $userCount = 5;

    /**
     *
     * @var Organization[] 
     */
    protected $organizations;

    /**
     * @var integer 
     */
    public $organizationCount = 5;

    protected function setUp()
    {
        parent::setUp();
        for ($i = 0; $i < $this->userCount; $i++) {
            $this->users[$i] = new User(['password' => '123456']);
            $this->users[$i]->register([$this->users[$i]->createProfile(['nickname' => "vistart$i"])]);
        }
        Yii::$app->authManager->assign(new SetUpOrganization, $this->users[0]);
        $this->assertTrue($this->users[0]->setUpOrganization("org0"));
        $this->organizations[0] = $this->users[0]->lastSetUpOrganization;
    }

    protected function tearDown()
    {
        Organization::deleteAll();
        User::deleteAll();
        parent::tearDown();
    }

    /**
     * One organization, Four child departments.
     * One creator and four administrators of organization.
     * Each administrator set up a department.
     * @group department
     */
    public function testOneOrganizationFourDepartments()
    {
        $members = [];
        for ($i = 1; $i < $this->userCount; $i++) {
            $members[$i] = $this->users[$i];
            $this->assertTrue($this->organizations[0]->addMember($members[$i]));
        }
        $users = $this->organizations[0]->memberUsers;
        $this->assertCount($this->userCount, $users);
        for ($i = 1; $i < $this->userCount; $i++) {
            $this->assertTrue($this->organizations[0]->hasMember($this->users[$i]));
            $this->assertFalse($this->organizations[0]->hasAdministrator($this->users[$i]));
            $this->assertTrue($this->organizations[0]->addAdministrator($this->users[$i]));
            $this->assertTrue($this->organizations[0]->hasAdministrator($this->users[$i]));
            $this->assertTrue($this->users[$i]->setUpDepartment("org$i", $this->organizations[0]));
            $this->organizations[$i] = $this->users[$i]->lastSetUpOrganization;
            $this->assertTrue($this->users[$i]->isOrganizationCreator($this->organizations[$i]));
            $this->assertFalse($this->users[$i]->isOrganizationAdministrator($this->organizations[$i]));
        }
        for ($i = 1; $i < $this->userCount; $i++) {
            $this->assertFalse($this->users[$i]->isOrganizationCreator($this->organizations[$this->userCount - $i]));
            $this->assertFalse($this->users[$i]->isOrganizationAdministrator($this->organizations[$this->userCount - $i]));
            try {
                $this->users[$i]->revokeOrganization($this->organizations[$this->userCount - $i]);
                $this->fail();
            } catch (InvalidParamException $ex) {
                $this->assertEquals("You do not have permission to revoke it.", $ex->getMessage());
            }
        }
    }

    /**
     * One organization.
     * Each department has a child department.
     * @group department
     */
    public function testOneOrganizationNestedDepartments()
    {
        $users = $this->organizations[0]->memberUsers;
        $this->assertCount(1, $users);
        for ($i = 1; $i < $this->userCount; $i++)
        {
            try {
                $this->users[$i]->setUpDepartment("org$i", $this->organizations[$i - 1]);
                $this->fail("Exception should be thrown.");
            } catch (\Exception $ex) {
                $this->assertEquals("You do not have permission to set up department.", $ex->getMessage());
            }
            $this->assertTrue($this->organizations[$i - 1]->addAdministrator($this->users[$i]));
            $this->assertTrue($this->users[$i]->setUpDepartment("org$i", $this->organizations[$i - 1]));
            $this->organizations[$i] = $this->users[$i]->lastSetUpOrganization;
        }
        for ($i = 0; $i < $this->userCount - 1; $i++) {
            for ($j = $i + 1; $j < $this->userCount; $j++) {
                $this->assertTrue($this->organizations[$j]->hasAncestor($this->organizations[$i]));
            }
        }
        $this->assertTrue($this->users[0]->revokeOrganization($this->organizations[0]));
        for ($i = 1; $i < $this->userCount; $i++) {
            $this->assertNull(Organization::find()->id($this->organizations[$i]->getID())->one());
            $this->assertEmpty(Member::findAll(['organization_guid' => $this->organizations[$i]->getGUID()]));
        }
    }
}

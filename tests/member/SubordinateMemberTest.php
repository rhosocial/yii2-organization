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

use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;
use Yii;

/**
 * Class SubordinateMemberTest
 * @package rhosocial\organization\tests\member
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SubordinateMemberTest extends TestCase
{
    /**
     * @var User[]
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

    /**
     * Prepare a user with `setUpOrganization` permission.
     * And set up an organization.
     */
    protected function setUp()
    {
        parent::setUp();
        for ($i = 0; $i < $this->userCount; $i++) {
            $this->users[$i] = new User(['password' => '123456']);
            $this->assertTrue($this->users[$i]->register([$this->users[$i]->createProfile(['nickname' => "vistart$i"])]));
        }
        Yii::$app->authManager->assign((new SetUpOrganization)->name, $this->users[0]);
        $this->assertTrue($this->users[0]->setUpOrganization("org0"));
        $this->organizations[0] = $this->users[0]->lastSetUpOrganization;

        for ($i = 1; $i < $this->userCount; $i++) {
            $this->assertTrue($this->organizations[$i - 1]->addAdministrator($this->users[$i]));
            $this->assertTrue($this->users[$i]->setUpDepartment($this->faker->name, $this->organizations[$i - 1]));
            $this->organizations[$i] = $this->users[$i]->lastSetUpOrganization;
        }
    }

    protected function tearDown()
    {
        Organization::deleteAll();
        User::deleteAll();
        parent::tearDown();
    }

    /**
     * @group member
     * @group subordinate
     */
    public function testStatus()
    {
        for ($i = 0; $i < $this->userCount; $i++) {
            $this->assertEquals($this->organizations[$i]->creator->getGUID(), $this->users[$i]->getGUID());
        }
        for ($i = 0; $i < $this->userCount - 1; $i++) {
            $this->assertCount(2, $this->organizations[$i]->members);
        }
        $this->assertCount(1, $this->organizations[$this->userCount - 1]->members);
    }

    /**
     * @group member
     * @group subordinate
     */
    public function testSubordinateMember()
    {
        for ($i = 0; $i < $this->userCount - 1; $i++) {
            $this->assertTrue($this->organizations[$i]->hasMember($this->users[$i]));
            for ($j = $i + 1; $j < $this->userCount; $j++) {
                $this->assertTrue($this->organizations[$i]->hasMemberInSubordinates($this->users[$j]), "Users[$j] should be the member of sub of Org[$i].");
                for ($k = $j; $k >= 0; $k--) {
                    $this->assertFalse($this->organizations[$j]->hasMemberInSubordinates($this->users[$k]));
                }
            }
        }
    }
}

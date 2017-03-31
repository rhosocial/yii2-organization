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
class TwoOrganizationsMemberTest extends TestCase
{
    /**
     * @var User 
     */
    protected $user;
    protected $user1;
    protected $orgName1;
    protected $orgName2;
    /**
     * @var Organization 
     */
    protected $organization1;
    protected $organization2;

    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));
        $this->user1 = new User(['password' => '123456']);
        $profile1 = $this->user1->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user1->register([$profile1]));
        $this->orgName1 = $this->faker->lastName;
        $this->assertTrue($this->user->setUpOrganization($this->orgName1));
        $this->organization1 = $this->user->lastSetUpOrganization;
        sleep(1);
        $this->orgName2 = $this->faker->lastName;
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
            $this->organization2 = null;
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
     * @group member
     */
    public function testTwoOrganizations()
    {
        $members = $this->user->getOfMembers()->orderByCreatedAt(SORT_ASC)->all();
        $this->assertCount(2, $members);
        foreach ($members as $member)
        {
            $this->assertEquals($this->user->getGUID(), $member->user_guid);
        }
        $this->assertEquals($this->organization1->getGUID(), $members[0]->{$members[0]->createdByAttribute});
        $this->assertEquals($this->organization2->getGUID(), $members[1]->{$members[1]->createdByAttribute});
    }

    /**
     * @group member
     * @group role
     */
    public function testMemberRole()
    {
        $assignments = Yii::$app->authManager->getAssignments($this->user);
        $count = 0;
        foreach ($assignments as $assignment)
        {
            if ($assignment->roleName == (new OrganizationCreator)->name) {
                $count++;
            }
        }
        $this->assertEquals(1, $count);
        
        $this->user->revokeOrganization($this->user->lastSetUpOrganization);
        
        $assignments = Yii::$app->authManager->getAssignments($this->user);
        $count = 0;
        foreach ($assignments as $assignment)
        {
            if ($assignment->roleName == (new OrganizationCreator)->name) {
                $count++;
            }
        }
        $this->assertEquals(1, $count);
        
        $this->user->revokeOrganization($this->organization1);
        
        $assignments = Yii::$app->authManager->getAssignments($this->user);
        $count = 0;
        foreach ($assignments as $assignment)
        {
            if ($assignment->roleName == (new OrganizationCreator)->name) {
                $count++;
            }
        }
        $this->assertEquals(0, $count);
    }
}

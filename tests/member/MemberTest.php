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

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberTest extends TestCase
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
     * @group member
     */
    public function testNew()
    {
        $members = $this->organization->members;
        $this->assertCount(1, $members);
        foreach ($members as $member) {
            $this->assertEquals($member->{$member->memberAttribute}, $this->user->getGUID());
        }
        $users = $this->organization->memberUsers;
        $this->assertCount(1, $users);
        foreach ($users as $user)
        {
            $this->assertEquals($user->getGUID(), $this->user->getGUID());
        }
    }

    /**
     * @group member
     */
    public function testGetMember()
    {
        $member = $this->organization->getMember($this->user);
        $this->assertInstanceOf(Member::class, $member);
        $this->assertEquals($this->user->getGUID(), $member->{$member->memberAttribute});
    }

    /**
     * @group member
     */
    public function testAddMember()
    {
        $member = $this->user1;
        $this->assertTrue($this->organization->addMember($member));
        $this->assertInstanceOf(Member::class, $member);
        $this->assertEquals($this->user1->getGUID(), $member->{$member->memberAttribute});
        
        $members = $this->organization->members;
        $this->assertCount(2, $members);
        $users = $this->organization->memberUsers;
        $this->assertCount(2, $users);
    }

    /**
     * @group member
     * @depends testAddMember
     */
    public function testGetMemberAfterAddMember()
    {
        $member = $this->user1;
        $this->assertTrue($this->organization->addMember($member));
        $model = $this->organization->getMember($member->{$member->memberAttribute});
        $this->assertInstanceOf(Member::class, $model);
        $this->assertEquals($this->user1->getGUID(), $model->{$model->memberAttribute});
    }

    /**
     * @group member
     * @depends testAddMember
     */
    public function testRemoveMemberAfterAdding()
    {
        $member = $this->user1;
        $this->assertTrue($this->organization->addMember($member));
        $this->assertCount(2, $this->organization->members);
        $this->assertTrue($this->organization->removeMember($member));
        $this->assertCount(1, $this->organization->getMembers()->all());
    }
}

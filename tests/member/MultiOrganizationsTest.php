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
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\organization\tests\TestCase;
use Yii;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MultiOrganizationsTest extends TestCase
{
    protected $user;
    protected $users;
    protected $organizations;
    public $organizationCount = 5;
    
    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));
        Yii::$app->authManager->assign(new SetUpOrganization, $this->user);
        
        for ($i = 0; $i < $this->organizationCount = 5; $i++) {
            $this->assertTrue($this->user->setUpOrganization("org$i"));
            $this->organizations[$this->user->lastSetUpOrganization->getGUID()] = $this->user->lastSetUpOrganization;
            $this->users[$i] = new User(['password' => '123456']);
            $this->assertTrue($this->users[$i]->register([$this->users[$i]->createProfile(['nickname' => 'vistsart'])]));
            $this->user->lastSetUpOrganization->addAdministrator($this->users[$i]);
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
     * @group administrator
     */
    public function testNew()
    {
        unset($this->user->atOrganizations);
        $organizations = $this->user->atOrganizations;
        $this->assertCount($this->organizationCount, $organizations);
    }

    /**
     * @group member
     * @group administrator
     */
    public function testCreators()
    {
        $organizations = $this->user->creatorsAtOrganizations;
        $this->assertCount($this->organizationCount, $organizations);
        foreach ($this->users as $user)
        {
            $this->assertCount(0, $user->creatorsAtOrganizations);
            $this->assertFalse($user->isOrganizationCreator($organizations[0]));
        }
        foreach ($organizations as $org)
        {
            $this->assertEquals($this->user->getGUID(), $org->creator->getGUID());
        }
    }

    /**
     * @group member
     * @group administrator
     */
    public function testAdministrators()
    {
        $organizations = $this->user->administratorsAtOrganizations;
        $this->assertCount(0, $organizations);
        foreach ($this->users as $user)
        {
            $orgs = $user->administratorsAtOrganizations;
            $this->assertCount(1, $orgs);
            $this->assertTrue($orgs[0]->hasAdministrator($user));
            $admins = $orgs[0]->administrators;
            $this->assertCount(1, $admins);
            $this->assertEquals($user->getGUID(), $admins[0]->getGUID());
        }
    }
}

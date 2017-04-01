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
    protected $organizations = [];
    public $organizationCount = 5;

    protected function setUp()
    {
        parent::setUp();
        $this->user = new User(['password' => '123456']);
        $profile = $this->user->createProfile(['nickname' => 'vistart']);
        $this->assertTrue($this->user->register([$profile]));
        Yii::$app->authManager->assign(new SetUpOrganization, $this->user);
        
        for ($i = 0; $i < $this->organizationCount; $i++) {
            $this->assertTrue($this->user->setUpOrganization("org$i"));
            $this->organizations[$this->user->lastSetUpOrganization->getGUID()] = $this->user->lastSetUpOrganization;
        }
    }

    protected function tearDown()
    {
        foreach ($this->organizations as $org) {
            try {
                $this->user->revokeOrganization($org);
            } catch (\Exception $ex) {
                
            }
        }
        $this->assertTrue($this->user->deregister());
        Organization::deleteAll();
        User::deleteAll();
        parent::tearDown();
    }

    /**
     * @group organization
     */
    public function testNew()
    {
        $organizations = $this->user->atOrganizations;
        $this->assertCount($this->organizationCount, $organizations);
    }

    /**
     * @group organization
     */
    public function testCreatorsAtOrganizations()
    {
        $organizations = $this->user->creatorsAtOrganizations;
        $this->assertCount($this->organizationCount, $organizations);
        foreach ($organizations as $org) {
            if (array_key_exists($org->getGUID(), $this->organizations)) {
                unset($this->organizations[$org->getGUID()]);
            }
        }
        $this->assertCount(0, $this->organizations);
    }

    /**
     * @group organization
     */
    public function testAdministratorsAtOrganizations()
    {
        $organizations = $this->user->administratorsAtOrganizations;
        $this->assertCount(0, $organizations);
    }
}

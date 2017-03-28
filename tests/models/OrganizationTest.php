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

namespace rhosocial\organization\tests\models;

use rhosocial\organization\tests\data\ar\member\Member;
use rhosocial\organization\tests\data\ar\org\Organization;
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
        $this->assertTrue($this->user->register());
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
     */
    public function testUser()
    {
        try {
            $result = $this->user->setUpOrganization('Organization');
        } catch (\Exception $ex) {
            $this->fail(get_class($ex), ' : ' . $ex->getMessage());
        }
    }
}
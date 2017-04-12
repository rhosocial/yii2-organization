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
use rhosocial\organization\tests\data\ar\user\OrganizationLimit;
use rhosocial\organization\tests\data\ar\user\User;
use rhosocial\organization\tests\TestCase;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use Yii;
use yii\base\InvalidParamException;

/**
 * Class OrganizationLimitTest
 * @package rhosocial\organization\tests\org
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationLimitTest extends TestCase
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
        $this->user = new User(['password' => '123456']);
        $this->assertTrue($this->user->register([$this->user->createProfile(['nickname' => 'vistart'])]));
        $this->assertNotNull(Yii::$app->authManager->assign((new SetUpOrganization)->name, $this->user));

    }
    protected function tearDown()
    {
        $this->assertTrue($this->user->deregister());
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    /**
     * @group organization
     * @group setup
     * @group limit
     */
    public function testNormal()
    {
        $limit = OrganizationLimit::find()->createdBy($this->user)->one();
        $this->assertNull($limit);

        $limit = OrganizationLimit::getLimit($this->user);
        $noInit = OrganizationLimit::buildNoInitModel();
        $this->assertEquals($noInit->defaultLimit, $limit);

        $limit = OrganizationLimit::find()->createdBy($this->user)->one();
        /* @var $limit OrganizationLimit */
        $this->assertInstanceOf(OrganizationLimit::class, $limit);
        $this->assertEquals($limit->defaultLimit, $limit->limit);
        $this->assertFalse($this->user->hasReachedOrganizationLimit());

        $result = $this->user->setUpOrganization($this->faker->name);
        $this->assertTrue($result);
        $this->assertEquals(1, (int)$this->user->getCreatorsAtOrganizations()->andWhere(['type' => Organization::TYPE_ORGANIZATION])->count());
        $limit = OrganizationLimit::getLimit($this->user);
        $this->assertEquals($noInit->defaultLimit, $limit);
        $this->assertFalse($this->user->hasReachedOrganizationLimit());
    }

    /**
     * @group organization
     * @group setup
     * @group limit
     */
    public function testLimit()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($this->user->setUpOrganization($this->faker->name));
        }
        $this->assertEquals(10, OrganizationLimit::getLimit($this->user));
        try {
            $this->user->setUpOrganization($this->faker->name);
        } catch (InvalidParamException $ex) {
            $this->assertEquals("You do not have permission to set up organization.", $ex->getMessage());
        }
    }
}

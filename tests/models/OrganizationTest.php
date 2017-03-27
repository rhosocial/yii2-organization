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

use rhosocial\organization\tests\data\ar\org\Organization;
use rhosocial\organization\tests\TestCase;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationTest extends TestCase
{
    protected $organization;

    protected function setUp()
    {
        parent::setUp();
        $this->organization = new Organization();
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
}
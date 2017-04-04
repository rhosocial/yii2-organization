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

namespace rhosocial\organization\tests;

use Faker\Factory;
use Faker\Generator;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;
use yii\db\Connection;

/**
 * Description of TestCase
 *
 * @author vistart <i@vistart.me>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase {

    public static $params;
    
    /**
     *
     * @var Generator 
     */
    protected $faker = null;

    /**
     * Returns a test configuration param from /data/config.php
     * @param  string $name params name
     * @param  mixed $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null) {
        if (static::$params === null) {
            static::$params = require(__DIR__ . '/data/config.php');
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown() {
        parent::tearDown();
        $this->destroyApplication();
        $this->faker = null;
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application') {
        new $appClass(ArrayHelper::merge([
                    'id' => 'testapp',
                    'basePath' => __DIR__,
                    'vendorPath' => dirname(__DIR__) . '/vendor',
                    'timeZone' => 'Asia/Shanghai',
                        ], $config));
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application') {
        new $appClass(ArrayHelper::merge([
                    'id' => 'testapp',
                    'basePath' => __DIR__,
                    'vendorPath' => dirname(__DIR__) . '/vendor',
                    'timeZone' => 'Asia/Shanghai',
                    'components' => [
                        'i18n' => [
                            'translations' => [
                                'user*' => [
                                    'class' => 'yii\i18n\PhpMessageSource',
                                    'basePath' => dirname(__DIR__) . '/vendor/rhosocial/yii2-user/messages',
                                    'sourceLanguage' => 'en-US',
                                    'fileMap' => [
                                        'user' => 'user.php',
                                    ],
                                ],
                                'organization*' => [
                                    'class' => 'yii\i18n\PhpMessageSource',
                                    'basePath' => dirname(__DIR__) . '/messages',
                                    'sourceLanguage' => 'en-US',
                                    'fileMap' => [
                                        'organization' => 'organization.php',
                                    ],
                                ],
                            ],
                        ],
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                        'user' => [
                            'class' => 'rhosocial\organization\tests\data\web\User',
                            'identityClass' => 'rhosocial\organization\tests\data\ar\user\User',
                            'enableAutoLogin' => true,
                        ],
                        'authManager' => [
                            'class' => 'rhosocial\user\rbac\DbManager',
                        ],
                    ]
                        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication() {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
        $this->faker->seed(time() % 1000000);
    }

    protected function setUp() {
        $databases = self::getParam('databases');
        $params = isset($databases['mysql']) ? $databases['mysql'] : null;
        if ($params === null) {
            $this->markTestSkipped('No mysql server connection configured.');
        }
        if (array_key_exists('class', $params)) {
            unset($params['class']);
        }
        $connection = new Connection($params);
        $cacheParams = self::getParam('cache');/*
        if ($cacheParams === null) {
            $this->markTestSkipped('No cache component configured.');;
        }*/
        $this->mockWebApplication(['components' => ['db' => $connection, 'cache' => $cacheParams]]);

        parent::setUp();
    }

    /**
     * @param  boolean    $reset whether to clean up the test database
     * @return Connection
     */
    public function getConnection($reset = true) {
        $databases = self::getParam('databases');
        $params = isset($databases['mysql']) ? $databases['mysql'] : [];
        $db = new Connection($params);
        if ($reset) {
            $db->open();
            //$db->flushdb();
        }

        return $db;
    }

}

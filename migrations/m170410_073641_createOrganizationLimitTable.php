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

namespace rhosocial\organization\migrations;

use rhosocial\user\User;
use rhosocial\organization\Organization;
use rhosocial\organization\OrganizationLimit;
use rhosocial\user\migrations\Migration;

/**
 * Create Organization Limit Table.
 *
 * This migration is equivalent to:
```SQL
CREATE TABLE `organization_limit` (
`guid` varbinary(16) NOT NULL COMMENT 'GUID',
`user_guid` varbinary(16) NOT NULL COMMENT 'User GUID',
`limit` int(11) unsigned NOT NULL DEFAULT '10' COMMENT 'Limit',
`ip` varbinary(16) NOT NULL DEFAULT '0' COMMENT 'IP Address',
`ip_type` tinyint(3) NOT NULL DEFAULT '4' COMMENT 'IP Address Type',
`created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Created At',
`updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Updated At',
PRIMARY KEY (`guid`),
UNIQUE KEY `organization_limit_user_unique` (`user_guid`),
CONSTRAINT `organization_limit_user_fk` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Organization Limit';
```
 * @package rhosocial\organization\migrations
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class m170410_073641_createOrganizationLimitTable extends Migration
{
    public function up()
    {
        if ($this->db->driverName == 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Organization Limit'";
            $this->createTable(OrganizationLimit::tableName(), [
                'guid' => $this->varbinary(16)->notNull()->comment('GUID'),
                'user_guid' => $this->varbinary(16)->notNull()->comment('User GUID'),
                'limit' => $this->integer(11)->unsigned()->defaultValue(10)->notNull()->comment('Limit'),
                'ip' => $this->varbinary(16)->notNull()->defaultValue(0)->comment('IP Address'),
                'ip_type' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(4)->comment('IP Address Type'),
                'created_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Created At'),
                'updated_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Updated At'),
            ], $tableOptions);
        }
        $this->addPrimaryKey('organization_limit_guid_pk', OrganizationLimit::tableName(), 'guid');
        $this->addForeignKey('organization_limit_user_fk', OrganizationLimit::tableName(), 'user_guid', User::tableName(), 'guid', 'CASCADE', 'CASCADE');
        $this->createIndex('organization_limit_user_unique', OrganizationLimit::tableName(), ['user_guid'], true);
    }

    public function down()
    {
        $this->dropTable(OrganizationLimit::tableName());
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

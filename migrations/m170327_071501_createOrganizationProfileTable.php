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

use rhosocial\organization\Organization;
use rhosocial\organization\Profile;
use rhosocial\user\migrations\Migration;

/**
 * Create Organization Table.
 *
 * This migration is equivalent to:
```SQL
CREATE TABLE `organization_profile` (
  `guid` varbinary(16) NOT NULL COMMENT 'Organization GUID',
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nickname',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name',
  `gravatar_type` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Gravatar Type',
  `gravatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Gravatar',
  `timezone` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UTC' COMMENT 'Timezone',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Description',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Created At',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Updated At',
  PRIMARY KEY (`guid`),
  KEY `organization_nickname_normal` (`nickname`),
  KEY `organization_name_normal` (`name`),
  CONSTRAINT `organization_profile_fk` FOREIGN KEY (`guid`) REFERENCES `organization` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organization Profile';
```
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class m170327_071501_createOrganizationProfileTable extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organization Profile'";
            $this->createTable(Profile::tableName(), [
                'guid' => $this->varbinary(16)->notNull()->comment('Organization GUID'),
                'nickname' => $this->varchar(255)->notNull()->comment('Nickname'),
                'name' => $this->varchar(255)->notNull()->defaultValue('')->comment('Name'),
                'gravatar_type' => $this->smallInteger()->notNull()->defaultValue(0)->comment('Gravatar Type'),
                'gravatar'=> $this->varchar(255)->notNull()->defaultValue('')->comment('Gravatar'),
                'timezone' => $this->varchar(255)->charset('utf8')->collate('utf8_unicode_ci')->notNull()->defaultValue('UTC')->comment('Timezone'),
                'description' => $this->text()->notNull()->comment('Description'),
                'created_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Created At'),
                'updated_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Updated At'),
            ], $tableOptions);
        }
        $this->addPrimaryKey('organization_guid_profile_pk', Profile::tableName(), 'guid');
        $this->addForeignKey('organization_profile_fk', Profile::tableName(), 'guid', Organization::tableName(), 'guid', 'CASCADE', 'CASCADE');
        $this->createIndex('organization_nickname_normal', Profile::tableName(), 'nickname');
        $this->createIndex('organization_name_normal', Profile::tableName(), 'name');
    }

    public function down()
    {
        $this->dropTable(Profile::tableName());
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

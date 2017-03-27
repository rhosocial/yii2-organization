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
use rhosocial\organization\Member;
use rhosocial\user\User;
use rhosocial\user\migrations\Migration;

/**
 * 
 * This migration is equvalent to:
```SQL
CREATE TABLE `organization_member` (
  `guid` varbinary(16) NOT NULL COMMENT 'Member GUID',
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Member ID',
  `organization_guid` varbinary(16) NOT NULL COMMENT 'Organization GUID',
  `user_guid` varbinary(16) NOT NULL COMMENT 'User GUID',
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Nickname',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Description',
  `ip` varbinary(16) NOT NULL DEFAULT '0' COMMENT 'IP Address',
  `ip_type` tinyint(3) NOT NULL DEFAULT '4' COMMENT 'IP Address Type',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Created At',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Updated At',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `organization_member_unique` (`id`,`organization_guid`),
  UNIQUE KEY `organization_user_unique` (`organization_guid`,`user_guid`),
  KEY `member_user_fk` (`user_guid`),
  KEY `member_created_at_normal` (`created_at`),
  CONSTRAINT `member_organization_fk` FOREIGN KEY (`organization_guid`) REFERENCES `organization` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `member_user_fk` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organization Member';
```
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class m170327_111508_createOrganizationMemberTable extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organization Member'";
            $this->createTable(Member::tableName(), [
                'guid' => $this->varbinary(16)->notNull()->comment('Member GUID'),
                'id' => $this->varchar(8)->notNull()->defaultValue('')->comment('Member ID'),
                'organization_guid' => $this->varbinary(16)->notNull()->comment('Organization GUID'),
                'user_guid' => $this->varbinary(16)->notNull()->comment('User GUID'),
                'nickname' => $this->varchar(255)->notNull()->defaultValue('')->comment('Nickname'),
                'description' => $this->text()->notNull()->comment('Description'),
                'ip' => $this->varbinary(16)->notNull()->defaultValue(0)->comment('IP Address'),
                'ip_type' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(4)->comment('IP Address Type'),
                'created_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Created At'),
                'updated_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Updated At'),
            ], $tableOptions);
        }
        $this->addPrimaryKey('organization_member_pk', Member::tableName(), 'guid');
        $this->addForeignKey('member_organization_fk', Member::tableName(), 'organization_guid', Organization::tableName(), 'guid', 'CASCADE', 'CASCADE');
        $this->addForeignKey('member_user_fk', Member::tableName(), 'user_guid', User::tableName(), 'guid', 'CASCADE', 'CASCADE');
        $this->createIndex('organization_member_unique', Member::tableName(), ['id', 'organization_guid'], true);
        $this->createIndex('organization_user_unique', Member::tableName(), ['organization_guid', 'user_guid'], true);
        $this->createIndex('member_created_at_normal', Member::tableName(), 'created_at');
    }

    public function down()
    {
        $this->dropTable(Member::tableName());
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

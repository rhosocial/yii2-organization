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
use rhosocial\user\migrations\Migration;

/**
 * Create Organization Table.
 *
 * This migration is equivalent to:
```SQL
CREATE TABLE `organization` (
  `guid` varbinary(16) NOT NULL COMMENT 'Organization GUID',
  `id` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Organization ID No.',
  `ip` varbinary(16) NOT NULL DEFAULT '0' COMMENT 'IP Address',
  `ip_type` tinyint(3) NOT NULL DEFAULT '4' COMMENT 'IP Address Type',
  `parent` varbinary(16) NOT NULL DEFAULT '' COMMENT 'Parent',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Created At',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT 'Updated At',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT 'Status',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT 'Type',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `organization_id_unique` (`id`),
  KEY `organization_created_at_normal` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Organization';
```
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class m170327_031029_createOrganizationTable extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName == 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Organization'";
            $this->createTable(Organization::tableName(), [
                'guid' => $this->varbinary(16)->notNull()->comment('Organization GUID'),
                'id' => $this->varchar(16)->notNull()->collate('utf8_unicode_ci')->comment('Organization ID No.'),
                'ip' => $this->varbinary(16)->notNull()->defaultValue(0)->comment('IP Address'),
                'ip_type' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(4)->comment('IP Address Type'),
                'parent' => $this->varbinary(16)->notNull()->defaultValue('')->comment('Parent'),
                'created_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Created At'),
                'updated_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Updated At'),
                'status' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(1)->comment('Status'),
                'type' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(Organization::TYPE_ORGANIZATION)->comment('Type'),
            ], $tableOptions);
        }
        $this->addPrimaryKey('organization_guid_pk', Organization::tableName(), 'guid');
        $this->createIndex('organization_id_unique', Organization::tableName(), 'id', true);
        $this->createIndex('organization_created_at_normal', Organization::tableName(), 'created_at');
    }

    public function down()
    {
        $this->dropTable(Organization::tableName());
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

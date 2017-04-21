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
use rhosocial\organization\OrganizationSetting;
use rhosocial\user\User;

/**
 * Class m170421_111104_createOrganizationSettingTable
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class m170421_111104_createOrganizationSettingTable extends Migration
{
    public function up()
    {
        if ($this->db->driverName == 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Organization Setting'";
            $this->createTable(OrganizationSetting::tableName(), [
                'guid' => $this->varbinary(16)->notNull()->comment('GUID'),
                'organization_guid' => $this->varbinary(16)->notNull()->comment('Organization GUID'),
                'operator_guid' => $this->varbinary(16)->comment('Operator GUID'),
                'item' => $this->varchar(255)->notNull()->defaultValue('')->comment('Item'),
                'value' => $this->varchar(255)->notNull()->defaultValue('')->comment('Item'),
                'created_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Created At'),
                'updated_at' => $this->dateTime()->notNull()->defaultValue('1970-01-01 00:00:00')->comment('Updated At'),
            ], $tableOptions);
        }
        $this->addPrimaryKey('organization_setting_guid_pk', OrganizationSetting::tableName(), 'guid');
        $this->addForeignKey('organization_setting_organization_fk', OrganizationSetting::tableName(), 'organization_guid', Organization::tableName(), 'guid', 'CASCADE', 'CASCADE');
        $this->addForeignKey('organization_setting_operator_fk', OrganizationSetting::tableName(), 'operator_guid', User::tableName(), 'guid', 'SET NULL', 'CASCADE');
        $this->createIndex('organization_item_unique', OrganizationSetting::tableName(), ['organization_guid', 'item'], true);
    }

    public function down()
    {
        $this->dropTable(OrganizationSetting::tableName());
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

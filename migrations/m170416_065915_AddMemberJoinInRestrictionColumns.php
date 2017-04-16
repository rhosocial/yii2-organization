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
 * Class m170416_065915_AddMemberJoinInRestrictionColumns
 */
class m170416_065915_AddMemberJoinInRestrictionColumns extends Migration
{
    public function up()
    {
        if ($this->db->driverName == 'mysql') {
            $this->addColumn(Organization::tableName(), 'eom',
                $this->boolean()->notNull()->defaultValue(false)->comment("Exclude Other Members"));
            $this->addColumn(Organization::tableName(), 'djo',
                $this->boolean()->notNull()->defaultValue(false)->comment("Disallow Member to Join Other Org"));
            $this->addColumn(Organization::tableName(), 'oacm',
                $this->boolean()->notNull()->defaultValue(false)->comment("Only Accept Current Org Member"));
            $this->addColumn(Organization::tableName(), 'oasm',
                $this->boolean()->notNull()->defaultValue(false)->comment("Only Accept Superior Org Member"));
        }
    }

    public function down()
    {
        $this->dropColumn(Organization::tableName(), 'eom');
        $this->dropColumn(Organization::tableName(), 'djo');
        $this->dropColumn(Organization::tableName(), 'oacm');
        $this->dropColumn(Organization::tableName(), 'oasm');
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

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

namespace rhosocial\organization\rbac\migrations;

use rhosocial\user\migrations\Migration;
use rhosocial\organization\rbac\rules\RevokeDepartmentRule;
use rhosocial\organization\rbac\rules\RevokeOrganizationRule;
use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\organization\rbac\permissions\ManageProfile;
use rhosocial\organization\rbac\permissions\RevokeDepartment;
use rhosocial\organization\rbac\permissions\RevokeOrganization;
use rhosocial\organization\rbac\permissions\SetUpDepartment;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use Yii;
use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class m170328_063048_insertPermissionsRolesAndRules extends Migration
{
    /**
     * @throws InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

    public function up()
    {
        $this->addRules();
        $this->addRoles();
    }

    public function down()
    {
        $this->removeRoles();
        $this->removeRules();
    }

    protected function addRules()
    {
        $authManager = Yii::$app->authManager;
        
        $revokeDepartmentRule = new RevokeDepartmentRule();
        $revokeOrganizationRule = new RevokeOrganizationRule();
        $authManager->add($revokeDepartmentRule);
        $authManager->add($revokeOrganizationRule);
    }

    protected function removeRules()
    {
        $authManager = Yii::$app->authManager;
        
        $revokeDepartmentRule = new RevokeDepartmentRule();
        $revokeOrganizationRule = new RevokeOrganizationRule();
        $authManager->remove($revokeDepartmentRule);
        $authManager->remove($revokeOrganizationRule);
    }

    protected function addRoles()
    {
        $authManager = Yii::$app->authManager;

        $manageMember = new ManageMember();
        $manageProfile = new ManageProfile();
        $revokeDepartment = new RevokeDepartment();
        $revokeOrganization = new RevokeOrganization();
        $setUpDepartment = new SetUpDepartment();
        $setUpOrganization = new SetUpOrganization();

        $authManager->add($manageMember);
        $authManager->add($manageProfile);
        $authManager->add($revokeDepartment);
        $authManager->add($revokeOrganization);
        $authManager->add($setUpDepartment);
        $authManager->add($setUpOrganization);

        $departmentAdmin = new DepartmentAdmin();
        $departmentCreator = new DepartmentCreator();
        $organizationAdmin = new OrganizationAdmin();
        $organizationCreator = new OrganizationCreator();

        $authManager->add($departmentAdmin);
        $authManager->add($departmentCreator);
        $authManager->add($organizationAdmin);
        $authManager->add($organizationCreator);

        $authManager->addChild($departmentAdmin, $manageMember);
        $authManager->addChild($departmentAdmin, $manageProfile);
        $authManager->addChild($departmentAdmin, $setUpDepartment);
        $authManager->addChild($departmentAdmin, $revokeDepartment);

        $authManager->addChild($departmentCreator, $departmentAdmin);

        $authManager->addChild($organizationAdmin, $departmentCreator);

        $authManager->addChild($organizationCreator, $organizationAdmin);
        $authManager->addChild($organizationCreator, $revokeOrganization);
    }

    protected function removeRoles()
    {
        $authManager = Yii::$app->authManager;

        $departmentAdmin = new DepartmentAdmin();
        $departmentCreator = new DepartmentCreator();
        $organizationAdmin = new OrganizationAdmin();
        $organizationCreator = new OrganizationCreator();

        $authManager->removeChildren($departmentAdmin);
        $authManager->removeChildren($departmentCreator);
        $authManager->removeChildren($organizationAdmin);
        $authManager->removeChildren($organizationCreator);
        $authManager->remove($departmentAdmin);
        $authManager->remove($departmentCreator);
        $authManager->remove($organizationAdmin);
        $authManager->remove($organizationCreator);

        $manageMember = new ManageMember();
        $manageProfile = new ManageProfile();
        $revokeDepartment = new RevokeDepartment();
        $revokeOrganization = new RevokeOrganization();
        $setUpDepartment = new SetUpDepartment();
        $setUpOrganization = new SetUpOrganization();

        $authManager->remove($manageMember);
        $authManager->remove($manageProfile);
        $authManager->remove($revokeDepartment);
        $authManager->remove($revokeOrganization);
        $authManager->remove($setUpDepartment);
        $authManager->remove($setUpOrganization);
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

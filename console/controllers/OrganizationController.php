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

namespace rhosocial\organization\console\controllers;

use rhosocial\user\User;
use rhosocial\organization\Organization;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use Yii;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Organization commands.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationController extends Controller
{
    public $userClass;
    public $organizationClass;
    public $defaultAction = 'show';  

    /**
     * Check user class.
     * @return User
     * @throws Exception throw if User is not an instance inherited from `\rhosocial\user\User`.
     */
    protected function checkUserClass()
    {
        $userClass = $this->userClass;
        if (!class_exists($userClass)) {
            throw new Exception('User Class Invalid.');
        }
        if (!((new $userClass()) instanceof User)) {
            throw new Exception('User Class(' . $userClass . ') does not inherited from `\rhosocial\user\User`.');
        }
        return $userClass;
    }

    /**
     * Get user from database.
     * @param User|string|integer $user User ID.
     * @return User
     */
    protected function getUser($user)
    {
        $userClass = $this->checkUserClass();
        if (is_numeric($user)) {
            $user = $userClass::find()->id($user)->one();
        } elseif (is_string($user) && strlen($user)) {
            $user = $userClass::find()->guid($user)->one();
        }
        if (!$user || $user->getIsNewRecord()) {
            throw new Exception('User Not Registered.');
        }
        return $user;
    }

    /**
     * Check organization class.
     * @return Organization
     * @throws Exception throw if Organization is not an instance inherited from `\rhosocial\organization\Organization`.
     */
    protected function checkOrganizationClass()
    {
        $organizationClass = $this->organizationClass;
        if (!class_exists($organizationClass)) {
            throw new Exception('Organization Class Invalid.');
        }
        if (!((new $organizationClass()) instanceof Organization)) {
            throw new Exception('Organization Class(' . $organizationClass . ') does not inherited from `\rhosocial\organization\Organization`.');
        }
        return $organizationClass;
    }

    /**
     * Get organization.
     * @param Organization|string|integer $organization
     * @return Organization
     */
    protected function getOrganization($organization)
    {
        $organizationClass = $this->checkOrganizationClass();
        if (is_numeric($organization)) {
            $organization = $organizationClass::find()->id($organization)->one();
        } elseif (is_string($organization) && strlen($organization)) {
            $organization = $organizationClass::find()->guid($organization)->one();
        }
        if (!$organization || $organization->getIsNewRecord()) {
            throw new Exception('Organization Not Set Up.');
        }
        return $organization;
    }

    /**
     * Assign SetUpOrganization permission.
     * @param User|string|integer $user
     * @return boolean
     */
    public function actionAssignSetUpOrganization($user)
    {
        $user = $this->getUser($user);
        $permission = new SetUpOrganization();
        try {
            $assignment = Yii::$app->authManager->assign($permission->name, $user);
        } catch (\yii\db\IntegrityException $ex) {
            echo "Failed to assign `" . $permission->name . "`.\n";
            echo "Maybe the permission has been assigned.\n";
            return false;
        }
        if ($assignment) {
            echo "`$permission->name`" . " assigned to User (" . $user->getID() . ") successfully.\n";
        } else {
            echo "Failed to assign `" . $permission->name . "`.\n";
        }
        return true;
    }

    /**
     * Show Organization Information.
     * @param Organization|string|integer $organization Organization's or department's ID.
     */
    public function actionShow($organization)
    {
        $organization = $this->getOrganization($organization);
        echo $organization->getID() . "\n";
    }

    /**
     * Set up organization.
     * @param User|string|integer $user Organization creator.
     * @param string $name
     */
    public function actionSetUpOrganization($user, $name)
    {
        $user = $this->getUser($user);
        try {
            $result = $user->setUpOrganization($name);
            if ($result !== true) {
                throw new Exception('Failed to set up.');
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        echo "Organization Set Up:\n";
        return $this->actionShow($user->lastSetUpOrganization);
    }

    /**
     * Set up department.
     * @param User|string|integer $user Department creator.
     * @param string $name
     * @param Organization|string|integer $parent
     */
    public function actionSetUpDepartment($user, $name, $parent)
    {
        $user = $this->getUser($user);
        $parent = $this->getOrganization($parent);
        try {
            $result = $user->setUpDepartment($name, $parent);
            if ($result !== true) {
                throw new Exception('Failed to set up.');
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        echo "Department Set Up:\n";
        return $this->actionShow($user->lastSetUpOrganization);
    }

    /**
     * Revoke organization.
     * @param Organization|string|integer $organization
     * @throws Exception
     */
    public function actionRevokeOrganization($organization)
    {
        $organization = $this->getOrganization($organization);
        $creator = $organization->creator;
        if (!$creator->revokeOrganization($organization)) {
            throw new Exception('Failed to revoke: ' . $organization->getID);
        }
        echo "Organization ({$organization->getID()}) revoked.\n";
    }

    /**
     * Add administrator.
     * @param Organization|string|integer $organization
     * @param User|string|integer $user
     */
    public function actionAddAdministrator($organization, $user)
    {
        $organization = $this->getOrganization($organization);
        $user = $this->getUser($user);
        if (!$organization->addAdministrator($user)) {
            throw new Exception('Failed to add administrator.');
        }
        echo "User ({$user->getID()}) assigned administrator.\n";
    }

    /**
     * Remove administrator
     * @param Organization|string|integer $organization
     * @param User|string|integer $user
     * @param boolean $keepMember
     */
    public function actionRemoveAdministrator($organization, $user, $keepMember = "yes")
    {
        $keepMember = strtolower($keepMember) == "yes";
        $organization = $this->getOrganization($organization);
        $user = $this->getUser($user);
        $id = $user->getID();
        if (!$organization->removeAdministrator($user, $keepMember)) {
            throw new Exception('Failed to remove administrator.');
        }
        echo "Administrator ($id) removed.\n";
        echo ($keepMember) ? ("But he is still a member of it.\n") : ("At the same time, he was also removed from the organization.\n");
    }

    /**
     * Add member.
     * @param Organization|string|intger $organization
     * @param User|string|integer $user
     */
    public function actionAddMember($organization, $user)
    {
        $organization = $this->getOrganization($organization);
        $user = $this->getUser($user);
        $id = $user->getID();
        if (!$organization->addMember($user)) {
            throw new Exception('Failed to add member.');
        }
        echo "User ($id) added to Organization ({$organization->getID()}).\n";
    }

    /**
     * Remove member.
     * @param Organization|string|intger $organization
     * @param User|string|integer $user
     */
    public function actionRemoveMember($organization, $user)
    {
        $organization = $this->getOrganization($organization);
        $user = $this->getUser($user);
        $id = $user->getID();
        if (!$organization->removeMember($user)) {
            throw new Exception('Failed to remove member.');
        }
        echo "Member ($id) removed.\n";
    }
}

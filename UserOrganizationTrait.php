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

namespace rhosocial\organization;

use rhosocial\organization\queries\MemberQuery;
use rhosocial\organization\queries\OrganizationQuery;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * @property string $guidAttribute GUID Attribute.
 * @property-read Member[] $ofMembers
 * @property-read Organization[] $atOrganizations
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait UserOrganizationTrait
{
    public $organizationClass = Organization::class;
    public $memberClass = Member::class;
    private $noInitOrganization;
    private $noInitMember;
    public $lastSetUpOrganization;
    /**
     * @return Organization
     */
    protected function getNoInitOrganization()
    {
        if (!$this->noInitOrganization) {
            $class = $this->organizationClass;
            $this->noInitOrganization = $class::buildNoInitModel();
        }
        return $this->noInitOrganization;
    }
    /**
     * @return Member
     */
    protected function getNoInitMember()
    {
        if (!$this->noInitMember) {
            $class = $this->memberClass;
            $this->noInitMember = $class::buildNoInitModel();
        }
        return $this->noInitMember;
    }

    /**
     * 
     * @return MemberQuery
     */
    public function getOfMembers()
    {
        return $this->hasMany($this->memberClass, [$this->getNoInitMember()->memberAttribute => $this->guidAttribute])->inverseOf('memberUser');
    }

    /**
     * 
     * @return OrganizationQuery
     */
    public function getAtOrganizations()
    {
        return $this->hasMany($this->organizationClass, [$this->guidAttribute => $this->getNoInitMember()->createdByAttribute])->via('ofMembers');
    }

    /**
     * Set up organization.
     * @param string $name
     * @param Organization $parent
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return boolean Whether indicate the setting-up succeeded or not.
     */
    public function setUpOrganization($name, $parent = null, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createOrganization($name, $parent, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '');
            $this->setUpBaseOrganization($models);
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        $this->lastSetUpOrganization = $models[0];
        return true;
    }

    /**
     * Set up organization.
     * @param string $name
     * @param Organization $parent
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return boolean Whether indicate the setting-up succeeded or not.
     */
    public function setUpDepartment($name, $parent = null, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        if ($parent == null) {
            throw new InvalidConfigException('Invalid Parent Parameter.');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createDepartment($name, $parent, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '');
            $this->setUpBaseOrganization($models);
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        $this->lastSetUpOrganization = $models[0];
        return true;
    }

    /**
     * Set up base organization.
     * @param array $models
     * @return boolean
     * @throws InvalidConfigException
     * @throws \Exception
     */
    protected function setUpBaseOrganization($models)
    {
        if (!array_key_exists(0, $models) || !($models[0] instanceof Organization))
        {
            throw new InvalidConfigException('Invalid Organization Model.');
        }
        $result = $models[0]->register($models['associatedModels']);
        if ($result instanceof \Exception) {
            throw $result;
        }
        if ($result !== true) {
            throw new \Exception('Failed to set up.');
        }
        return true;
    }

    /**
     * Create organization.
     * @param string $name
     * @param Organization $parent
     * @param string $nickname
     * @param string $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return Organization
     */
    public function createOrganization($name, $parent = null, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        return $this->createBaseOrganization($name, $parent, $nickname, $gravatar_type, $gravatar, $timezone, $description);
    }

    /**
     * Create department.
     * @param string $name
     * @param Organization $parent
     * @param string $nickname
     * @param string $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return Organization
     */
    public function createDepartment($name, $parent = null, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        return $this->createBaseOrganization($name, $parent, $nickname, $gravatar_type, $gravatar, $timezone, $description, Organization::TYPE_DEPARTMENT);
    }

    /**
     * Create Base Organization.
     * @param string $name
     * @param Organization $parent
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @param integer $type
     * @return array This array contains two elements, the first is `Organization` or `Department` depends on `$type`.
     * The other is `associatedModels` array, contains two elements `Profile`(profile) and `Creator`(creator).
     * @throws InvalidParamException throw if setting parent failed. Possible reasons include:
     * - The parent is itself.
     * - The parent has already been its ancestor.
     * - The current organization has reached the limit of ancestors.
     */
    protected function createBaseOrganization($name, $parent = null, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '', $type = Organization::TYPE_ORGANIZATION)
    {
        $class = $this->organizationClass;
        $organization = new $class(['type' => $type]);
        if (empty($parent)) {
            $organization->setNullParent();
        } elseif ($organization->setParent($parent) === false) {
            throw new InvalidParamException("Failed to set parent.");
        }
        /* @var $organization Organization */
        $profileConfig = [
            'name' => $name,
            'nickname' => $nickname,
            'gravatar_type' => $gravatar_type,
            'gravatar' => $gravatar,
            'timezone' => $timezone,
            'description' => $description,
        ];
        $profile = $organization->createProfile($profileConfig);
        $role = null;
        if ($type == Organization::TYPE_ORGANIZATION) {
            $role = new OrganizationCreator();
        } elseif ($type == Organization::TYPE_DEPARTMENT) {
            $role = new DepartmentCreator();
        }
        $member = $organization->createMemberModelWithUser($this);
        $member->assignRole($role);
        return [0 => $organization, 'associatedModels' => ['profile' => $profile, 'creator'=> $member]];
    }

    /**
     * Revoke organization.
     * @param static|string|integer $organization
     * @param boolean $revokeIfHasChildren
     * @throws InvalidParamException throw if current user is not the creator of organization.
     */
    public function revokeOrganization($organization, $revokeIfHasChildren = false)
    {
        if (!($organization instanceof $this->organizationClass))
        {
            $class = $this->organizationClass;
            if (is_int($organization)) {
                $organization = $class::find()->id($organization)->one();
            } elseif (is_string($organization)) {
                $organization = $class::find()->guid($organization)->one();
            }
        }
        if (!$this->isOrganizationCreator($organization)) {
            throw new InvalidParamException('You are not the creator of the this organization and have no right to revoke it.');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $result = $organization->deregister();
            if ($result instanceof \Exception){
                throw $result;
            }
            if ($result !== true) {
                throw new InvalidParamException();
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        return true;
    }

    /**
     * 
     * @param Organization $organization
     */
    public function isOrganizationCreator($organization)
    {
        $member = $organization->getMember($this);
        if (!$member) {
            return false;
        }
        return $member->isCreator();
    }

    /**
     * 
     * @param Organization $organization
     */
    public function isOrganizationAdministrator($organization)
    {
        $member = $organization->getMember($this);
        if (!$member) {
            return false;
        }
        return $member->isAdministrator();
    }
}

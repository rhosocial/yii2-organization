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

use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\organization\exceptions\RevokePreventedException;
use rhosocial\organization\queries\MemberQuery;
use rhosocial\organization\queries\OrganizationQuery;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\organization\rbac\permissions\SetUpDepartment;
use rhosocial\organization\rbac\permissions\RevokeOrganization;
use rhosocial\organization\rbac\permissions\RevokeDepartment;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * @property string $guidAttribute GUID Attribute.
 * @property-read Member[] $ofMembers
 * @property-read Member[] $ofCreators
 * @property-read Member[] $ofAdministrators
 * @property-read Organization[] $atOrganizations
 * @property-read Organization[] $atOrganizationsOnly
 * @property-read Organization[] $atDepartmentsOnly
 * @property-read Organization[] $creatorsAtOrganizations
 * @property-read Organization[] $creatorsAtOrganizationsOnly
 * @property-read Organization[] $administratorsAtOrganizations
 * @property-read Organization[] $administratorsAtOrganizationsOnly
 * @property-read OrganizationLimit $organizationLimit
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait UserOrganizationTrait
{
    /**
     * @var string The organization class.
     * Note: Please assign it with your own Organization class.
     */
    public $organizationClass = Organization::class;

    /**
     * @var string The organization limit class.
     * Note: Please assign it with your own OrganizationLimit class.
     */
    public $organizationLimitClass = OrganizationLimit::class;
    private $noInitOrganizationLimit;
    private $noInitOrganization;
    public $lastSetUpOrganization;

    /**
     * @return OrganizationLimit
     */
    public function getNoInitOrganizationLimit()
    {
        if (!$this->noInitOrganizationLimit) {
            $class = $this->organizationLimitClass;
            $this->noInitOrganizationLimit = $class::buildNoInitModel();
        }
        return $this->noInitOrganizationLimit;
    }
    /**
     * @return Organization
     */
    public function getNoInitOrganization()
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
    public function getNoInitMember()
    {
        return $this->getNoInitOrganization()->getNoInitMember();
    }

    /**
     * Get member query.
     * @return MemberQuery
     */
    public function getOfMembers()
    {
        return $this->hasMany(get_class($this->getNoInitMember()), [$this->getNoInitMember()->memberAttribute => $this->guidAttribute])->inverseOf('memberUser');
    }

    /**
     * Get query of member whose role is creator.
     * @return MemberQuery
     */
    public function getOfCreators()
    {
        return $this->getOfMembers()->andWhere(['role' => [(new DepartmentCreator)->name, (new OrganizationCreator)->name]]);
    }

    /**
     * Get query of member whose role is administrator.
     * @return MemberQuery
     */
    public function getOfAdministrators()
    {
        return $this->getOfMembers()->andWhere(['role' => [(new DepartmentAdmin)->name, (new OrganizationAdmin)->name]]);
    }

    /**
     * Get query of organization of which this user has been a member.
     * If you access this method as magic-property `atOrganizations`, you will
     * get all organizations the current user has joined in.
     * @return OrganizationQuery
     */
    public function getAtOrganizations()
    {
        return $this->hasMany($this->organizationClass, [$this->guidAttribute => $this->getNoInitMember()->createdByAttribute])->via('ofMembers');
    }

    /**
     * 
     * @return OrganizationQuery
     */
    public function getAtOrganizationsOnly()
    {
        return $this->getAtOrganizations()->andWhere(['type' => Organization::TYPE_ORGANIZATION]);
    }

    /**
     * 
     * @return OrganizationQuery
     */
    public function getAtDepartmentsOnly()
    {
        return $this->getAtOrganizations()->andWhere(['type' => Organization::TYPE_DEPARTMENT]);
    }

    /**
     * 
     * @return OrganizationQuery
     */
    public function getCreatorsAtOrganizations()
    {
        return $this->hasMany($this->organizationClass, [$this->guidAttribute => $this->getNoInitMember()->createdByAttribute])->via('ofCreators');
    }

    /**
     *
     * @return OrganizationQuery
     */
    public function getCreatorsAtOrganizationsOnly()
    {
        return $this->getCreatorsAtOrganizations()->andWhere(['type' => Organization::TYPE_ORGANIZATION]);
    }

    /**
     * 
     * @return OrganizationQuery
     */
    public function getAdministratorsAtOrganizations()
    {
        return $this->hasMany($this->organizationClass, [$this->guidAttribute => $this->getNoInitMember()->createdByAttribute])->via('ofAdministrators');
    }

    /**
     * @return OrganizationQuery
     */
    public function getAdministratorsAtOrganizationsOnly()
    {
        return $this->getAdministratorsAtOrganizations()->andWhere(['type' => Organization::TYPE_ORGANIZATION]);
    }

    /**
     * Get Organization Limit Query.
     * @return BaseBlameableQuery
     */
    public function getOrganizationLimit()
    {
        if (empty($this->organizationLimitClass)) {
            return null;
        }
        return $this->hasOne($this->organizationLimitClass, [$this->guidAttribute => $this->getNoInitOrganizationLimit()->createdByAttribute]);
    }

    /**
     * Set up organization.
     * @param string $name
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return boolean Whether indicate the setting-up succeeded or not.
     * @throws InvalidParamException
     * @throws \Exception
     */
    public function setUpOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        $accessChecker = Yii::$app->authManager;
        if (!$accessChecker->checkAccess($this, (new SetUpOrganization)->name)) {
            throw new InvalidParamException("You do not have permission to set up organization.");
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createOrganization($name, null, $nickname, $gravatar_type, $gravatar, $timezone, $description);
            $this->setUpBaseOrganization($models);
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        $this->lastSetUpOrganization = is_array($models) ? $models[0] : $models;
        return true;
    }

    /**
     * Set up organization.
     * @param string $name Department name.
     * @param Organization $parent Parent organization or department.
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return boolean Whether indicate the setting-up succeeded or not.
     * @throws InvalidParamException
     * @throws \Exception
     */
    public function setUpDepartment($name, $parent, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        if (!($parent instanceof $this->organizationClass)) {
            throw new InvalidParamException('Invalid Parent Parameter.');
        }
        $accessChecker = Yii::$app->authManager;
        if (!$accessChecker->checkAccess($this, (new SetUpDepartment)->name, ['organization' => $parent])) {
            throw new InvalidParamException("You do not have permission to set up department.");
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createDepartment($name, $parent, $nickname, $gravatar_type, $gravatar, $timezone, $description);
            $this->setUpBaseOrganization($models);
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        $this->lastSetUpOrganization = is_array($models) ? $models[0] : $models;
        return true;
    }

    /**
     * Set up base organization.
     * @param Organization $models
     * @return boolean
     * @throws InvalidConfigException
     * @throws \Exception
     */
    protected function setUpBaseOrganization($models)
    {
        $model = null;
        $associatedModels = [];
        if (is_array($models)) {
            if (!array_key_exists(0, $models)) {
                throw new InvalidConfigException('Invalid Organization Model.');
            }
            $model = $models[0];
            $associatedModels = array_key_exists('associatedModels', $models) ? $models['associatedModels'] : [];
        } elseif ($models instanceof $this->organizationClass) {
            $model = $models;
        }
        $result = $model->register($associatedModels);
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
     * @return Organization
     * @throws InvalidParamException throw if setting parent failed. Possible reasons include:
     * - The parent is itself.
     * - The parent has already been its ancestor.
     * - The current organization has reached the limit of ancestors.
     */
    protected function createBaseOrganization($name, $parent = null, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '', $type = Organization::TYPE_ORGANIZATION)
    {
        $class = $this->organizationClass;
        $profileConfig = [
            'name' => $name,
            'nickname' => $nickname,
            'gravatar_type' => $gravatar_type,
            'gravatar' => $gravatar,
            'timezone' => $timezone,
            'description' => $description,
        ];
        $organization = new $class(['type' => $type, 'creatorModel' => $this, 'profileConfig' => $profileConfig]);
        if (empty($parent)) {
            $organization->setNullParent();
        } elseif ($organization->setParent($parent) === false) {
            throw new InvalidParamException("Failed to set parent.");
        }
        return $organization;
    }

    /**
     * Revoke organization or department.
     * @param Organization|string|integer $organization Organization or it's ID or GUID.
     * @param boolean $revokeIfHasChildren True represents revoking organization if there are subordinates.
     * @return boolean True if revocation is successful.
     * @throws InvalidParamException throws if organization is invalid.
     * @throws \Exception
     * @throws RevokePreventedException throws if $revokeIfHasChildren is false, at the
     * same time the current organization or department has subordinates.
     * @throws @var:$organization@mtd:deregister
     */
    public function revokeOrganization($organization, $revokeIfHasChildren = true)
    {
        if (!($organization instanceof $this->organizationClass))
        {
            $class = $this->organizationClass;
            $organization = $class::find()->guidOrId($organization)->one();
        }
        if (!($organization instanceof $this->organizationClass)) {
            throw new InvalidParamException('Invalid Organization.');
        }
        if (!Yii::$app->authManager->checkAccess(
                $this,
                $organization->type == Organization::TYPE_ORGANIZATION ? (new RevokeOrganization)->name : (new RevokeDepartment)->name,
                ['organization' => $organization])) {
            throw new InvalidParamException("You do not have permission to revoke it.");
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$revokeIfHasChildren && ((int)($organization->getChildren()->count())) > 0) {
                $type = $organization->isOrganization() ? "organization" : "department";
                throw new RevokePreventedException("The $type has children. Revoking prevented.");
            }
            $result = $organization->deregister();
            if ($result instanceof \Exception){
                throw $result;
            }
            if ($result !== true) {
                throw new InvalidParamException("Failed to revoke.");
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
     * Check whether current user is organization or department creator.
     * @param Organization $organization
     * @return boolean True if current is organization or department creator.
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
     * Check whether current user is organization or department administrator.
     * @param Organization $organization
     * @return boolean True if current is organization or department administrator.
     */
    public function isOrganizationAdministrator($organization)
    {
        $member = $organization->getMember($this);
        if (!$member) {
            return false;
        }
        return $member->isAdministrator();
    }

    /**
     * Attach events associated with organization.
     */
    public function initOrganizationEvents()
    {
        $this->on(static::EVENT_BEFORE_DELETE, [$this, "onRevokeOrganizationsByCreator"]);
    }

    /**
     * Revoke Organization Event.
     * It should be triggered when deleting (not deregistering).
     * @param Event $event
     */
    public function onRevokeOrganizationsByCreator($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        $organizations = $this->creatorsAtOrganizations;
        foreach ($organizations as $org)
        {
            $sender->revokeOrganization($org);
        }
    }

    /**
     * Check whether the current user has reached the upper limit of organizations.
     * @return boolean the upper limit of organizations which current could be set up.
     */
    public function hasReachedOrganizationLimit()
    {
        $remaining = $this->getRemainingOrganizationPlaces();
        if ($remaining === false) {
            return false;
        }
        return $remaining <= 0;
    }

    /**
     * Get the remaining places of organizations.
     * @return bool|int False if no limit.
     */
    public function getRemainingOrganizationPlaces()
    {
        $class = $this->organizationLimitClass;
        if (empty($class)) {
            return false;
        }
        $limit = $class::getLimit($this);
        if ($limit === false) {
            return false;
        }
        $count = (int)$this->getCreatorsAtOrganizationsOnly()->count();
        return $limit - $count;
    }
}

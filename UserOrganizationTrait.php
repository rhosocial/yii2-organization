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
use Yii;
use yii\base\InvalidConfigException;

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
    public $departmentClass = Department::class;
    public $memberClass = Member::class;
    private $noInitOrganization;
    private $noInitMember;
    public $lastSetUpOrganization;
    public $lastSetUpDepartment;
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
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @param BaseOrganization $parent
     * @return boolean Whether indicate the setting-up succeeded or not.
     */
    public function setUpOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '', $parent = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '');
            if (!array_key_exists(0, $models) || !($models[0] instanceof Organization)) {
                throw new InvalidConfigException('Invalid Organization Model.');
            }
            $result = $models[0]->register($models['associatedModels']);
            if ($result instanceof \Exception) {
                throw $result;
            }
            if ($result !== true) {
                throw new \Exception('Failed to set up.');
            }
            if ($parent instanceof BaseOrganization && !$parent->getIsNewRecord()) {
                $result = $models[0]->setParent($parent);
            }
            if ($result === false) {
                throw new \Exception('Failed to set parent.');
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->lastSetUpOrganization = $models[0];
        return true;
    }

    /**
     * 
     * @param BaseOrganization $parent
     * @param string $name
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return boolean Whether indicate the setting-up succeeded or not.
     */
    public function setUpDepartment($parent, $name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createDepartment($name, $nickname, $gravatar_type, $gravatar, $timezone, $description);
            if (!array_key_exists(0, $models) || !($models[0] instanceof Department)) {
                throw new InvalidConfigException('Invalid Department Model.');
            }
            $result = $models[0]->register($models['associatedModels']);
            if ($result instanceof \Exception) {
                throw $result;
            }
            if ($result !== true) {
                throw new \Exception('Failed to set up.');
            }
            if ($parent instanceof BaseOrganization && !$parent->getIsNewRecord()) {
                $result = $models[0]->setParent($parent);
            }
            if ($result === false) {
                throw new \Exception('Failed to set parent.');
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->lastSetUpDepartment = $models[0];
        return true;
    }

    /**
     * Create organization.
     * @param string $name
     * @param string $nickname
     * @param string $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return Organization
     */
    public function createOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        return $this->createBaseOrganization($name, $nickname, $gravatar_type, $gravatar, $timezone, $description);
    }

    /**
     * Create department.
     * @param string $name
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @return Department
     */
    public function createDepartment($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        return $this->createBaseOrganization($name, $nickname, $gravatar_type, $gravatar, $timezone, $description, BaseOrganization::TYPE_DEPARTMENT);
    }

    /**
     * Create Base Organization.
     * @param string $name
     * @param string $nickname
     * @param integer $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     * @param integer $type
     * @return array This array contains two elements, the first is `Organization` or `Department` depends on `$type`.
     * The other is `associatedModels` array, contains two elements `Profile`(profile) and `Creator`(creator).
     */
    protected function createBaseOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '', $type = BaseOrganization::TYPE_ORGANIZATION)
    {
        $class = $this->organizationClass;
        if ($type == BaseOrganization::TYPE_DEPARTMENT) {
            $class = $this->departmentClass;
        }
        $organization = new $class();
        /* @var $organization BaseOrganization */
        $profileConfig = [
            'name' => $name,
            'nickname' => $nickname,
            'gravatar_type' => $gravatar_type,
            'gravatar' => $gravatar,
            'timezone' => $timezone,
            'description' => $description,
        ];
        $profile = $organization->createProfile($profileConfig);
        $member = $organization->createMemberModelWithUser($this);
        return [0 => $organization, 'associatedModels' => ['profile' => $profile, 'creator'=> $member]];
    }
}

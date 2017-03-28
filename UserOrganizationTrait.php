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
     * @return boolean
     */
    public function setUpOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $models = $this->createOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '');
            if (!array_key_exists('organization', $models) || !($models['organization'] instanceof Organization)) {
                throw new InvalidConfigException('Invalid Organization Model.');
            }
            $result = $models['organization']->register($models['associatedModels']);
            if ($result instanceof \Exception) {
                throw $result;
            }
            if ($result !== true) {
                throw new \Exception('Failed to set up.');
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->lastSetUpOrganization = $models['organization'];
        return true;
    }

    /**
     * Set Up Department.
     * @param BaseOrganization $organization
     * @param type $department
     */
    public function setUpDepartment($organization, $department)
    {
        
    }

    /**
     * 
     * @param string $name
     * @param string $nickname
     * @param string $gravatar_type
     * @param string $gravatar
     * @param string $timezone
     * @param string $description
     */
    public function createOrganization($name, $nickname = '', $gravatar_type = 0, $gravatar = '', $timezone = 'UTC', $description = '')
    {
        $class = $this->organizationClass;
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
        return ['organization' => $organization, 'associatedModels' => ['profile' => $profile, 'creator'=> $member]];
    }
}

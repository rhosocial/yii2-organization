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

namespace rhosocial\organization\forms;

use rhosocial\organization\Organization;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

/**
 * Class SettingsForm
 * @package rhosocial\organization\forms
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SettingsForm extends Model
{
    /**
     * @var string
     */
    public $exclude_other_members;

    /**
     * @var string
     */
    public $disallow_member_join_other;

    /**
     * @var string
     */
    public $only_accept_current_org_member;

    /**
     * @var string
     */
    public $only_accept_superior_org_member;

    /**
     * @var string
     */
    public $join_password;

    /**
     * @var string
     */
    public $join_ip_address;

    /**
     * @var string
     */
    public $join_entrance_url;

    const SCENARIO_ORGANIZATION = 'organization';
    const SCENARIO_DEPARTMENT = 'department';

    /**
     * @var Organization
     */
    public $organization;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->organization) {
            throw new InvalidConfigException('Invalid Organization Model.');
        }
        $this->scenario = $this->organization->isOrganization() ? static::SCENARIO_ORGANIZATION : static::SCENARIO_DEPARTMENT;
        $this->loadSettings();
    }

    /**
     * Load settings.
     */
    protected function loadSettings()
    {
        if ($this->organization->isOrganization()) {
            $this->exclude_other_members = $this->organization->isExcludeOtherMembers ? '1' : '0';
            $this->disallow_member_join_other = $this->organization->isDisallowMemberJoinOther ? '1' : '0';
        } elseif ($this->organization->isDepartment()) {
            $this->only_accept_current_org_member = $this->organization->isOnlyAcceptCurrentOrgMember ? '1' : '0';
            $this->only_accept_superior_org_member = $this->organization->isOnlyAcceptSuperiorOrgMember ? '1' : '0';
        }
        $this->join_password = $this->organization->joinPassword;
        $this->join_ip_address = $this->organization->joinIpAddress;
        $this->join_entrance_url = $this->organization->joinEntranceUrl;
    }

    /**
     * Submit settings.
     */
    public function submit()
    {
        try {
            if ($this->organization->isOrganization()) {
                if ($this->exclude_other_members != ($this->organization->isExcludeOtherMembers ? '1' : '0')) {
                    $this->organization->isExcludeOtherMembers = ($this->exclude_other_members == '1');
                }
                if ($this->disallow_member_join_other != ($this->organization->isDisallowMemberJoinOther ? '1' : '0')) {
                    $this->organization->isDisallowMemberJoinOther = ($this->disallow_member_join_other == '1');
                }
            } elseif ($this->organization->isDepartment()) {
                if ($this->only_accept_current_org_member != ($this->organization->isOnlyAcceptCurrentOrgMember ? '1' : '0')) {
                    $this->organization->isOnlyAcceptCurrentOrgMember = ($this->only_accept_current_org_member == '1');
                }
                if ($this->only_accept_superior_org_member != ($this->organization->isOnlyAcceptSuperiorOrgMember ? '1' : '0')) {
                    $this->organization->isOnlyAcceptSuperiorOrgMember = ($this->only_accept_superior_org_member == '1');
                }
            }
            if ($this->join_password != $this->organization->joinPassword) {
                $this->organization->joinPassword = $this->join_password;
            }
            if ($this->join_ip_address != $this->organization->joinIpAddress) {
                $this->organization->joinIpAddress = $this->join_ip_address;
            }
            if ($this->join_entrance_url != $this->organization->joinEntranceUrl) {
                $this->organization->joinEntranceUrl = $this->join_entrance_url;
            }
        } catch (\Exception $ex) {
            throw new ServerErrorHttpException($ex->getMessage());
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'exclude_other_members' => Yii::t('organization', 'Exclude other members'),
            'disallow_member_join_other' => Yii::t('organization', 'Disallow members to join other'),
            'only_accept_current_org_member' => Yii::t('organization', 'Only accept organization members'),
            'only_accept_superior_org_member' => Yii::t('organization', 'Only accept superior members'),
            'join_password' => Yii::t('organization', 'Password'),
            'join_ip_address' => Yii::t('organization', 'IP Address'),
            'join_entrance_url' => Yii::t('organization', 'Entrance URL'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        $topName = $this->organization->isDepartment() ? $this->organization->topOrganization->profile->name . ' (' . $this->organization->topOrganization->getID() . ')' : '';
        $parentName = $this->organization->isDepartment() ? $this->organization->parent->profile->name . ' (' . $this->organization->parent->getID() . ')' : '';
        return [
            'exclude_other_members' => Yii::t('organization', 'This organization does not allow other organizations and their subordinates\' members to join.') . "\n" . Yii::t('organization', 'All members of the other organizations (including their subordinates) who have joined this organization (including subordinate departments) are not affected.'),
            'disallow_member_join_other' => Yii::t('organization', 'This organization does not allow the organization and its subordinates\' members to join other organizations or their subordinates.') . "\n" . Yii::t('organization', 'All members of this organization (including subordinate departments) who have joined other organizations (including their subordinates) are not affected.') . "\n" . Yii::t('organization', 'If this option is enabled, all members of the organization (including subordinate departments) who have the "Set Up Organization" permission will not be able to set up a new organization.'),
            'only_accept_current_org_member' => Yii::t('organization', 'This department is only accepted by members of the organization.') . "\n" . Yii::t('organization', 'That is to say, only the members of {name} are accepted.', ['name' => $topName]),
            'only_accept_superior_org_member' => Yii::t('organization', 'This department only accepts members of the parent organization or department.') . "\n" . Yii::t('organization', 'That is to say, only the members of {name} are accepted.', ['name' => $parentName]),
            'join_entrance_url' => Yii::t('organization', 'Only the users through the above entrance URL can join this organization / department proactively.') . Yii::t('organization', 'This condition needs to be unique and can not be the same as the entrance URL for other organizations / departments.'),
            'join_password' => Yii::t('organization', 'Only the users by entering the above password can join this organization / department proactively.'),
            'join_ip_address' => Yii::t('organization', 'Only the users from the above IP address (segment) can join the organization / department proactively.'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['exclude_other_members', 'disallow_member_join_other', 'only_accept_current_org_member', 'only_accept_superior_org_member'], 'boolean', 'trueValue' => '1', 'falseValue' => '0'],
            [['join_password', 'join_entrance_url'], 'string', 'max' => 255],
            ['join_ip_address', 'ip', 'subnet' => null, 'normalize' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_ORGANIZATION => ['exclude_other_members', 'disallow_member_join_other', 'join_password', 'join_entrance_url', 'join_ip_address'],
            static::SCENARIO_DEPARTMENT => ['only_accept_current_org_member', 'only_accept_superior_org_member', 'join_password', 'join_entrance_url', 'join_ip_address'],
        ];
    }
}

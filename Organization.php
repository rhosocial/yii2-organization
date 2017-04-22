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

use rhosocial\base\models\traits\SelfBlameableTrait;
use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\organization\exceptions\DisallowMemberJoinOtherException;
use rhosocial\organization\exceptions\ExcludeOtherMembersException;
use rhosocial\organization\exceptions\OnlyAcceptCurrentOrgMemberException;
use rhosocial\organization\exceptions\OnlyAcceptSuperiorOrgMemberException;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use rhosocial\organization\queries\MemberQuery;
use rhosocial\organization\queries\OrganizationQuery;
use rhosocial\user\User;
use Yii;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\db\IntegrityException;

/**
 * Organization.
 * This class is used to describe an organization or department, depending on the type property.
 * Organization or department should be created by the user, it is best not to directly implement their own such.
 *
 * In general, the organization needs to have `setUpOrganization` permission, and the user does not have this permission
 * by default. You need to give this permission to the user who created the organization in advance.
 * Department, affiliated with the organization or other department, also need the appropriate permission to set up.
 *
 * While this can work independently, we still strongly recommend that you declare the Organization class yourself and
 * inherit this.
 * Then you need to specify the Profile and Member class yourself, like following:
```php
class Organization extends \rhosocial\organization\Organization
{
    public $profileClass = Profile::class;
    public $memberClass = Member::class;
}
```
 * If you need to limit the number of subordinates, the number of members, you need to specify the appropriate class
 * name.
 * If there is no limit, you need to set it to false manually.
 *
 * @method Member createMember(array $config) Create member who is subordinate to this.
 * @property int $type Whether indicate this instance is an organization or a department.
 *
 * @property bool $isExcludeOtherMembers Determine whether the other organization and its subordinate departments
 * members could join in the current organization and its subordinate departments. (Only fit for Organization)
 * @property bool $isDisallowMemberJoinOther Determine whether the current organization and its subordinate
 * departments members could join in the other organization and its subordinate departments. (Only fit for Organization)
 * @property bool $isOnlyAcceptCurrentOrgMember Determine whether the current department only accept the member of
 * the top level organization. (Only fit for Department)
 * @property bool $isOnlyAcceptSuperiorOrgMember Determine whether the current department only accept the member of
 * the superior organization or department. (Only fit for Department)
 * @property string $joinPassword
 * @property string $joinIpAddress
 * @property string $joinEntranceUrl
 *
 * @property-read Member[] $members Get all member models of this organization/department.
 * @property-read User[] $memberUsers Get all members of this organization/department.
 * @property-read User $creator Get creator of this organization/department.
 * @property-read User[] $administrators Get administrators of this organization/department.
 * @property-read SubordinateLimit subordinateLimit
 * @property-read MemberLimit memberLimit
 * @property-read static|null $topOrganization The top level organization of current organization or departments.
 * @property-read Profile $profile Get profile model. Friendly to IDE.
 * @property-read OrganizationSetting[] $settings Get all settings.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Organization extends User
{
    use SelfBlameableTrait;

    const TYPE_ORGANIZATION = 1;
    const TYPE_DEPARTMENT = 2;

    /**
     * @var boolean Organization does not need password and corresponding features.
     */
    public $passwordHashAttribute = false;

    /**
     * @var boolean Organization does not need password and corresponding features.
     */
    public $passwordResetTokenAttribute = false;

    /**
     * @var boolean Organization does not need password and corresponding features.
     */
    public $passwordHistoryClass = false;

    /**
     * @var boolean Organization does not need source.
     */
    public $sourceAttribute = false;

    /**
     * @var boolean Organization does not need auth key.
     */
    public $authKeyAttribute = false;

    /**
     * @var boolean Organization does not need access token.
     */
    public $accessTokenAttribute = false;

    /**
     * @var boolean Organization does not need login log.
     */
    public $loginLogClass = false;

    /**
     * @var string The Organization Profile Class
     */
    public $profileClass = Profile::class;

    /**
     * @var string The Member Class.
     */
    public $memberClass = Member::class;

    /**
     * @var string The Subordinate Limit Class
     */
    public $subordinateLimitClass = SubordinateLimit::class;

    /**
     * @var string The Member Limit Class
     */
    public $memberLimitClass = MemberLimit::class;

    /**
     * @var string The Organization Search Class
     */
    public $searchClass = OrganizationSearch::class;

    /**
     * @var string The Organization Setting Class
     */
    public $organizationSettingClass = OrganizationSetting::class;

    /**
     * @var Member
     */
    private $noInitMember;

    /**
     * @var SubordinateLimit
     */
    private $noInitSubordinateLimit;

    /**
     * @var MemberLimit
     */
    private $noInitMemberLimit;

    /**
     * @var OrganizationSetting
     */
    private $noInitOrganizationSetting;

    /**
     * @var User the creator of current Organization or Department.
     * This property is only available after registration.
     * Please do not access it at other times.
     * If you want to get creator model except registration, please
     * access [[$creator]] magic-property instead.
     */
    public $creatorModel;

    /**
     * @var array The configuration array of Organization Profile.
     * This property is only available after registration.
     * Please do not access it at other times.
     * If you want to get profile model except registration, please
     * access [[$profile]] magic-property instead.
     */
    public $profileConfig;

    const EVENT_BEFORE_ADD_MEMBER = 'eventBeforeAddMember';
    const EVENT_AFTER_ADD_MEMBER = 'eventAfterAddMember';
    const EVENT_BEFORE_REMOVE_MEMBER = 'eventBeforeRemoveMember';
    const EVENT_AFTER_REMOVE_MEMBER = 'eventAfterRemoveMember';

    public $cacheTagPrefix = 'tag_organization_';

    /**
     * @return Member
     */
    public function getNoInitMember()
    {
        if (!$this->noInitMember) {
            $class = $this->memberClass;
            $this->noInitMember = $class::buildNoInitModel();
        }
        return $this->noInitMember;
    }

    /**
     * @return SubordinateLimit
     */
    public function getNoInitSubordinateLimit()
    {
        if (!$this->noInitSubordinateLimit) {
            $class = $this->subordinateLimitClass;
            $this->noInitSubordinateLimit = $class::buildNoInitModel();
        }
        return $this->noInitSubordinateLimit;
    }

    /**
     * @return MemberLimit
     */
    public function getNoInitMemberLimit()
    {
        if (!$this->noInitMemberLimit) {
            $class = $this->memberLimitClass;
            $this->noInitMemberLimit = $class::buildNoInitModel();
        }
        return $this->noInitMemberLimit;
    }

    /**
     * @return null|OrganizationSetting
     */
    public function getNoInitOrganizationSetting()
    {
        if (!$this->noInitOrganizationSetting) {
            $class = $this->organizationSettingClass;
            if (empty($class)) {
                return null;
            }
            $this->noInitOrganizationSetting = $class::buildNoInitModel();
        }
        return $this->noInitOrganizationSetting;
    }

    /**
     * @return null|OrganizationSearch
     */
    public function getSearchModel()
    {
        $class = $this->searchClass;
        if (empty($class) || !class_exists($class)) {
            return null;
        }
        return new $class;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->parentAttribute = 'parent_guid';
        if (class_exists($this->memberClass)) {
            $this->addSubsidiaryClass('Member', ['class' => $this->memberClass]);
        }
        if ($this->skipInit) {
            return;
        }
        $this->on(static::$eventAfterRegister, [$this, 'onAddProfile'], $this->profileConfig);
        $this->on(static::$eventAfterRegister, [$this, 'onAssignCreator'], $this->creatorModel);
        $this->on(static::EVENT_BEFORE_DELETE, [$this, 'onRevokeCreator']);
        $this->on(static::EVENT_BEFORE_DELETE, [$this, 'onRevokeAdministrators']);
        $this->on(static::EVENT_BEFORE_DELETE, [$this, 'onRevokePermissions']);
        $this->initSelfBlameableEvents();
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            $this->guidAttribute => Yii::t('user', 'GUID'),
            $this->idAttribute => Yii::t('user', 'ID'),
            $this->ipAttribute => Yii::t('user', 'IP Address'),
            $this->ipTypeAttribute => Yii::t('user', 'IP Address Type'),
            $this->parentAttribute => Yii::t('organization', 'Parent'),
            $this->createdAtAttribute => Yii::t('user', 'Creation Time'),
            $this->updatedAtAttribute => Yii::t('user', 'Last Updated Time'),
            $this->statusAttribute => Yii::t('user', 'Status'),
            'type' => Yii::t('user', 'Type'),
            'isExcludeOtherMembers' => Yii::t('organization', 'Exclude Other Members'),
            'isDisallowMemberJoinOther' => Yii::t('organization', 'Disallow Member to Join in Other Organizations'),
            'isOnlyAcceptCurrentOrgMember' => Yii::t('organization', 'Only Accept Current Organization Members'),
            'isOnlyAcceptSuperiorOrgMember' => Yii::t('organization', 'Only Accept Superior Organization Members'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization}}';
    }

    /**
     * Find.
     * Friendly to IDE.
     * @return OrganizationQuery
     */
    public static function find()
    {
        return parent::find();
    }

    /**
     * Get rules associated with type attribute.
     * @return array
     */
    protected function getTypeRules()
    {
        return [
            ['type', 'default', 'value' => static::TYPE_ORGANIZATION],
            ['type', 'required'],
            ['type', 'in', 'range' => [static::TYPE_ORGANIZATION, static::TYPE_DEPARTMENT]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), $this->getTypeRules(), $this->getSelfBlameableRules());
    }

    /**
     * Get Member Query.
     * @return MemberQuery
     */
    public function getMembers()
    {
        return $this->hasMany($this->memberClass, [
            $this->getNoInitMember()->createdByAttribute => $this->guidAttribute
        ])->inverseOf('organization');
    }

    /**
     * Get organization member users' query.
     * @return BaseUserQuery
     */
    public function getMemberUsers()
    {
        $noInit = $this->getNoInitMember();
        $class = $noInit->memberUserClass;
        $noInitUser = $class::buildNoInitModel();
        return $this->hasMany($class, [
            $noInitUser->guidAttribute => $this->getNoInitMember()->memberAttribute
        ])->via('members')->inverseOf('atOrganizations');
    }

    /**
     * Get subordinate limit query.
     * @return null|BaseBlameableQuery
     */
    public function getSubordinateLimit()
    {
        if (empty($this->subordinateLimitClass)) {
            return null;
        }
        return $this->hasOne($this->subordinateLimitClass, [
            $this->getNoInitSubordinateLimit()->createdByAttribute => $this->guidAttribute
        ]);
    }

    /**
     * Get member limit query.
     * @return null|BaseBlameableQuery
     */
    public function getMemberLimit()
    {
        if (empty($this->memberLimitClass)) {
            return null;
        }
        return $this->hasOne($this->memberLimitClass, [
            $this->getNoInitMemberLimit()->createdByAttribute => $this->guidAttribute
        ]);
    }

    /**
     * @param string|null $item If you want to get all settings, please set it null.
     * @return null
     */
    public function getSettings($item = null)
    {
        if (empty($this->organizationSettingClass) || !is_string($this->organizationSettingClass)) {
            return null;
        }
        $query = $this->hasMany($this->organizationSettingClass, [$this->getNoInitOrganizationSetting()->createdByAttribute => $this->guidAttribute]);
        if (!empty($item)) {
            $query = $query->andWhere([$this->getNoInitOrganizationSetting()->idAttribute => $item]);
        }
        return $query;
    }

    /**
     * @param $item
     * @param $value
     * @return bool|null Null if organization setting not enabled.
     */
    public function setSetting($item, $value)
    {
        if (empty($this->organizationSettingClass) || !is_string($this->organizationSettingClass)) {
            return null;
        }
        $setting = $this->getSettings($item)->one();
        /* @var $setting OrganizationSetting */
        if (!$setting) {
            $setting = $this->create($this->organizationSettingClass, [
                $this->getNoInitOrganizationSetting()->idAttribute => $item,
            ]);
        }
        $setting->value = $value;
        return $setting->save();
    }

    /**
     * Get member with specified user.
     * @param User|string|integer $user
     * @return Member Null if `user` is not in this organization.
     */
    public function getMember($user)
    {
        return $this->getMembers()->user($user)->one();
    }

    /**
     * Add member to organization.
     * @param Member|User|string|integer $member Member or User model, or User ID or GUID.
     * If member is created, it will be re-assigned to this parameter.
     * @see createMemberModel
     * @see createMemberModelWithUser
     * @return boolean
     * @throws DisallowMemberJoinOtherException
     * @throws ExcludeOtherMembersException
     * @throws OnlyAcceptCurrentOrgMemberException
     * @throws OnlyAcceptSuperiorOrgMemberException
     */
    public function addMember(&$member)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        if ($this->hasReachedMemberLimit()) {
            return false;
        }
        $user = null;
        if ($member instanceof Member) {
            if ($member->getIsNewRecord()) {
                return false;
            }
            $user = $member->memberUser;
        }
        if ($member instanceof User) {
            $user = $member;
        }
        if (is_string($member) || is_int($member)) {
            $class = Yii::$app->user->identityClass;
            $user = $class::find()->guidOrId($member)->one();
        }
        if ($this->hasMember($user)) {
            return false;
        }
        $orgs = $user->getAtOrganizations()->all();
        /* @var $orgs Organization[] */
        foreach ($orgs as $org) {
            if ($org->topOrganization->isDisallowMemberJoinOther && !$org->topOrganization->equals($this->topOrganization)) {
                throw new DisallowMemberJoinOtherException(Yii::t('organization', "An organization in which the user is located does not allow its members to join other organizations."));
            }
            if ($this->topOrganization->isExcludeOtherMembers && !$org->topOrganization->equals($this->topOrganization)) {
                throw new ExcludeOtherMembersException(Yii::t('organization', "The organization does not allow users who have joined other organizations to join."));
            }
        }
        if ($this->isDepartment() && $this->isOnlyAcceptCurrentOrgMember && !$this->topOrganization->hasMember($user)) {
            throw new OnlyAcceptCurrentOrgMemberException(Yii::t('organization' ,'This department is only accepted by members of the organization.'));
        }
        if ($this->isDepartment() && !$this->parent->equals($this->topOrganization) && $this->isOnlyAcceptSuperiorOrgMember && !$this->parent->hasMember($user)) {
            throw new OnlyAcceptSuperiorOrgMemberException(Yii::t('organization', 'This department only accepts members of the parent organization or department.'));
        }

        $this->trigger(self::EVENT_BEFORE_ADD_MEMBER);
        $model = null;
        if ($member instanceof Member) {
            $model = $this->createMemberModel($member);
        } elseif (($member instanceof User) || is_string($member) || is_int($member)) {
            $model = $this->createMemberModelWithUser($member);
        }
        $member = $model;
        $result = ($member instanceof Member) ? $member->save() : false;
        $this->trigger(self::EVENT_AFTER_ADD_MEMBER);
        return $result;
    }

    /**
     * Create member model, and set organization with this.
     * @param Member $member If this parameter is not new record, it's organization
     * will be set with this, and return it. Otherwise, it will extract `User`
     * model and create new `Member` model.
     * @see createMemberModelWithUser
     * @return Member
     */
    public function createMemberModel($member)
    {
        if (!$member->getIsNewRecord()) {
            $member->setOrganization($this);
            return $member;
        }
        return $this->createMemberModelWithUser($member->memberUser);
    }

    /**
     * Create member model with user, and set organization with this.
     * @param User|string|integer $user
     * @return Member
     */
    public function createMemberModelWithUser($user)
    {
        $config = [
            'memberUser' => $user,
            'organization' => $this,
            'nickname' => '',
        ];
        $member = $this->createMember($config);
        $member->nickname = $member->memberUser->profile->nickname;
        return $member;
    }

    /**
     * Remove member.
     * Note: the creator cannot be removed.
     * @param Member|User $member
     * @return boolean
     */
    public function removeMember(&$member)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        $this->trigger(self::EVENT_BEFORE_REMOVE_MEMBER);
        if ($member instanceof $this->memberClass) {
            $member = $member->{$member->memberAttribute};
        }
        $member = $this->getMember($member);
        if (!$member || $member->isCreator()) {
            return false;
        }
        $result = $member->delete() > 0;
        $this->trigger(self::EVENT_AFTER_REMOVE_MEMBER);
        return $result;
    }

    /**
     * Remove administrator.
     * @param Member|User|integer|string $member Member instance, or User instance or its GUID or ID.
     * @param boolean $keep Keep member after administrator being revoked.
     * @return boolean
     * @throws IntegrityException
     */
    public function removeAdministrator(&$member, $keep = true)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        if ($member instanceof $this->memberClass) {
            $member = $member->{$member->memberAttribute};
        }
        $member = $this->getMember($member);
        if ($member && $member->isAdministrator()) {
            if ($keep) {
                return $member->revokeAdministrator();
            }
            return $this->removeMember($member);
        }
        return false;
    }

    /**
     * 
     * @param Event $event
     * @throws IntegrityException
     * @return boolean
     */
    public function onAddProfile($event)
    {
        $profile = $event->sender->createProfile($event->data);
        if (!$profile->save()) {
            throw new IntegrityException('Profile Save Failed.');
        }
        return true;
    }

    /**
     * 
     * @param Event $event
     */
    public function onAssignCreator($event)
    {
        return $event->sender->addCreator($event->data);
    }

    /**
     * 
     * @param Event $event
     * @return boolean
     */
    public function onRevokeCreator($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        $member = $sender->getMemberCreators()->one();
        /* @var $member Member */
        $role = $this->isOrganization() ? (new OrganizationCreator)->name : (new DepartmentCreator)->name;
        return $member->revokeRole($role);
    }

    /**
     * 
     * @param Event $event
     * @return boolean
     */
    public function onRevokeAdministrators($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        $members = $sender->getMemberAdministrators()->all();
        /* @var $members Member[] */
        foreach ($members as $member)
        {
            $member->revokeAdministrator();
        }
        return true;
    }

    /**
     * 
     * @param Event $event
     */
    public function onRevokePermissions($event)
    {
        
    }

    /**
     * Check whether current instance is an organization.
     * @return boolean
     */
    public function isOrganization()
    {
        return $this->type == static::TYPE_ORGANIZATION;
    }

    /**
     * Check whether current instance if a department.
     * @return boolean
     */
    public function isDepartment()
    {
        return $this->type == static::TYPE_DEPARTMENT;
    }

    /**
     * Check whether the current organization has a member.
     * @param User|string|integer $user User instance, GUID or ID.
     * @return boolean
     */
    public function hasMember($user)
    {
        return !empty($this->getMember($user));
    }

    /**
     * Get member query which role is specified `Creator`.
     * @return MemberQuery
     */
    public function getMemberCreators()
    {
        return $this->getMembers()->andWhere(['role' => [(new DepartmentCreator)->name, (new OrganizationCreator)->name]]);
    }

    /**
     * Get member query which role is specified `Administrator`.
     * @return MemberQuery
     */
    public function getMemberAdministrators()
    {
        return $this->getMembers()->andWhere(['role' => [(new DepartmentAdmin)->name, (new OrganizationAdmin)->name]]);
    }

    /**
     * Get user query which role is specified `Creator`.
     * @return BaseUserQuery
     */
    public function getCreator()
    {
        $noInit = $this->getNoInitMember();
        $class = $noInit->memberUserClass;
        $noInitUser = $class::buildNoInitModel();
        return $this->hasOne($class, [
            $noInitUser->guidAttribute => $this->getNoInitMember()->memberAttribute
        ])->via('memberCreators')->inverseOf('creatorsAtOrganizations');
    }

    /**
     * Get user query which role is specified `Administrator`.
     * @return BaseUserQuery
     */
    public function getAdministrators()
    {
        $noInit = $this->getNoInitMember();
        $class = $noInit->memberUserClass;
        $noInitUser = $class::buildNoInitModel();
        return $this->hasMany($class, [
            $noInitUser->guidAttribute => $this->getNoInitMember()->memberAttribute
        ])->via('memberAdministrators')->inverseOf('administratorsAtOrganizations');
    }

    /**
     * 
     * @param User $user
     * @return boolean
     * @throws \Exception
     * @throws IntegrityException
     */
    protected function addCreator($user)
    {
        if (!$user) {
            throw new InvalidParamException('Creator Invalid.');
        }
        $member = $user;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->addMember($member)) {
                throw new IntegrityException('Failed to add member.');
            }
            $role = $this->isOrganization() ? (new OrganizationCreator)->name : (new DepartmentCreator)->name;
            $member->assignRole($role);
            if (!$member->save()) {
                throw new IntegrityException('Failed to assign creator.');
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
     * Add administrator.
     * @param User|integer|string $user User instance, or its GUID or ID.
     * @return boolean
     * @throws \Exception
     * @throws IntegrityException
     */
    public function addAdministrator($user)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->hasMember($user) && !$this->addMember($user)) {
                throw new IntegrityException(Yii::t('organization', 'Failed to add member.'));
            }
            $member = $this->getMember($user);
            $member->assignAdministrator();
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        return true;
    }

    /**
     * Check whether the current organization has administrator.
     * @param User|integer|string $user
     * @return boolean
     */
    public function hasAdministrator($user)
    {
        $member = $this->getMember($user);
        if (!$member) {
            return false;
        }
        return $member->isAdministrator();
    }

    /**
     * Check whether this organization has reached the upper limit of subordinates.
     * @return boolean
     */
    public function hasReachedSubordinateLimit()
    {
        $remaining = $this->getRemainingSubordinatePlaces();
        if ($remaining === false) {
            return false;
        }
        return $remaining <= 0;
    }

    /**
     * Get the remaining places of subordinates.
     * @return bool|int False if no limit
     */
    public function getRemainingSubordinatePlaces()
    {
        $class = $this->subordinateLimitClass;
        if (empty($class)) {
            return false;
        }
        $limit = $class::getLimit($this);
        if ($limit === false) {
            return false;
        }
        $count = (int)$this->getChildren()->count();
        return $limit - $count;
    }

    /**
     * Check whether this organization has reached the upper limit of members.
     * @return boolean
     */
    public function hasReachedMemberLimit()
    {
        $remaining = $this->getRemainingMemberPlaces();
        if ($remaining === false) {
            return false;
        }
        return $remaining <= 0;
    }

    /**
     * Get the remaining places of members.
     * @return bool|int False if no limit.
     */
    public function getRemainingMemberPlaces()
    {
        $class = $this->memberLimitClass;
        if (empty($class)) {
            return false;
        }
        $limit = $class::getLimit($this);
        if ($limit === false) {
            return false;
        }
        $count = (int)$this->getMembers()->count();
        return $limit - $count;
    }

    const SETTING_ITEM_EXCLUDE_OTHER_MEMBERS = 'exclude_other_members';

    /**
     * @return bool
     */
    public function getIsExcludeOtherMembers()
    {
        $setting = $this->getSettings(static::SETTING_ITEM_EXCLUDE_OTHER_MEMBERS)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_EXCLUDE_OTHER_MEMBERS, '0');
            $setting = $this->getSettings(static::SETTING_ITEM_EXCLUDE_OTHER_MEMBERS)->one();
        }
        return $setting->value == '1';
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function setIsExcludeOtherMembers($value = true)
    {
        return $this->setSetting(static::SETTING_ITEM_EXCLUDE_OTHER_MEMBERS, $value ? '1' : '0');
    }

    const SETTING_ITEM_DISALLOW_MEMBER_JOIN_OTHER = 'disallow_member_join_other';

    /**
     * @return bool
     */
    public function getIsDisallowMemberJoinOther()
    {
        $setting = $this->getSettings(static::SETTING_ITEM_DISALLOW_MEMBER_JOIN_OTHER)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_DISALLOW_MEMBER_JOIN_OTHER, '0');
            $setting = $this->getSettings(static::SETTING_ITEM_DISALLOW_MEMBER_JOIN_OTHER)->one();
        }
        return $setting->value == '1';
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function setIsDisallowMemberJoinOther($value = true)
    {
        return $this->setSetting(static::SETTING_ITEM_DISALLOW_MEMBER_JOIN_OTHER, $value ? '1' : '0');
    }

    const SETTING_ITEM_ONLY_ACCEPT_CURRENT_ORG_MEMBER = 'only_accept_current_org_member';

    /**
     * @return bool
     */
    public function getIsOnlyAcceptCurrentOrgMember()
    {
        $setting = $this->getSettings(static::SETTING_ITEM_ONLY_ACCEPT_CURRENT_ORG_MEMBER)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_ONLY_ACCEPT_CURRENT_ORG_MEMBER, '0');
            $setting = $this->getSettings(static::SETTING_ITEM_ONLY_ACCEPT_CURRENT_ORG_MEMBER)->one();
        }
        return $setting->value == '1';
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function setIsOnlyAcceptCurrentOrgMember($value = true)
    {
        return $this->setSetting(static::SETTING_ITEM_ONLY_ACCEPT_CURRENT_ORG_MEMBER, $value ? '1' : '0');
    }

    const SETTING_ITEM_ONLY_ACCEPT_SUPERIOR_ORG_MEMBER = 'only_accept_superior_org_member';

    /**
     * @return bool
     */
    public function getIsOnlyAcceptSuperiorOrgMember()
    {
        if ($this->parent->equals($this->topOrganization)) {
            return $this->getIsOnlyAcceptCurrentOrgMember();
        }
        $setting = $this->getSettings(static::SETTING_ITEM_ONLY_ACCEPT_SUPERIOR_ORG_MEMBER)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_ONLY_ACCEPT_SUPERIOR_ORG_MEMBER, '0');
            $setting = $this->getSettings(static::SETTING_ITEM_ONLY_ACCEPT_SUPERIOR_ORG_MEMBER)->one();
        }
        return $setting->value == '1';
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function setIsOnlyAcceptSuperiorOrgMember($value = true)
    {
        if ($this->parent->equals($this->topOrganization)) {
            return $this->setIsOnlyAcceptCurrentOrgMember($value);
        }
        return $this->setSetting(static::SETTING_ITEM_ONLY_ACCEPT_SUPERIOR_ORG_MEMBER, $value ? '1' : '0');
    }

    const SETTING_ITEM_JOIN_PASSWORD = 'join_password';

    /**
     * Get join password.
     * @return mixed
     */
    public function getJoinPassword()
    {
        $setting = $this->getSettings(static::SETTING_ITEM_JOIN_PASSWORD)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_JOIN_PASSWORD, '');
            $setting = $this->getSettings(static::SETTING_ITEM_JOIN_PASSWORD)->one();
        }
        return $setting->value;
    }

    /**
     * Set join password.
     * @param string $value
     * @return bool|null
     */
    public function setJoinPassword($value = '')
    {
        return $this->setSetting(static::SETTING_ITEM_JOIN_PASSWORD, $value);
    }

    const SETTING_ITEM_JOIN_IP_ADDRESS = 'join_ip_address';

    /**
     * Get Join IP address
     * @return mixed
     */
    public function getJoinIpAddress()
    {
        $setting = $this->getSettings(static::SETTING_ITEM_JOIN_IP_ADDRESS)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_JOIN_IP_ADDRESS, '');
            $setting = $this->getSettings(static::SETTING_ITEM_JOIN_IP_ADDRESS)->one();
        }
        return $setting->value;
    }

    /**
     * Set join IP address.
     * @param $value
     * @return bool|null
     */
    public function setJoinIpAddress($value = '')
    {
        return $this->setSetting(static::SETTING_ITEM_JOIN_IP_ADDRESS, $value);
    }

    const SETTING_ITEM_JOIN_ENTRANCE_URL = 'join_entrance_url';

    /**
     * Get join entrance URL.
     * @return string
     */
    public function getJoinEntranceUrl()
    {
        $setting = $this->getSettings(static::SETTING_ITEM_JOIN_ENTRANCE_URL)->one();
        if (!$setting) {
            $this->setSetting(static::SETTING_ITEM_JOIN_ENTRANCE_URL, '');
            $setting = $this->getSettings(static::SETTING_ITEM_JOIN_ENTRANCE_URL)->one();
        }
        return $setting->value;
    }

    /**
     * Set join entrance URL.
     * @param string $value
     * @return bool|null
     */
    public function setJoinEntranceUrl($value = '')
    {
        return $this->setSetting(static::SETTING_ITEM_JOIN_ENTRANCE_URL, $value);
    }

    /**
     * @return $this|null|static
     */
    public function getTopOrganization()
    {
        if ($this->isOrganization()) {
            return $this;
        }
        $chain = $this->getAncestorChain();
        return static::findOne(end($chain));
    }

    /**
     * Check whether the subordinates have the [[$user]]
     * Note, this operation may consume the quantity of database selection.
     * @param User $user
     * @return bool
     */
    public function hasMemberInSubordinates($user)
    {
        if ($this->getChildren()->joinWith(['memberUsers mu_alias'])
            ->andWhere(['mu_alias.' . $user->guidAttribute => $user->getGUID()])->exists()) {
            return true;
        }
        $children = $this->children;
        /* @var $children static[] */
        foreach ($children as $child) {
            if ($child->hasMemberInSubordinates($user)) {
                return true;
            }
        }
        return false;
    }
}

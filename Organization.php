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
use rhosocial\user\User;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use rhosocial\organization\queries\MemberQuery;
use rhosocial\organization\queries\OrganizationQuery;
use Yii;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\db\IntegrityException;

/**
 * Base Organization.
 * This class is an abstract class that can not be instantiated directly.
 * You can use [[Organization]] or [[Department]] instead.
 *
 * @method Member createMember(array $config) Create member who is subordinate to this.
 * @property int $type Whether indicate this instance is an organization or a department.
 * @property int $eom Fit for [[$isExcludeOtherMembers]]. Do not modify it directly.
 * @property int $djo Fit for [[$isDisallowMemberJoinInOther]]. Do not modify it directly.
 * @property int $oacm Fit for [[$isOnlyAcceptCurrentOrgMember]]. Do not modify it directly.
 * @property int $oasm Fit for [[$isOnlyAcceptSuperiorOrgMember]]. Do not modify it directly.
 *
 * @property bool $isExcludeOtherMembers Determine whether the other organization and its subordinate departments
 * members could join in the current organization and its subordinate departments. (Only fit for Organization)
 * @property bool $isDisallowMemberJoinInOther Determine whether the current organization and its subordinate
 * departments members could join in the other organization and its subordinate departments. (Only fit for Organization)
 * @property bool $isOnlyAcceptCurrentOrgMember Determine whether the current department only accept the member of
 * the top level organization. (Only fit for Department)
 * @property bool $isOnlyAcceptSuperiorOrgMember Determine whether the current department only accept the member of
 * the superior organization or department. (Only fit for Department)
 *
 * @property-read Member[] $members Get all member models of this organization/department.
 * @property-read User[] $memberUsers Get all members of this organization/department.
 * @property-read User $creator Get creator of this organization/department.
 * @property-read User[] $administrators Get administrators of this organization/department.
 * @property-read SubordinateLimit subordinateLimit
 * @property-read MemberLimit memberLimit
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
     *
     * @var boolean Organization does not need login log.
     */
    public $loginLogClass = false;

    public $profileClass = Profile::class;

    public $memberClass = Member::class;
    public $subordinateLimitClass = SubordinateLimit::class;
    public $memberLimitClass = MemberLimit::class;
    public $searchClass = OrganizationSearch::class;
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
    public $creatorModel;
    public $profileConfig;

    const EVENT_BEFORE_ADD_MEMBER = 'eventBeforeAddMember';
    const EVENT_AFTER_ADD_MEMBER = 'eventAfterAddMember';
    const EVENT_BEFORE_REMOVE_MEMBER = 'eventBeforeRemoveMember';
    const EVENT_AFTER_REMOVE_MEMBER = 'eventAfterRemoveMember';

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
            'guid' => Yii::t('user', 'GUID'),
            'id' => Yii::t('user', 'ID'),
            'ip' => Yii::t('user', 'IP Address'),
            'ip_type' => Yii::t('user', 'IP Address Type'),
            'parent' => Yii::t('organization', 'Parent'),
            'created_at' => Yii::t('user', 'Creation Time'),
            'updated_at' => Yii::t('user', 'Last Updated Time'),
            'status' => Yii::t('user', 'Status'),
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

    protected function getTypeRules()
    {
        return [
            ['type', 'default', 'value' => static::TYPE_ORGANIZATION],
            ['type', 'required'],
            ['type', 'in', 'range' => [static::TYPE_ORGANIZATION, static::TYPE_DEPARTMENT]],
        ];
    }

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
        return $this->hasMany($this->memberClass, [$this->getNoInitMember()->createdByAttribute => $this->guidAttribute])->inverseOf('organization');
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
        return $this->hasMany($class, [$noInitUser->guidAttribute => $this->getNoInitMember()->memberAttribute])->via('members')->inverseOf('atOrganizations');
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
        return $this->hasOne($this->subordinateLimitClass, [$this->getNoInitSubordinateLimit()->createdByAttribute => $this->guidAttribute]);
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
        return $this->hasOne($this->memberLimitClass, [$this->getNoInitMemberLimit()->createdByAttribute => $this->guidAttribute]);
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
     */
    public function addMember(&$member)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        if ($this->hasReachedMemberLimit()) {
            return false;
        }
        $this->trigger(self::EVENT_BEFORE_ADD_MEMBER);
        $model = null;
        if ($member instanceof Member) {
            if (!$member->getIsNewRecord()) {
                return false;
            }
            $model = $this->createMemberModel($member);
        }
        if (($member instanceof User) || is_string($member) || is_int($member)) {
            if ($this->hasMember($member)) {
                return false;
            }
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
        $role = $this->type == static::TYPE_ORGANIZATION ? (new OrganizationCreator)->name : (new DepartmentCreator)->name;
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
        return !is_null($this->getMember($user));
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
        return $this->hasOne($class, [$noInitUser->guidAttribute => $this->getNoInitMember()->memberAttribute])->via('memberCreators')->inverseOf('creatorsAtOrganizations');
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
        return $this->hasMany($class, [$noInitUser->guidAttribute => $this->getNoInitMember()->memberAttribute])->via('memberAdministrators')->inverseOf('administratorsAtOrganizations');
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
            $role = $this->type == static::TYPE_ORGANIZATION ? (new OrganizationCreator)->name : (new DepartmentCreator)->name;
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
        $class = $this->subordinateLimitClass;
        if (empty($class)) {
            return false;
        }
        $limit = $class::getLimit($this);
        if ($limit === false) {
            return false;
        }
        $count = (int)$this->getChildren()->count();
        return $count >= $limit;
    }

    /**
     * Check whether this organization has reached the upper limit of members.
     * @return boolean
     */
    public function hasReachedMemberLimit()
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
        return $count >= $limit;
    }

    public function getIsExcludeOtherMembers()
    {
        return $this->eom > 0;
    }
    public function setIsExcludeOtherMembers($value = true)
    {
        $this->eom = ($value) ? 1 : 0;
    }
    public function getIsDisallowMemberJoinOther()
    {
        return $this->djo > 0;
    }
    public function setIsDisallowMemberJoinOther($value = true)
    {
        $this->djo = ($value) ? 1 : 0;
    }
    public function getIsOnlyAcceptOrgMember()
    {
        return $this->oaom > 0;
    }
    public function setIsOnlyAcceptOrgMember($value = true)
    {
        $this->oaom = ($value) ? 1 : 0;
    }
    public function getIsOnlyAcceptSuperiorOrgMember()
    {
        return $this->oasm > 0;
    }
    public function setIsOnlyAcceptSuperiorOrgMember($value = true)
    {
        $this->oasm = ($value) ? 1 : 0;
    }

    /**
     * @return $this|null|static
     */
    public function getTopOrganization()
    {
        if ($this->isOrganization()) {
            return $this;
        }
        return static::findOne(end($this->getAncestorChain()));
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

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
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\user\User;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use rhosocial\organization\queries\MemberQuery;
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
 * @property integer $type Whether indicate this instance is an organization or a department.
 *
 * @property-read User[] $memberUsers Get all members of this organization/department.
 * @property-read User $creator Get creator of this organization/department.
 * @property-read User[] $administrators Get administrators of this organization/department.
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
    private $noInitMember;
    public $creatorModel;
    public $profileConfig;
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
        $this->on(static::$eventBeforeDeregister, [$this, 'onRevokeCreator']);
        $this->on(static::$eventBeforeDeregister, [$this, 'onRevokeAdministrators']);
        $this->on(static::$eventBeforeDeregister, [$this, 'onRevokePermissions']);
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
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization}}';
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
     * @param Member|User|string|integer $member
     * @see createMemberModel
     * @see createMemberModelWithUser
     * @return boolean
     */
    public function addMember(&$member)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
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
        return ($member instanceof Member) ? $member->save() : false;
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
     * @param Member|User $member
     * @return boolean
     */
    public function removeMember(&$member)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        if ($member instanceof $this->memberClass) {
            $member = $member->{$member->memberAttribute};
        }
        $member = $this->getMember($member);
        return $member && $member->delete() > 0;
    }

    /**
     * Remove administrator.
     * @param Member|User $member
     * @param boolean $keepMember Keep member after administrator being revoked.
     * @return boolean
     * @throws IntegrityException
     */
    public function removeAdministrator(&$member, $keepMember = true)
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        if ($member instanceof $this->memberClass) {
            $member = $member->{$member->memberAttribute};
        }
        $member = $this->getMember($member);
        if ($member && $member->isAdministrator()) {
            if ($keepMember) {
                return $member->revokeAdministrator();
            }
            return $this->removeMember($member);
        }
        return false;
    }

    /**
     * 
     * @param Event $event
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
     * 
     * @param User $user
     * @return boolean
     * @throws \Exception
     * @throws IntegrityException
     */
    public function addAdministrator($user)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->hasMember($user) && !$this->addMember($user)) {
                throw new IntegrityException('Failed to add member.');
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
     * 
     * @param type $user
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
}

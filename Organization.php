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
use rhosocial\organization\queries\MemberQuery;
use rhosocial\organization\queries\DepartmentQuery;
use rhosocial\organization\queries\OrganizationQuery;
use Yii;

/**
 * Base Organization.
 * This class is an abstract class that can not be instantiated directly.
 * You can use [[Organization]] or [[Department]] instead.
 *
 * @method Member createMember(array $config) Create member who is subordinate to this.
 * @property integer $type Whether indicate this instance is an organization or a department.
 
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
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'GUID'),
            'id' => Yii::t('app', 'ID'),
            'ip' => Yii::t('app', 'IP'),
            'ip_type' => Yii::t('app', 'IP Address Type'),
            'parent' => Yii::t('app', 'Parent'),
            'created_at' => Yii::t('app', 'Create Time'),
            'updated_at' => Yii::t('app', 'Update Time'),
            'status' => Yii::t('app', 'Status'),
            'type' => Yii::t('app', 'Type'),
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
            $model = $this->createMemberModel($member);
        }
        if (($member instanceof User) || is_string($member) || is_int($member)) {
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
        if ($user->profile) {
            $config['nickname'] = $user->profile->nickname;
        }
        return $this->createMember($config);
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
}

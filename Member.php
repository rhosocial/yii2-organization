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

use rhosocial\base\models\models\BaseBlameableModel;
use rhosocial\user\rbac\Item;
use rhosocial\user\rbac\Role;
use rhosocial\user\User;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use rhosocial\organization\queries\OrganizationQuery;
use rhosocial\organization\queries\MemberQuery;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use yii\db\IntegrityException;

/**
 * Organization member.
 *
 * @property string $organization_guid
 * @property string $user_guid store guid of user who represents this member.
 * @property string $nickname
 * @property string $role
 * @property string $position
 * @property string $description
 * 
 * @property string $department_guid
 * @property string $member_guid
 * @property Organization $organization
 * @property User $memberUser
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Member extends BaseBlameableModel
{
    public $createdByAttribute = 'organization_guid';
    public $updatedByAttribute = false;
    public $hostClass = Organization::class;

    public $memberAttribute = 'user_guid';
    public $memberUserClass = User::class;
    public $contentAttribute = 'nickname';
    public $searchClass = MemberSearch::class;
    private $noInitMemberUser;
    /**
     * @return User
     */
    public function getNoInitMemberUser()
    {
        if (!$this->noInitMemberUser) {
            $class = $this->memberUserClass;
            $this->noInitMemberUser = $class::buildNoInitModel();
        }
        return $this->noInitMemberUser;
    }

    /**
     * @return null|MemberSearch
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
        if (!is_string($this->queryClass)) {
            $this->queryClass = MemberQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        parent::init();
    }

    public $descriptionAttribute = 'description';

    public function getMemberUserRules()
    {
        return [
            [$this->memberAttribute, 'required'],
            [$this->memberAttribute, 'string', 'max' => 36],
        ];
    }

    public function getMemberRoleRules()
    {
        return [
            ['role', 'string', 'max' => 255],
        ];
    }

    public function getMemberPositionRules()
    {
        return [
            ['position', 'default', 'value' => ''],
            ['position', 'string', 'max' => 255],
        ];
    }

    /**
     * Set member user.
     * @param User|string|integer $user
     */
    public function setMemberUser($user)
    {
        $class = $this->memberUserClass;
        if (is_numeric($user)) {
            $user = $class::find()->id($user)->one();
        }
        if ($user instanceof $class) {
            $user = $user->getGUID();
        }
        $this->{$this->memberAttribute} = $user;
    }

    public function getMemberUser()
    {
        return $this->hasOne($this->memberUserClass, [$this->getNoInitMemberUser()->guidAttribute => $this->memberAttribute]);
    }

    /**
     * Get Organization Query.
     * Alias of `getHost` method.
     * @return OrganizationQuery
     */
    public function getOrganization()
    {
        return $this->getHost();
    }

    /**
     * Set Organization.
     * @param BaseOrganization $organization
     * @return boolean
     */
    public function setOrganization($organization)
    {
        return $this->setHost($organization);
    }

    /**
     * Assign role.
     * The setting role operation will not take effect immediately. You should
     * wrap this method and the subsequent save operations together into a
     * transaction, in order to ensure data cosistency.
     * @param Role $role
     * @return boolean
     */
    public function assignRole($role)
    {
        $user = $this->memberUser;
        if (!$user) {
            throw new InvalidValueException('Invalid User');
        }
        if ($role instanceof Item) {
            $role = $role->name;
        }
        $assignment = Yii::$app->authManager->getAssignment($role, $user);
        if (!$assignment) {
            $assignment = Yii::$app->authManager->assign($role, $user->getGUID());
        }
        return $this->setRole($role);
    }

    /**
     * Assign administrator.
     * @return boolean
     * @throws \Exception
     * @throws IntegrityException
     */
    public function assignAdministrator()
    {
        $host = $this->organization;
        /* @var $host Organization */
        if ($this->isCreator()) {
            throw new InvalidCallException(Yii::t('organization', 'The user is already a creator.'));
        }
        if ($this->isAdministrator()) {
            throw new InvalidCallException(Yii::t('organization', 'The user is already an administrator.'));
        }
        $role = null;
        if ($host->type == Organization::TYPE_ORGANIZATION) {
            $role = new OrganizationAdmin();
        } elseif ($host->type == Organization::TYPE_DEPARTMENT) {
            $role = new DepartmentAdmin();
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->assignRole($role);
            if (!$this->save()) {
                throw new IntegrityException(Yii::t('organization', 'Failed to assign administrator.'));
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
     * Set role.
     * @param Role $role
     * @return boolean
     */
    public function setRole($role = null)
    {
        if (empty($role)) {
            $role = '';
        }
        if ($role instanceof Item) {
            $role = $role->name;
        }
        $this->role = $role;
        return true;
    }

    /**
     * Revoke role.
     * @param Role $role
     * @throws InvalidParamException
     * @throws IntegrityException
     * @throws \Exception
     * @return boolean
     */
    public function revokeRole($role)
    {
        $user = $this->memberUser;
        if (!$user) {
            throw new InvalidValueException('Invalid User');
        }
        if ($role instanceof Item) {
            $role = $role->name;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $assignment = Yii::$app->authManager->getAssignment($role, $user);
            if ($assignment) {
                $count = (int)($user->getOfMembers()->role($role)->count());
                if ($count <= 1) {
                    Yii::$app->authManager->revoke($role, $user);
                }
            }
            $this->setRole();
            if (!$this->save()) {
                throw new IntegrityException('Save failed.');
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
     * Revoke administrator.
     * @return boolean
     * @throws IntegrityException
     * @throws \Exception
     */
    public function revokeAdministrator()
    {
        $host = $this->organization;
        /* @var $host Organization */
        if ($this->isCreator()) {
            throw new InvalidCallException(Yii::t('organization', 'The user is already a creator.'));
        }
        if (!$this->isAdministrator()) {
            throw new InvalidCallException(Yii::t('organization', 'The user is not administrator yet.'));
        }
        $role = null;
        if ($host->type == Organization::TYPE_ORGANIZATION) {
            $role = new OrganizationAdmin();
        } elseif ($host->type == Organization::TYPE_DEPARTMENT) {
            $role = new DepartmentAdmin();
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->revokeRole($role);
            if (!$this->save()) {
                throw new IntegrityException("Failed to revoke administrator.");
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex->getMessage(), __METHOD__);
            throw $ex;
        }
        return true;
    }

    public function rules()
    {
        return array_merge($this->getMemberUserRules(), $this->getMemberRoleRules(), $this->getMemberPositionRules(), parent::rules());
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_member}}';
    }

    /**
     * Find.
     * Friendly to IDE.
     * @return MemberQuery
     */
    public static function find()
    {
        return parent::find();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            $this->guidAttribute => Yii::t('user', 'GUID'),
            $this->idAttribute => Yii::t('user', 'ID'),
            $this->createdByAttribute => Yii::t('organization', 'Organization GUID'),
            $this->memberAttribute => Yii::t('organization', 'User GUID'),
            $this->contentAttribute => Yii::t('user', 'Nickname'),
            'role' => Yii::t('organization', 'Role'),
            'position' => Yii::t('organization', 'Member Position'),
            $this->descriptionAttribute => Yii::t('organization', 'Description'),
            $this->ipAttribute => Yii::t('user', 'IP Address'),
            $this->ipTypeAttribute => Yii::t('user', 'IP Address Type'),
            $this->createdAtAttribute => Yii::t('organization', 'Join Time'),
            $this->updatedAtAttribute => Yii::t('user', 'Last Updated Time'),
        ];
    }

    /**
     * @return bool
     */
    public function isDepartmentAdministrator()
    {
        return $this->role == (new DepartmentAdmin)->name;
    }

    /**
     * @return bool
     */
    public function isDepartmentCreator()
    {
        return $this->role == (new DepartmentCreator)->name;
    }

    /**
     * @return bool
     */
    public function isOrganizationAdministrator()
    {
        return $this->role == (new OrganizationAdmin)->name;
    }

    /**
     * @return bool
     */
    public function isOrganizationCreator()
    {
        return $this->role == (new OrganizationCreator)->name;
    }

    /**
     * Check whether current member is administrator.
     * @return boolean
     */
    public function isAdministrator()
    {
        return $this->isDepartmentAdministrator() || $this->isOrganizationAdministrator();
    }

    /**
     * Check whether current member is creator.
     * @return boolean
     */
    public function isCreator()
    {
        return $this->isDepartmentCreator() || $this->isOrganizationCreator();
    }

    /**
     * We think it a `member` if `role` property is empty.
     * @return boolean
     */
    public function isOnlyMember()
    {
        return empty($this->role);
    }

    const SCENARIO_UPDATE = 'update';
    const SCENARIO_ADMIN_UPDATE = 'admin_update';

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_UPDATE => [$this->contentAttribute,],
        ], [
            self::SCENARIO_ADMIN_UPDATE => [$this->contentAttribute, 'position', $this->descriptionAttribute],
        ]);
    }
}

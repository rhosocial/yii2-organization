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
use rhosocial\user\User;
use rhosocial\organization\queries\OrganizationQuery;
use rhosocial\organization\queries\MemberQuery;
use Yii;

/**
 * Organization member.
 *
 * @property string $organization_guid
 * @property string $user_guid store guid of user who represents this member.
 * @property string $nickname
 * @property string $role
 * @property string $description
 * 
 * @property string $department_guid
 * @property string $member_guid
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
    private $noInitMemberUser;
    /**
     * @return User
     */
    protected function getNoInitMemberUser()
    {
        if (!$this->noInitMemberUser) {
            $class = $this->memberUserClass;
            $this->noInitMemberUser = $class::buildNoInitModel();
        }
        return $this->noInitMemberUser;
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

    /**
     * Set member user.
     * @param User|string|integer $user
     */
    public function setMemberUser($user)
    {
        $class = $this->memberUserClass;
        if (is_int($user)) {
            $user = $class::find()->id($user)->one();
        }
        if ($user instanceof $class) {
            $user = $user->getGUID();
        }
        $this->user_guid = $user;
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

    public function assignRole($role)
    {
        
    }

    public function removeRole($role)
    {
        
    }

    public function rules()
    {
        return array_merge($this->getMemberUserRules(), $this->getMemberRoleRules(), parent::rules());
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_member}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'GUID'),
            'id' => Yii::t('app', 'ID'),
            'organization_guid' => Yii::t('app', 'Organization GUID'),
            'user_guid' => Yii::t('app', 'User GUID'),
            'nickname' => Yii::t('app', 'Nickname'),
            'description' => Yii::t('app', 'Description'),
            'ip' => Yii::t('app', 'IP'),
            'ip_type' => Yii::t('app', 'IP Address Type'),
            'created_at' => Yii::t('app', 'Create Time'),
            'updated_at' => Yii::t('app', 'Update Time'),
        ];
    }
}

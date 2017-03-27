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

/**
 * Organization member.
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

    public $descriptionAttribute = 'description';

    public function getMemberUserRules()
    {
        return [
            [$this->memberAttribute, 'required'],
            [$this->memberAttribute, 'string', 'max' => 36],
        ];
    }

    /**
     * 
     * @param User $user
     */
    public function setMemberUser($user)
    {
        $this->user_guid = $user->getGUID();
    }

    public function getMemberUser()
    {
        $class = $this->memberUserClass;
        $noInit = $class::buildInitNoModel();
        /* @var $noInit User */
        return $this->hasOne($this->memberUserClass, [$this->memberAttribute => $noInit->guidAttribute]);
    }

    public function rules()
    {
        return array_merge($this->getMemberUserRules(), parent::rules());
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_member}}';
    }
}

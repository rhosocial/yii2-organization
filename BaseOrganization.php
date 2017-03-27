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
use rhosocial\user\User;

/**
 * Organization.
 * The organization can open sub-organizations & departments.
 * An organization can exist either alone or as a sub-organization of an
 * organization but not department.
 *
 * @property integer $type Whether indicate this instance is an organization or a department.
 
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseOrganization extends User
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

    abstract protected function typeAttributeBehavior();

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), $this->typeAttributeBehavior());
    }

    abstract protected function getTypeRules();

    public function rules()
    {
        return array_merge(parent::rules(), $this->getTypeRules(), $this->getSelfBlameableRules());
    }
}

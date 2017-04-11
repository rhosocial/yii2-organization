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
use Yii;

/**
 * Class OrganizationLimit
 *
 * @property integer $limit
 *
 * @package rhosocial\organization
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationLimit extends BaseBlameableModel
{
    public $contentAttribute = 'limit';
    public $contentAttributeRule = ['integer', 'min' => 0];
    public $updatedByAttribute = false;
    public $idAttribute = false;
    /**
     * @var string The host who owns this model.
     * Note: Please assign it with your own User model.
     */
    public $hostClass = User::class;
    public $defaultLimit = 10;

    public static function tableName()
    {
        return '{{%organization_limit}}';
    }

    protected function getLimitRules()
    {
        return [
            [$this->contentAttribute, 'default', 'value' => is_numeric($this->defaultLimit) ? (int)$this->defaultLimit : 10]
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), $this->getLimitRules());
    }

    public function attributeLabels()
    {
        return [
            $this->guidAttribute => Yii::t('user', 'GUID'),
            $this->createdByAttribute => Yii::t('user', 'User GUID'),
            $this->contentAttribute => Yii::t('organization', 'Limit'),
            $this->ipAttribute => Yii::t('user', 'IP Address'),
            $this->ipTypeAttribute => Yii::t('user', 'IP Address Type'),
            $this->createdAtAttribute => Yii::t('user', 'Creation Time'),
            $this->updatedAtAttribute => Yii::t('user', 'Last Updated Time'),
        ];
    }

    /**
     * Get the upper limit of organizations the user could set up.
     * @param User|integer|string $user
     * @return int|boolean False if no limit.
     */
    public static function getLimit($user)
    {
        if (!($user instanceof User) || ($user->getIsNewRecord() && $user = $user->getGUID())) {
            $noInit = static::buildNoInitModel();
            $class = $noInit->hostClass;
            $user = $class::find()->guidOrId($user)->one();
        }
        /* @var $user User */
        if (empty($user->organizationLimitClass)) {
            return false;
        }
        $limit = static::find()->createdBy($user)->one();
        /* @var $limit static */
        if (!$limit) {
            $limit = $user->create(static::class);
            $limit->save();
        }
        return $limit->limit;
    }
}

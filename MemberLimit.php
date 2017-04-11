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

class MemberLimit extends BaseBlameableModel
{
    public $contentAttribute = 'limit';
    public $contentAttributeRule = ['integer', 'min' => 0];
    public $createdByAttribute = 'organization_guid';
    public $updatedByAttribute = false;
    public $idAttribute = false;
    /**
     * @var string The host who owns this model.
     * Note: Please assign it with your own Organization model.
     */
    public $hostClass = Organization::class;
    public $defaultLimit = 100;

    public static function tableName()
    {
        return '{{%organization_member_limit}}';
    }

    protected function getLimitRules()
    {
        return [
            [$this->contentAttribute, 'default', 'value' => is_numeric($this->defaultLimit) ? (int)$this->defaultLimit : 100]
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
            $this->createdByAttribute => Yii::t('organization', 'Organization GUID'),
            $this->contentAttribute => Yii::t('organization', 'Limit'),
            $this->ipAttribute => Yii::t('user', 'IP Address'),
            $this->ipTypeAttribute => Yii::t('user', 'IP Address Type'),
            $this->createdAtAttribute => Yii::t('user', 'Creation Time'),
            $this->updatedAtAttribute => Yii::t('user', 'Last Updated Time'),
        ];
    }

    /**
     * Get the upper limit of members.
     * @param $organization
     * @return int|boolean
     */
    public static function getLimit($organization)
    {
        if (!($organization instanceof Organization) || ($organization->getIsNewRecord() && $organization = $organization->getGUID())) {
            $noInit = static::buildNoInitModel();
            $class = $noInit->hostClass;
            $organization = $class::find()->guidOrId($organization)->one();
        }
        /* @var $organization Organization */
        if (empty($organization->memberLimitClass)) {
            return false;
        }
        $limit = static::find()->createdBy($organization)->one();
        /* @var $limit static */
        if (!$limit) {
            $limit = $organization->create(static::class);
            $limit->save();
        }
        return $limit->limit;
    }
}

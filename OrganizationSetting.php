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
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\user\User;
use Yii;
use yii\base\Event;
use yii\behaviors\BlameableBehavior;

/**
 * Class OrganizationSetting
 *
 * @property string $item
 * @property mixed $value
 * @property-read User $operator The user who last modified this setting.
 * Note: the return value may be null, please note that the case of invalid user.
 *
 * @package rhosocial\organization
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationSetting extends BaseBlameableModel
{
    use OperatorTrait;
    /**
     * @var string Host class.
     * You must assign with your own [[Organization]] class.
     */
    public $hostClass = Organization::class;

    public $idAttribute = 'item';
    public $idPreassigned = true;
    public $createdByAttribute = 'organization_guid';
    public $updatedByAttribute = false;
    public $enableIP = false;

    /**
     * @var string
     */
    public $contentAttribute = 'value';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = array_merge($behaviors, $this->getOperatorBehaviors());
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array_merge(parent::rules(), [
            [$this->idAttribute, 'string', 'max' => 255],
        ]);
        $rules = array_merge($rules, $this->getOperatorRules());
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getContentRules()
    {
        return [
            array_merge([$this->contentAttribute], $this->contentAttributeRule),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return  array_merge([
            $this->guidAttribute => Yii::t('user','GUID'),
            $this->createdByAttribute => Yii::t('organization', 'Organization GUID'),
            $this->idAttribute => 'Item',
            $this->contentAttribute => 'Value',
            $this->createdAtAttribute => Yii::t('user', 'Creation Time'),
            $this->updatedAtAttribute => Yii::t('user', 'Last Updated Time'),
        ], $this->getOperatorLabels());
    }
}

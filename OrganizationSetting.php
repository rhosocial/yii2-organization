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
    public $operatorAttribute = 'operator_guid';

    /**
     * @var string
     */
    public $contentAttribute = 'value';

    /**
     * Get operator query.
     * If you want to get operator, please access [[$operator]] magic-property.
     * @return BaseUserQuery
     */
    public function getOperator()
    {
        if (empty($this->operatorAttribute) || !is_string($this->operatorAttribute)) {
            return null;
        }
        $userClass = Yii::$app->user->identityClass;
        $noInit = $userClass::buildNoInitModel();
        /* @var $noInit User */
        return $this->hasOne($userClass, [$noInit->guidAttribute => $this->operatorAttribute]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_setting}}';
    }

    /**
     * @param Event $event
     * @return null|string
     */
    public function onAssignOperator($event)
    {
        $identity = Yii::$app->user->identity;
        if (empty($identity)) {
            return null;
        }
        return $identity->getGUID();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if (!empty($this->operatorAttribute) && is_string($this->operatorAttribute)) {
            $behaviors[] = [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => false,
                'updatedByAttribute' => $this->operatorAttribute,
                'value' => [$this, 'onAssignOperator'],
            ];
        }
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [$this->idAttribute, 'string', 'max' => 255],
            [$this->operatorAttribute, 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            $this->guidAttribute => Yii::t('user','GUID'),
            $this->createdByAttribute => Yii::t('organization', 'Organization GUID'),
            $this->idAttribute => 'Item',
            $this->contentAttribute => 'Value',
            $this->operatorAttribute => Yii::t('organization', 'Operator'),
            $this->createdAtAttribute => Yii::t('user', 'Creation Time'),
            $this->updatedAtAttribute => Yii::t('user', 'Last Updated Time'),
        ];
    }
}

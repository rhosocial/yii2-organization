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

use rhosocial\base\models\queries\BaseUserQuery;
use Yii;
use yii\base\Event;
use yii\behaviors\BlameableBehavior;

/**
 * OperatorTrait
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
Trait OperatorTrait
{
    /**
     * @var string
     */
    public $operatorAttribute = 'operator_guid';

    /**
     * Get operator query.
     * If you want to get operator, please access [[$operator]] magic-property.
     * Note: It may return null value! Please check whether the return value is available before accessing.
     * @return BaseUserQuery
     */
    public function getOperator()
    {
        if (empty($this->operatorAttribute) || !is_string($this->operatorAttribute)) {
            return null;
        }
        $userClass = Yii::$app->user->identityClass;
        $noInit = $userClass::buildNoInitModel();
        return $this->hasOne($userClass, [$noInit->guidAttribute => $this->operatorAttribute]);
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
     * @return array
     */
    public function getOperatorBehaviors()
    {
        if (!empty($this->operatorAttribute) && is_string($this->operatorAttribute)) {
            $behaviors[] = [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => false,
                'updatedByAttribute' => $this->operatorAttribute,
                'value' => [$this, 'onAssignOperator'],
            ];
            return $behaviors;
        }
        return [];
    }

    /**
     * @return array
     */
    public function getOperatorRules()
    {
        return [
            [$this->operatorAttribute, 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function getOperatorLabels()
    {
        return [
            $this->operatorAttribute => Yii::t('organization', 'Operator GUID'),
            'operator' => Yii::t('organization', 'Operator'),
        ];
    }
}

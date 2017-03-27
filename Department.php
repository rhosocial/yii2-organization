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

use yii\behaviors\AttributeBehavior;

/**
 * Department.
 * The department can open sub-departments.
 * A departments can not exist alone, only subordinate to an organization.
 * It can move from one department or organization to another department or
 * organization, but the subordinate relationship can not be a circle.
 *
 * Each department should have at least one member, and members have at least
 * one administrator, who can manage the daily affairs of the department, but can
 * not manage the sub-department or sub-organization's affairs, as he/she may not
 * be the administrator of sub-one.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Department extends BaseOrganization
{
    public $parentAttribute = 'parent';

    protected function typeAttributeBehavior()
    {
        return [
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'type',
                    self::EVENT_BEFORE_UPDATE => 'type',
                ],
                'value' => self::TYPE_DEPARTMENT,
            ]
        ];
    }

    /**
     * The default value of `type` attribute is `ORGANIZATION`(1).
     * @return array Rules associated with `type` attribute.
     */
    protected function getTypeRules()
    {
        return [
            ['type', 'default', 'value' => self::TYPE_DEPARTMENT],
            ['type', 'required'],
            ['type', 'in', 'range' => [self::TYPE_DEPARTMENT]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->andWhere(['type' => self::TYPE_DEPARTMENT]);
    }
}
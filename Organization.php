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
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Organization extends BaseOrganization
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
                'value' => self::TYPE_ORGANIZATION,
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
            ['type', 'default', 'value' => self::TYPE_ORGANIZATION],
            ['type', 'required'],
            ['type', 'in', 'range' => [self::TYPE_ORGANIZATION]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->andWhere(['type' => self::TYPE_ORGANIZATION]);
    }
}
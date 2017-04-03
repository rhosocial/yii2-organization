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

use Yii;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Profile extends \rhosocial\user\Profile
{
    public $hostClass = Organization::class;
    public $contentAttribute = 'name';
    public $descriptionAttribute = 'description';

    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('user', 'GUID'),
            'name' => Yii::t('organization', 'Name'),
            'gravatar_type' => Yii::t('user', 'Gravatar Type'),
            'gravatar' => Yii::t('user', 'Gravatar'),
            'timezone' => Yii::t('user', 'Timezone'),
            'description' => Yii::t('organization', 'Description'),
            'created_at' => Yii::t('user', 'Creation Time'),
            'updated_at' => Yii::t('user', 'Last Updated Time'),
        ];
    }

    public function getIndividualSignRules()
    {
        return [];
    }

    public function getGenderRules()
    {
        return [];
    }

    public function getNameRules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_profile}}';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = [
            $this->contentAttribute, 'gravatar_type', 'gravatar', 'timezone', $this->descriptionAttribute,
        ];
        return $scenarios;
    }
}

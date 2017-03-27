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

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Profile extends \rhosocial\user\Profile
{
    public $hostClass = Organization::class;

    public function attributeLabels()
    {
        return [
            'guid' => Yii::t('app', 'GUID'),
            'nickname' => Yii::t('app', 'Nickname'),
            'name' => Yii::t('app', 'Name'),
            'gravatar_type' => Yii::t('app', 'Gravatar Type'),
            'gravatar' => Yii::t('app', 'Gravatar'),
            'timezone' => Yii::t('app', 'Timezone'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Creation Time'),
            'updated_at' => Yii::t('app', 'Last Updated Time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%organization_profile}}';
    }
}

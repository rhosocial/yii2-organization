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

use rhosocial\organization\Member;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $member Member */

$user = $member->memberUser;
$profile = $user->profile;
$profileClass = get_class($profile);

echo DetailView::widget([
    'model' => $user,
    'attributes' => [
        'id',
        [
            'label' => Yii::t('user', 'Name'),
            'value' => $profile->last_name . $profile->first_name,
        ],
        [
            'label' => Yii::t('user', 'Gender'),
            'value' => $profileClass::getGenderDesc($profile->gender),
        ],
        [
            'label' => Yii::t('organization', 'Role'),
            'value' => function($model, $widget) use ($member) {
                if (empty($member->role)) {
                    return null;
                }
                $role = Yii::$app->authManager->getRole($member->role);
                if (empty($role)) {
                    return null;
                }
                return Yii::t('organization', $role->description);
            },
        ],
        'createdAt:datetime',
        [
            'label' => Yii::t('organization', 'Join Time'),
            'value' => $member->getCreatedAt(),
            'format' => 'datetime',
        ]
    ],
]);

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
use rhosocial\organization\Organization;
use rhosocial\organization\grid\MemberListActionColumn;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\web\View;

/* @var $organization Organization */
/* @var $dataProvider ActiveDataProvider */
/* @var $this View */
/* @var $tips boolean|array */
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'caption' => Yii::t('organization', 'Here are all members of the {organization}:', [
        'organization' => $organization->profile->name . (empty($organization->profile->nickname) ? '' : (' (' . $organization->profile->nickname . ')')),
    ]),
    'columns' => [
        ['class' => SerialColumn::class],
        'user_id' => [
            'class' => DataColumn::class,
            'attribute' => 'id',
            'label' => Yii::t('user', 'User ID'),
            'content' => function ($model, $key, $index, $column) {
                return $model->memberUser->getID();
            },
            'contentOptions' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                if ($model->memberUser->getID() != Yii::$app->user->identity->getID()) {
                    return [];
                }
                return ['bgcolor' => '#00FF00'];
            },
        ],
        'name' => [
            'class' => DataColumn::class,
            'attribute' => 'name',
            'label' => Yii::t('user', 'Name'),
            'content' => function ($model, $key, $index, $column) {
                if (!$model->memberUser || !$model->memberUser->profile) {
                    return null;
                }
                return $model->memberUser->profile->last_name . $model->memberUser->profile->first_name;
            }
        ],
        'position',
        'role' => [
            'class' => DataColumn::class,
            'attribute' => 'role',
            'label' => Yii::t('organization', 'Role'),
            'content' => function ($model, $key, $index, $column) {
                if (empty($model->role)) {
                    return null;
                }
                $role = Yii::$app->authManager->getRole($model->role);
                if (empty($role)) {
                    return null;
                }
                return Yii::t('organization', $role->description);
            },
        ],
        'created_at' => [
            'class' => DataColumn::class,
            'attribute' => 'created_at',
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                return $column->grid->formatter->format($model->getCreatedAt(), 'datetime');
            },
        ],
        'updated_at' => [
            'class' => DataColumn::class,
            'attribute' => 'updated_at',
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                return $column->grid->formatter->format($model->getUpdatedAt(), 'datetime');
            },
        ],
        'action' => [
            'class' => MemberListActionColumn::class,
        ],
    ],
    'tableOptions' => [
        'class' => 'table table-striped'
    ],
]);
echo $this->render('@rhosocial/organization/widgets/views/member-list-tips', ['tips' => $tips]);

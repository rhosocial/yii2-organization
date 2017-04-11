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

use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;
use yii\grid\SerialColumn;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;
/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
$this->title = Yii::t('organization', 'Member');
$this->params['breadcrumbs'][] = $this->title;
Pjax::begin([
    'id' => 'member-pjax',
]);
echo GridView::widget([
    'caption' => 'Here are all members of the organization / department:',
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => SerialColumn::class],
        'user_id' => [
            'class' => DataColumn::class,
            'label' => Yii::t('user', 'User ID'),
            'content' => function ($model, $key, $index, $column) {
                return $model->memberUser->getID();
            }
        ],
        'name' => [
            'class' => DataColumn::class,
            'label' => Yii::t('user', 'Name'),
            'content' => function ($model, $key, $index, $column) {
                if (!$model->memberUser || !$model->memberUser->profile) {
                    return null;
                }
                return $model->memberUser->profile->first_name . ' ' . $model->memberUser->profile->last_name;
            }
        ],
        'position',
        'role' => [
            'class' => DataColumn::class,
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
        'action' => [
            'class' => ActionColumn::class,

        ]
    ],
]);
Pjax::end();

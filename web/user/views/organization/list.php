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

use rhosocial\organization\Organization;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
$this->title = Yii::t('organization', 'List');
$this->params['breadcrumbs'][] = $this->title;
Pjax::begin([
    'id' => 'organization-pjax',
]);
echo empty($dataProvider) ? '' : GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'guid' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('user', 'GUID'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $model->getReadableGUID();
            },
        ],
        'id',
        'parent' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('organization', 'Parent'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                if ($model->type == Organization::TYPE_ORGANIZATION) {
                    return null;
                }
                $parent = $model->parent;
                return $parent ? $parent->getID() : null;
            },
        ],
        'children' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('organization', 'Parent'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $model->getChildren()->count();
            },
        ],
        'creator' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('organization', 'Creator'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                $creator = $model->creator;
                return ($creator) ? $creator->getID() : null;
            },
        ],
        'administrator' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('organization', 'Administrator'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $model->getAdministrators()->count();
            },
        ],
        'member' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('organization', 'Member'),
            'content' => function ($model, $key, $index, $column) {
                /* $var $model Organization */
                return $model->getMemberUsers()->count();
            },
        ],
        [
            'class' => ActionColumn::class,
            'header' => Yii::t('user', 'Action'),
            'urlCreator' => function (string $action, $model, $key, $index, ActionColumn $column) {
                /* @var $model Organization */
                if ($action == 'view') {
                    return Url::to(['view', 'id' => $model->getID()]);
                } elseif ($action == 'update') {
                    return Url::to(['update', 'id' => $model->getID()]);
                } elseif ($action == 'delete') {
                    return Url::to(['revoke', 'id' => $model->getID()]);
                }
                return '#';
            },
            'visibleButtons' => [
                'view' => true,
                'update' => function ($model, $key, $index) {
                    return Yii::$app->user->can('manageProfile', ['organization' => $model]);
                },
                'delete' => function ($model, $key, $index) {
                    $permission = ($model->type == Organization::TYPE_ORGANIZATION) ? 'revokeOrganization' : 'revokeDepartment';
                    return Yii::$app->user->can($permission, ['organization' => $model]);
                },
            ],
        ],
    ],
]);
Pjax::end();
?>
<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Set Up New Organization'), ['set-up-organization'], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

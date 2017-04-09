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

use rhosocial\user\User;
use rhosocial\organization\Organization;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $user User */
/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $orgOnly boolean*/
$this->title = Yii::t('organization', 'List');
$this->params['breadcrumbs'][] = $this->title;
Pjax::begin([
    'id' => 'organization-pjax',
]);
echo GridView::widget([
    'caption' => Yii::t('organization', $orgOnly ? "Here are all the organizations you have joined:" : "Here are all the organizations / departments you have joined:"),
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        /* The GUID is not displayed by default.
        'guid' => [
            'class' => 'yii\grid\DataColumn',
            'header' => Yii::t('user', 'GUID'),
            'content' => function ($model, $key, $index, $column) {
                return $model->getReadableGUID();
            },
        ],*/
        'type' => [
            'class' => DataColumn::class,
            'label' => Yii::t('user', 'Type'),
            'content' => function ($model, $key, $index, $column) {
                return Yii::t('organization', $model->isOrganization() ? 'Organization' : 'Department');
            },
            'visible' => !$orgOnly,
        ],
        'id',
        'parent' => [
            'class' => DataColumn::class,
            'attribute' => 'parent',
            'label' => Yii::t('organization', 'Parent ID'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                if ($model->isOrganization()) {
                    return null;
                }
                $parent = $model->parent;
                return $parent ? $parent->getID() : null;
            },
        ],
        'children' => [
            'class' => DataColumn::class,
            'label' => Yii::t('organization', 'Number of Children'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $model->getChildren()->count();
            },
        ],
        'creator' => [
            'class' => DataColumn::class,
            'header' => Yii::t('organization', 'Creator'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                $creator = $model->creator;
                return ($creator) ? $creator->getID() : null;
            },
            'contentOptions' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                $creator = $model->creator;
                if (!$creator || $creator->getID() != Yii::$app->user->identity->getID()) {
                    return [];
                }
                return ['bgcolor' => '#00FF00'];
            },
        ],
        'administrator' => [
            'class' => DataColumn::class,
            'header' => Yii::t('organization', 'Number of Administrators'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $model->getAdministrators()->count();
            },
        ],
        'member' => [
            'class' => DataColumn::class,
            'header' => Yii::t('organization', 'Number of Members'),
            'content' => function ($model, $key, $index, $column) {
                /* $var $model Organization */
                return $model->getMemberUsers()->count();
            },
        ],
        'createdAt' => [
            'class' => DataColumn::class,
            'attribute' => 'createdAt',
            'label' => Yii::t('user', 'Creation Time'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $column->grid->formatter->format($model->getCreatedAt(), 'datetime');
            },
        ],
        'updatedAt' => [
            'class' => DataColumn::class,
            'attribute' => 'updatedAt',
            'label' => Yii::t('user', 'Last Updated Time'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Organization */
                return $column->grid->formatter->format($model->getUpdatedAt(), 'datetime');
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
                    $permission = ($model->isOrganization()) ? 'revokeOrganization' : 'revokeDepartment';
                    return Yii::$app->user->can($permission, ['organization' => $model]);
                },
            ],
        ],
    ],
]);
Pjax::end();
?>
<div class="well well-sm">
    <?= Yii::t('user', 'Directions:') ?>
    <ol>
        <li><?= Yii::t('organization', 'If no search criteria are specified, all organizations are displayed.') ?></li>
        <li><?= Yii::t('organization', 'When the creator column is green, it indicates that the user is the current logged-in user.') ?></li>
        <li><?= Yii::t('user', 'If the creation time is the same as the last update time, there is no change.') ?></li>
        <li><?= Yii::t('organization', 'If you do not see the "Set Up Organization" button, it means that the current login user does not have permission to set up a new organization, or the number of organizations has reached the maximum.') ?></li>
    </ol>
</div>
<div class="row">
    <?php if (Yii::$app->authManager->checkAccess($user, (new SetUpOrganization)->name)) :?>
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Set Up New Organization'), ['set-up-organization'], ['class' => 'btn btn-primary']) ?>
    </div>
    <?php endif; ?>
</div>

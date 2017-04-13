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

use rhosocial\organization\grid\OrganizationListActionColumn;
use rhosocial\organization\Organization;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\web\View;

/* @var $dataProvider ActiveDataProvider */
/* @var $this View */
/* @var $showGUID boolean */
/* @var $showType boolean */
/* @var $additionalColumns array */
/* @var $actionColumn array */
/* @var $tips boolean|array */
$columns = [
    ['class' => SerialColumn::class],
    'guid' => [
        'class' => DataColumn::class,
        'label' => Yii::t('user', 'GUID'),
        'content' => function ($model, $key, $index, $column) {
            return $model->getReadableGUID();
        },
        'visible' => $showGUID,
    ],
    'type' => [
        'class' => DataColumn::class,
        'attribute' => 'type',
        'label' => Yii::t('user', 'Type'),
        'content' => function ($model, $key, $index, $column) {
            return Yii::t('organization', $model->isOrganization() ? 'Organization' : 'Department');
        },
        'visible' => $showType,
    ],
    'id',
    'name' => [
        'class' => DataColumn::class,
        'attribute' => 'name',
        'label' => Yii::t('organization', 'Name'),
        'content' => function ($model, $key, $index, $column) {
            if (!$model->profile) {
                return null;
            }
            return $model->profile->name;
        },
    ],
    'nickname' => [
        'class' => DataColumn::class,
        'attribute' => 'nickname',
        'label' => Yii::t('user', 'Nickname'),
        'content' => function ($model, $key, $index, $column) {
            if (!$model->profile) {
                return null;
            }
            return $model->profile->nickname;
        },
    ],
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
    'created_at' => [
        'class' => DataColumn::class,
        'attribute' => 'created_at',
        'content' => function ($model, $key, $index, $column) {
            /* @var $model Organization */
            return $column->grid->formatter->format($model->getCreatedAt(), 'datetime');
        },
    ],
    'updated_at' => [
        'class' => DataColumn::class,
        'attribute' => 'updated_at',
        'content' => function ($model, $key, $index, $column) {
            /* @var $model Organization */
            return $column->grid->formatter->format($model->getUpdatedAt(), 'datetime');
        },
    ],
];
if (!empty($additionalColumns) && is_array($additionalColumns)) {
    $columns = array_merge($columns, $additionalColumns);
}
if (!empty($actionColumn)) {
    $columns[] = $actionColumn;
}
echo GridView::widget([
    'caption' => Yii::t('organization', "Here are all the organizations / departments you have joined:"),
    'dataProvider' => $dataProvider,
    'layout' => "{summary}\n<div class=\"table-responsive\">{items}</div>\n{pager}",
    'columns' => $columns,
    'tableOptions' => [
        'class' => 'table table-striped'
    ],
]);
?>
<?php if ($tips): ?>
<div class="well well-sm">
    <?= Yii::t('organization', 'Organization List Directions:') ?>
    <ol>
        <li><?= Yii::t('organization', 'If no search criteria are specified, all organizations and departments are displayed.') ?></li>
        <li><?= Yii::t('organization', 'When the creator column is green, it indicates that the user is the current logged-in user.') ?></li>
        <li><?= Yii::t('user', 'If the creation time is the same as the last update time, there is no change.') ?></li>
        <li><?= Yii::t('organization', 'If you can not see the "Set Up Organization" button, it means that the current login user does not have permission to set up a new organization, or the number of organizations has reached the maximum.') ?></li>
        <li><?= Yii::t('organization', 'If you can not see the "Set Up New Department" button, it means that the current login user does not have permission to set up a new department, or the number of departments has reached the maximum.') ?></li>
        <?php if (is_array($tips)): ?>
            <?php foreach ($tips as $tip): ?>
                <li><?= $tip ?></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ol>
</div>
<?php endif; ?>

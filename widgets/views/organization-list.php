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
/* @var $orgOnly boolean */
/* @var $showGUID boolean */
/* @var $additionalColumns array */
/* @var $actionColumn array */
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
];
if (!empty($additionalColumns) && is_array($additionalColumns)) {
    $columns = array_merge($columns, $additionalColumns);
}
if (!empty($actionColumn)) {
    $columns[] = $actionColumn;
}
echo GridView::widget([
    'caption' => Yii::t('organization', $orgOnly ? "Here are all the organizations you have joined:" : "Here are all the organizations / departments you have joined:"),
    'dataProvider' => $dataProvider,
    'layout' => "{summary}\n<div class=\"table-responsive\">{items}</div>\n{pager}",
    'columns' => $columns,
    'tableOptions' => [
        'class' => 'table table-striped'
    ]
]);

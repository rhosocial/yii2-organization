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
use rhosocial\organization\Member;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
/* @var $this View */
/* @var $user User */
/* @var $organization Organization */
/* @var $dataProvider ActiveDataProvider */
$this->title = Yii::t('organization', 'View Members');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
Pjax::begin([
    'id' => 'organization-pjax',
]);
echo empty($dataProvider) ? '' : GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'id' => [
            'class' => DataColumn::class,
            'header' => Yii::t('organization', 'ID'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                return $model->memberUser->getID();
            },
        ],
        'role' => [
            'class' => DataColumn::class,
            'header' => Yii::t('organization', 'Role'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                if (empty($model->role)) {
                    return '';
                }
                $role = Yii::$app->authManager->getRole($model->role);
                if (empty($role)) {
                    return '';
                }
                return Yii::t('organization', $role->description);
            },
        ],
        'createdAt' => [
            'class' => DataColumn::class,
            'header' => Yii::t('organization', 'Join Time'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                return $column->grid->formatter->format($model->getCreatedAt(), 'datetime');
            },
        ],
        'updatedAt' => [
            'class' => DataColumn::class,
            'header' => Yii::t('user', 'Last Updated Time'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                return $column->grid->formatter->format($model->getUpdatedAt(), 'datetime');
            },
        ],
    ],
]);
Pjax::end();
?>

<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Back to List'), [
            'list',
        ], ['class' => 'btn btn-primary']) ?>
<?php if ($user->isOrganizationAdministrator($organization) || $user->isOrganizationCreator($organization)): ?>
        <?= Html::a(Yii::t('organization', 'Add New Member'), [
            'add-new-member',
            'organization' => $organization->getID()
        ], ['class' => 'btn btn-primary']) ?>
<?php endif; ?>
    </div>
</div>

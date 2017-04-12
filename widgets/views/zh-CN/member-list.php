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

use rhosocial\organization\grid\MemberListActionColumn;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\web\View;

/* @var $dataProvider ActiveDataProvider */
/* @var $this View */
/* @var $tips boolean|array */
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'caption' => 'Here are all members of the organization / department:',
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
                return $model->memberUser->profile->last_name . $model->memberUser->profile->first_name;
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
        'createdAt' => [
            'class' => DataColumn::class,
            'attribute' => 'createdAt',
            'label' => Yii::t('user', 'Creation Time'),
            'content' => function ($model, $key, $index, $column) {
                /* @var $model Member */
                return $column->grid->formatter->format($model->getCreatedAt(), 'datetime');
            },
        ],
        'updatedAt' => [
            'class' => DataColumn::class,
            'attribute' => 'updatedAt',
            'label' => Yii::t('user', 'Last Updated Time'),
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
?>
<?php if ($tips): ?>
    <div class="well well-sm">
        <?= Yii::t('user', 'Directions:') ?>
        <ol>
            <li><?= Yii::t('organization', 'If no search criteria are specified, all members are displayed.') ?></li>
            <li><?= Yii::t('organization', 'When the User ID column is green, it indicates that the user is the current logged-in user.') ?></li>
            <li><?= Yii::t('user', 'If the creation time is the same as the last update time, there is no change.') ?></li>
            <li><?= Yii::t('organization', 'If you can not see the "Update" or "Remove Member" button, it means that the you do not have corresponding permission.') ?></li>
            <?php if (is_array($tips)): ?>
                <?php foreach ($tips as $tip): ?>
                    <li><?= $tip ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ol>
    </div>
<?php endif; ?>

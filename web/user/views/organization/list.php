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
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
$this->title = Yii::t('organization', 'Organization');
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
            'content' => function ($mode, $key, $index, $column) {
                /* $var $model Organization */
                return $model->getMemberUsers()->count();
            },
        ]
    ],
]);
Pjax::end();
?>
<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Set Up New Organization'), ['set-up-organization'], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

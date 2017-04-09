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
        ['class' => 'yii\grid\SerialColumn'],
        'id',
    ],
]);
Pjax::end();

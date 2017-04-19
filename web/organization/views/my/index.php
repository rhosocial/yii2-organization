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
use rhosocial\organization\OrganizationSearch;
use rhosocial\organization\widgets\OrganizationListWidget;
use rhosocial\organization\widgets\OrganizationSearchWidget;
use rhosocial\organization\widgets\SetUpButtonWidget;
use yii\data\ActiveDataProvider;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $user User */
/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $searchModel OrganizationSearch */
$this->title = Yii::t('organization', 'The organizations / departments I have joined in');
$this->params['breadcrumbs'][] = $this->title;
$formId = 'organization-search-form';
echo OrganizationSearchWidget::widget([
    'formId' => $formId,
    'model' => $searchModel,
]);
Pjax::begin([
    'id' => 'organization-pjax',
    'formSelector' => "#$formId",
]);
echo OrganizationListWidget::widget([
    'dataProvider' => $dataProvider,
    'showType' => empty($searchModel->type),
    'actionColumn' => OrganizationListWidget::ACTION_COLUMN_DEFAULT,
]);
Pjax::end();
?>
<div class="row">
    <div class="col-md-3 col-sm-6">
        <?= SetUpButtonWidget::widget(['operator' => $user]) ?>
    </div>
</div>

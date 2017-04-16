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
use rhosocial\organization\grid\AddMemberActionColumn;
use rhosocial\user\widgets\UserProfileSearchWidget;
use rhosocial\user\widgets\UserListWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $searchModel UserProfileView */
/* @var $dataProvider ActiveDataProvider */
/* @var $organization Organization */
$this->title = Yii::t('organization', 'Add member');
$this->params['breadcrumbs'][] = $this->title;
$formId = 'user-search-form';
echo UserProfileSearchWidget::widget([
    'model' => $searchModel,
    'formId' => $formId,
    'formConfig' => [
        'id' => $formId,
        'action' => ['add-member', 'org' => $organization->getID()],
        'method' => 'get',
    ],
]);
Pjax::begin([
    'id' => 'user-pjax',
    'formSelector' => "#$formId",
]);
echo UserListWidget::widget([
    'dataProvider' => $dataProvider,
    'actionColumn' => [
        'class' => AddMemberActionColumn::class,
        'organization' => $organization,
        'addConfirm' => true,
    ],
    'tips' => [
        Yii::t('organization', 'If you can not see the "Add" button, it means that the user is already a member of the current organization / department.')
    ],
]);
Pjax::end();
?>
<div class="row">
    <div class="col-md-12">
        <?= Html::a(Yii::t('organization', 'Back to List'), ['index'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('organization', 'Member List'), ['member', 'org' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

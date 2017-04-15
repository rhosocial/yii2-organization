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

use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\organization\widgets\MemberListWidget;
use rhosocial\organization\widgets\MemberSearchWidget;
use rhosocial\organization\MemberSearch;
use rhosocial\organization\Organization;
use rhosocial\user\User;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;
/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $organization Organization */
/* @var $searchModel MemberSearch */
/* @var $user User */
$this->title = Yii::t('organization', 'Member');
$this->params['breadcrumbs'][] = $this->title;
$formId = 'member-search-form';
echo MemberSearchWidget::widget([
    'formId' => $formId,
    'organization' => $organization,
    'model' => $searchModel,
]);
Pjax::begin([
    'id' => 'member-pjax',
    'formSelector' => "#$formId",
]);
echo MemberListWidget::widget([
    'organization' => $organization,
    'dataProvider' => $dataProvider,
]);
Pjax::end();
?>
<div class="row">
        <div class="col-md-12">
            <?= Html::a(Yii::t('organization', 'Back to List'), ['index'], ['class' => 'btn btn-primary']) ?>
            <?php if (Yii::$app->authManager->checkAccess($user, (new ManageMember)->name, ['organization' => $organization]) && !$organization->hasReachedMemberLimit()) :?>
            <?= Html::a(Yii::t('organization', 'Add member'), ['add-member', 'org' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
</div>

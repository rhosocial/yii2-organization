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

use rhosocial\organization\rbac\permissions\ManageProfile;
use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\organization\rbac\permissions\SetUpDepartment;
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
$name = ($organization->profile) ? $organization->profile->name : null;
$this->title = (empty($name) ? '' : "$name ({$organization->getID()}) ") . Yii::t('organization', 'Member');
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
<h3><?= Yii::t('user', 'Other operations') ?></h3>
<hr>
<div class="row">
        <div class="col-md-12">
            <?= Html::a(Yii::t('organization', 'Back to Organization List'), ['index'], ['class' => 'btn btn-primary']) ?>
            <?php if (Yii::$app->authManager->checkAccess($user, (new ManageMember)->name, ['organization' => $organization]) && !$organization->hasReachedMemberLimit()) :?>
            <?= Html::a(Yii::t('organization', 'Add member'), ['add-member', 'org' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
            <?php if (Yii::$app->authManager->checkAccess($user, (new ManageProfile)->name, ['organization' => $organization])) : ?>
                <?= Html::a(Yii::t('organization', 'Update Profile'), ['update', 'id' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
            <?php if (Yii::$app->authManager->checkAccess($user, (new SetUpDepartment)->name, ['organization' => $organization])) : ?>
                <?= Html::a(Yii::t('organization', 'Set Up New Department'), ['set-up-department', 'parent' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
</div>

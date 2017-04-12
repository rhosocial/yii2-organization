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
use rhosocial\organization\grid\OrganizationListActionColumn;
use rhosocial\organization\widgets\OrganizationListWidget;
use rhosocial\organization\Organization;
use rhosocial\organization\rbac\permissions\SetUpOrganization;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

/* @var $user User */
/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $orgOnly boolean*/
$this->title = Yii::t('organization', 'Organization List');
$this->params['breadcrumbs'][] = $this->title;
Pjax::begin([
    'id' => 'organization-pjax',
]);
echo OrganizationListWidget::widget([
    'dataProvider' => $dataProvider,
    'orgOnly' => $orgOnly,
    'actionColumn' => OrganizationListWidget::ACTION_COLUMN_DEFAULT,
]);
Pjax::end();
?>
<div class="well well-sm">
    <?= Yii::t('user', 'Directions:') ?>
    <ol>
        <li><?= Yii::t('organization', 'If no search criteria are specified, all organizations are displayed.') ?></li>
        <li><?= Yii::t('organization', 'When the creator column is green, it indicates that the user is the current logged-in user.') ?></li>
        <li><?= Yii::t('user', 'If the creation time is the same as the last update time, there is no change.') ?></li>
        <li><?= Yii::t('organization', 'If you can not see the "Set Up Organization" button, it means that the current login user does not have permission to set up a new organization, or the number of organizations has reached the maximum.') ?></li>
    </ol>
</div>
<div class="row">
    <?php if (Yii::$app->authManager->checkAccess($user, (new SetUpOrganization)->name)) :?>
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Set Up New Organization'), ['set-up-organization'], ['class' => 'btn btn-primary']) ?>
    </div>
    <?php endif; ?>
</div>

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

use rhosocial\organization\forms\JoinOrganizationForm;
use rhosocial\organization\web\organization\Module;
use rhosocial\organization\widgets\JoinOrganizationFormWidget;
use rhosocial\organization\Organization;
use yii\widgets\DetailView;
use yii\web\View;

/* @var $this View */
/* @var $model JoinOrganizationForm */
$organization = $model->organization;
/* @var $organization Organization */

$this->title = Yii::t('organization', 'Join {name}', ['name' => $organization->profile->name]);
$this->params['breadcrumbs'][] = $this->title;
?>

<?= DetailView::widget([
    'model' => $organization,
    'attributes' => [
        'id',
        'created_at:datetime',
    ],
]) ?>
<p>
    <?= $organization->profile->description ?>
</p>

<?php if (Yii::$app->user->isGuest): ?>
    <p><?= Yii::t('organization', 'You are not logged in yet.') ?></p>
<?php else: ?>
    <?php // Only validate the current login user. ?>
    <?php $user = Yii::$app->user->identity ?>

    <?php if ($organization->creator->equals($user)) : ?>
        <p><?= // Creator can not join.
            Yii::t('organization', 'The user is already a creator.')
            ?></p>
    <?php elseif ($organization->hasMember($user)) : ?>
        <p><?= // Joined user, can be considered to want to withdraw.
            Yii::t('organization', 'You are already a member of the ' . ($organization->isOrganization() ? 'organization' : 'department') . '.')
            ?></p>
        <?php if ($organization->exitAllowWithdrawActively) : ?>
            <?= JoinOrganizationFormWidget::widget(['model' => $model, 'join' => false]) ?>
        <?php else: ?>
            <?= $organization->isOrganization() ? Yii::t('organization', 'According to the organization\'s setting, you cannot withdraw from this organization proactively.') : Yii::t('organization', 'According to the department\'s setting, you cannot withdraw from this department proactively.') ?>
        <?php endif;?>
    <?php else:
        // Unjoined user, can be considered to want to join.
        ?>
        <?php if (Module::validateIPRanges($organization, Yii::$app->request->userIP, $errors)) : ?>
            <p><?= Yii::t('organization', 'Would you like to join the ' . ($organization->isOrganization() ? 'organization' : 'department') . '?') ?></p>
            <?= JoinOrganizationFormWidget::widget(['model' => $model]) ?>
        <?php else: ?>
            <p><?= Yii::t('organization', 'Your current IP address is not allowed.') ?></p>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

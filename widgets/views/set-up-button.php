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

use rhosocial\organization\rbac\permissions\SetUpOrganization;
use rhosocial\user\User;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $operator User */
/* @var $url array|string */
/* @var $options array */
?>
<div class="thumbnail">
    <div class="caption">
        <h3><?= Yii::t('organization', 'Set Up New Organization') ?></h3>
        <?php if (Yii::$app->authManager->checkAccess($operator->getGUID(), (new SetUpOrganization)->name)) : ?>
            <?php $remaining = $operator->getRemainingOrganizationPlaces() ?>
            <?php if ($remaining === false): ?>
                <p><?= Yii::t('organization', 'There is no limit to setting up organization.') ?></p>
            <?php else: ?>
                <?php $limit = $remaining + (int)$operator->getCreatorsAtOrganizationsOnly()->count() ?>
                <p><?= Yii::t('organization', 'You can open up to {limit,plural,=1{only one organization} other{# organizations}}.', ['limit' => $limit]) ?></p>
                <?php if ($remaining < 0): ?>
                    <p><?= Yii::t('organization', 'You can not set up new organization.') ?></p>
                <?php else: ?>
                    <p><?= Yii::t('organization', 'There {remaining,plural,=0{is no places} =1{is only one place} other{are # places}} left.', ['remaining' => $remaining]) ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <p><?= Html::a(Yii::t('organization', 'Set Up New Organization'), $url, $options) ?></p>
        <?php else: ?>
            <p><?= Yii::t('organization', 'You do not have access to set up new organization.') ?></p>
        <?php endif; ?>
    </div>
</div>

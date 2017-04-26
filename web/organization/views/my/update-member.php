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

use rhosocial\organization\Member;
use rhosocial\organization\widgets\MemberFormWidget;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Member */
$name = ($model->organization->profile) ? $model->organization->profile->name : null;
$this->title = empty($name) ? '' : "$name ({$model->organization->getID()}) " . Yii::t('organization', 'Update Member');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-lg-6">
        <?= $this->render('_member_profile', ['member' => $model]) ?>
        <?= MemberFormWidget::widget(['member' => $model]); ?>
    </div>
    <div class="col-lg-6">
        <div class="thumbnail">
            <div class="caption">
                <h3><?= Yii::t('organization', 'Administrator') ?></h3>
                <?php if ($model->organization->getMember(Yii::$app->user->identity)->isCreator()) : ?>
                    <?php if ($model->isCreator()): ?>
                        <p><?= Yii::t('organization', 'The user is already a creator.') ?></p>
                    <?php elseif ($model->isAdministrator()): ?>
                        <p><?= Yii::t('organization', 'Revoke the current user administrator role:') ?></p>
                        <p>
                            <?=
                            Html::beginForm([
                                'assign-admin',
                                'org' => $model->organization->getID(),
                                'user' => $model->memberUser->getID(),
                                'revoke' => '1',
                            ], 'post')
                            . Html::submitButton(
                                Yii::t('organization', 'Revoke Administrator'),
                                ['class' => 'btn btn-primary', 'role' => 'button']
                            )
                            . Html::endForm()
                            ?>
                        </p>
                    <?php else: ?>
                        <p><?= Yii::t('organization', 'Give the current user an administrator role:') ?></p>
                        <p>
                            <?=
                            Html::beginForm([
                                'assign-admin',
                                'org' => $model->organization->getID(),
                                'user' => $model->memberUser->getID(),
                            ], 'post')
                            . Html::submitButton(
                                Yii::t('organization', 'Assign Administrator'),
                                ['class' => 'btn btn-primary', 'role' => 'button']
                            )
                            . Html::endForm()
                            ?>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($model->isCreator()) : ?>
                        <p><?= Yii::t('organization', 'The user is already a creator.') ?></p>
                    <?php elseif ($model->isAdministrator()) : ?>
                        <p><?= Yii::t('organization', 'The user is already an administrator.') ?></p>
                    <?php else: ?>
                        <p><?= Yii::t('organization', 'The user is not administrator yet.') ?></p>
                    <?php endif; ?>
                <?php endif ;?>
            </div>
        </div>
    </div>
</div>
<h3><?= Yii::t('user', 'Other operations') ?></h3>
<hr>
<div class="row">
    <div class="col-md-12">
        <?= Html::a(Yii::t('organization', 'Back to Organization List'), [
            'index',
        ], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('organization', 'Member List'), ['member', 'org' => $model->organization->getID()], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

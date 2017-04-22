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

use rhosocial\organization\forms\SettingsForm;
use rhosocial\organization\Organization;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model SettingsForm */
/* @var $organization Organization */
$profile = $organization->profile;
$name = '';
if ($profile) {
    $name = $profile->name;
} else {
    $name = Yii::t('yii', 'not set');
}
?>
<h2><?= $name ?></h2>
<?php $form = ActiveForm::begin([
    'id' => 'organization-settings-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-md-4 col-lg-2',
            'offset' => '',
            'input' => 'col-md-6',
            'wrapper' => 'col-md-6 col-lg-8',
            'error' => 'col-md-12',
            'hint' => 'col-md-12',
        ],
    ],
]) ?>
<?php
$horizontalCheckboxTemplate = "<div class=\"col-lg-2 col-md-4\"></div>\n" .
    "{beginWrapper}\n{input}\n{label}\n" .
    "{hint}\n{error}\n" .
    "{endWrapper}"
?>
<h3><?= Yii::t('organization', 'General') ?></h3>
<hr>
<?php if ($model->scenario == SettingsForm::SCENARIO_ORGANIZATION): ?>
    <?= $form->field($model, 'exclude_other_members', [
            'horizontalCheckboxTemplate' => $horizontalCheckboxTemplate,
    ])->checkbox() ?>
    <?= $form->field($model, 'disallow_member_join_other', [
        'horizontalCheckboxTemplate' => $horizontalCheckboxTemplate,
    ])->checkbox() ?>
<?php elseif ($model->scenario == SettingsForm::SCENARIO_DEPARTMENT): ?>
    <?= $form->field($model, 'only_accept_current_org_member', [
        'horizontalCheckboxTemplate' => $horizontalCheckboxTemplate,
    ])->checkbox() ?>
    <?php if (!$model->organization->parent->equals($model->organization->topOrganization)) : ?>
        <?= $form->field($model, 'only_accept_superior_org_member', [
            'horizontalCheckboxTemplate' => $horizontalCheckboxTemplate,
        ])->checkbox() ?>
    <?php endif; ?>
<?php endif; ?>

<h3><?= Yii::t('organization', 'Join') ?></h3>
<hr>
<p><?= Yii::t('organization', 'The following conditions need to be fully met, if not limit a condition, please leave it blank.') ?></p>
<?= $form->field($model, 'join_entrance_url')->textInput() ?>
<?= $form->field($model, 'join_password')->textInput() ?>
<?= $form->field($model, 'join_ip_address')->textInput() ?>

<div class="form-group">
    <div class="col-md-offset-4 col-md-8 col-lg-offset-2 col-lg-10">
        <?= Html::submitButton(Yii::t('organization', 'Submit'), ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
        <?= Html::resetButton(Yii::t('user', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>
</div>

<?php ActiveForm::end() ?>

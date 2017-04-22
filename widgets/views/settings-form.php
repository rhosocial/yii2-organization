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
<p><?= $name ?></p>
<?php $form = ActiveForm::begin([
    'id' => 'organization-settings-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-lg-6',
            'offset' => '',
            'input' => 'col-lg-6',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => 'col-lg-12',
        ],
    ],
]) ?>
<div class="thumbnail">
    <?php if ($model->scenario == SettingsForm::SCENARIO_ORGANIZATION): ?>
        <p><?= $form->field($model, 'exclude_other_members')->checkbox() ?></p>
        <p><?= $form->field($model, 'disallow_member_join_other')->checkbox() ?></p>
    <?php elseif ($model->scenario == SettingsForm::SCENARIO_DEPARTMENT): ?>
        <p><?= $form->field($model, 'only_accept_current_org_member')->checkbox() ?></p>
        <p><?= $form->field($model, 'only_accept_superior_org_member')->checkbox() ?></p>
    <?php endif; ?>
</div>

<div class="form-group">
    <div class="col-lg-12">
        <?= Html::submitButton(Yii::t('organization', 'Submit'), ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
    </div>
</div>

<?php ActiveForm::end() ?>

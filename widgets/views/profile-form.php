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

use rhosocial\base\helpers\Timezone;
use rhosocial\organization\Profile;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $model Profile */
?>
<div class="site-login">
    <p><?= Yii::t('user', 'Please fill out the following fields to update profile:') ?></p>

    <?php $form = ActiveForm::begin([
        'id' => 'profile-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'gravatar_type')->textInput() ?>

        <?= $form->field($model, 'gravatar')->textInput() ?>

        <?= $form->field($model, 'timezone')->dropDownList(Timezone::generateList()) ?>

        <?= $form->field($model, 'description')->textarea() ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-primary', 'name' => 'update-button']) ?>
                <?= Html::resetButton(Yii::t('user', 'Reset'), ['class' => 'btn btn-default', 'name' => 'reset-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
</div>

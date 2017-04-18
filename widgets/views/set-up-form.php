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
use rhosocial\organization\forms\SetUpForm;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
/* @var $model SetUpForm */
?>
<div class="site-login">
    <p><?= Yii::t('organization', 'Please fill out the following fields to set up ' . ($model->getParent() ? 'department' : 'organization') . ':') ?></p>

    <?php $form = ActiveForm::begin([
        'id' => 'set-up-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'nickname')->textInput() ?>

        <?= $form->field($model, 'timezone')->dropDownList(Timezone::generateList()) ?>

        <?= $form->field($model, 'description')->textarea() ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton(Yii::t('organization', 'Set up'), ['class' => 'btn btn-primary', 'name' => 'set-up-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
    
</div>

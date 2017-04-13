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
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Member */
?>
<div class="site-login">
    <p><?= Yii::t('organization', 'Please fill out the following fields to update member:') ?></p>

    <?php $form = ActiveForm::begin([
        'id' => '-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, $model->contentAttribute)->textInput(['autofocus' => true]) ?>

    <?= $form->field($model, 'position')->textInput() ?>

    <?= $form->field($model, $model->descriptionAttribute)->textarea() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-primary', 'name' => 'update-button']) ?>
            <?= Html::resetButton(Yii::t('user', 'Reset'), ['class' => 'btn btn-default', 'name' => 'reset-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

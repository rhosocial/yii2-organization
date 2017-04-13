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

use kartik\datetime\DateTimePicker;
use rhosocial\organization\OrganizationSearch;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $formId string */
/* @var $formConfig array */
/* @var $model OrganizationSearch */
/* @var $this View */
$css = <<<EOT
div.required label.control-label:after {
    content: " *";
    color: red;
}
EOT;
$this->registerCss($css);
?>

<div class="organization-search">
    <?php $form = ActiveForm::begin($formConfig) ?>
    <?php /* @var $form ActiveForm */ ?>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <?= $form->field($model, 'id', [
                'template' => "{input}\n{error}",
            ])->textInput(['placeholder' => Yii::t('user', 'ID')]) ?>
        </div>
        <div class="col-md-3 col-sm-6">
            <?= $form->field($model, 'name', [
                'template' => "{input}\n{error}",
            ])->textInput(['placeholder' => Yii::t('organization', 'Name')]) ?>
        </div>
        <div class="col-md-3 col-sm-6">
            <?= $form->field($model, 'nickname', [
                'template' => "{input}\n{error}",
            ])->textInput(['placeholder' => Yii::t('user', 'Nickname')]) ?>
        </div>
        <div class="col-md-3 col-sm-6">
            <?= $form->field($model, 'type', [
                'template' => "{input}\n{error}"
            ])->dropDownList(OrganizationSearch::getTypesWithEmpty()) ?>
        </div>
        <div class="col-md-3 col-sm-6">
            <?= $form->field($model, 'createdFrom', [
                'template' => "{input}\n{hint}\n{error}",
            ])->widget(DateTimePicker::class, [
                'options' => ['placeholder' => Yii::t('user', 'From')],
                'pluginOptions' => [
                    'todayHighlight' => true
                ]
            ])->hint(Yii::t('user', 'If you do not limit the start time, leave it blank.')) ?>
        </div>
        <div class="col-md-3 col-sm-6">
            <?= $form->field($model, 'createdTo', [
                'template' => "{input}\n{hint}\n{error}",
            ])->widget(DateTimePicker::class, [
                'options' => ['placeholder' => Yii::t('user', 'To')],
                'pluginOptions' => [
                    'todayHighlight' => true
                ]
            ])->hint(Yii::t('user', 'If you do not limit the end time, leave it blank.')) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('user', 'Search'), ['id' => "$formId-submit", 'class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('user', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

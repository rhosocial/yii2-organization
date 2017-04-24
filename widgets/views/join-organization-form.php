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
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model JoinOrganizationForm */
/* @var bool $join */
/* @var $formConfig array */
$form = ActiveForm::begin($formConfig);
if (!empty($model->organization->getJoinPassword())) {
    echo $form->field($model, 'password')->textInput();
}
echo Html::submitButton(Yii::t('organization', $join ? 'Join' : 'Exit'), [
    'class' => 'btn btn-primary'
]);
ActiveForm::end();

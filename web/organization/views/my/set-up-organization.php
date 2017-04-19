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

use rhosocial\organization\forms\SetUpForm;
use rhosocial\organization\widgets\SetUpFormWidget;
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model SetUpForm */
$parent = $model->getParent();
$this->title = Yii::t('organization', ($parent) ? 'Set Up New Department' : 'Set Up New Organization');
$this->params['breadcrumbs'][] = $this->title;
if ($parent) {
    echo "<p>" . Yii::t('organization', 'To set up a new department under "{name}":', ['name' => $parent->profile->name]) . "</p>";
} else {
    echo "<p>" . Yii::t('organization', 'Set up a new organization:') . "</p>";
}
echo SetUpFormWidget::widget(['model' => $model]);
?>
<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Back to Organization List'), [
            'index',
        ], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

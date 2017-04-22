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
use rhosocial\organization\widgets\SettingsFormWidget;
use yii\helpers\Html;
use yii\web\View;
/* @var $this View */
/* @var $organization Organization */
/* @var $model SettingsForm */
$profile = $organization->profile;
$name = '';
if ($profile) {
    $name = $profile->name;
} else {
    $name = Yii::t('yii', 'not set');
}
$this->title = '(' . $name . ') ' . Yii::t('organization', 'Settings');
$this->params['breadcrumbs'][] = Yii::t('organization', 'Settings');
?>
<?= SettingsFormWidget::widget([
    'organization' => $organization,
    'model' => $model,
]) ?>
<h3><?= Yii::t('user', 'Other operations') ?></h3>
<hr>
<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Back to Organization List'), [
            'index',
        ], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('organization', 'Member List'), ['member', 'org' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

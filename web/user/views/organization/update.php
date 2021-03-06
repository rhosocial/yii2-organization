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

use rhosocial\organization\Organization;
use rhosocial\organization\Profile;
use rhosocial\organization\widgets\ProfileFormWidget;
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $organization Organization */
/* @var $profile Profile */
$this->title = Yii::t('organization', 'Update Organization') . ' (' . $organization->getID() . ')';
$this->params['breadcrumbs'][] = Yii::t('organization', 'Update Organization');
echo ProfileFormWidget::widget(['model' => $model]);
?>
<h3><?= Yii::t('user', 'Other operations') ?></h3>
<hr>
<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('organization', 'Manage members.'), ['manage-member', 'id' => $organization->getID()], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

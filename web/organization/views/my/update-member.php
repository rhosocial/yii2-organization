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
use rhosocial\organization\widgets\MemberFormWidget;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Member */
$this->title = Yii::t('organization', 'Update Member');
$this->params['breadcrumbs'][] = $this->title;
echo MemberFormWidget::widget(['member' => $model]);
?>
<h3><?= Yii::t('user', 'Other operations') ?></h3>
<hr>
<div class="row">
    <div class="col-md-3">
        <?= Html::a(Yii::t('user', 'Back to List'), [
            'index',
        ], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('organization', 'Member'), ['member', 'org' => $model->organization->getID()], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

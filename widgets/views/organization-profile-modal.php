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
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;

/* @var $this View */
/* @var $toggleButton array */
/* @var $organization Organization */
Modal::begin([
    'header' => $organization->profile->name,
    'toggleButton' => empty($toggleButton) ? [
        'tag' => 'a',
        'label' => $organization->getID(),
    ] : $toggleButton,
]);
?>
<?= DetailView::widget([
    'model' => $organization,
    'attributes' => [
        'id',
        'created_at:datetime',
    ],
]) ?>
<p>
    <?= $organization->profile->description ?>
</p>
<?php Modal::end() ?>

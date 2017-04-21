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
use rhosocial\organization\widgets\OrganizationChildrenListWidget;
use yii\bootstrap\Modal;
use yii\web\View;

/* @var $this View */
/* @var $toggleButton array */
/* @var $organization Organization */
$count = (int)$organization->getChildren()->count();
if ($count == 0) {
    echo '0';
    return;
}
Modal::begin([
    'header' => $organization->profile->name,
    'toggleButton' => empty($toggleButton) ? [
        'tag' => 'a',
        'label' => $count,
    ] : $toggleButton,
]);
?>
<?= OrganizationChildrenListWidget::widget(['organization' => $organization]) ?>
<?php Modal::end() ?>

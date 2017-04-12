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

/* @var $tips boolean|array */
?>
<?php if ($tips): ?>
    <div class="well well-sm">
        <?= Yii::t('organization', 'Member List Directions:') ?>
        <ol>
            <li><?= Yii::t('organization', 'If no search criteria are specified, all members are displayed.') ?></li>
            <li><?= Yii::t('organization', 'When the User ID column is green, it indicates that the user is the current logged-in user.') ?></li>
            <li><?= Yii::t('user', 'If the creation time is the same as the last update time, there is no change.') ?></li>
            <li><?= Yii::t('organization', 'If you can not see the "Update" or "Remove Member" button, it means that the you do not have corresponding permission.') ?></li>
            <?php if (is_array($tips)): ?>
                <?php foreach ($tips as $tip): ?>
                    <li><?= $tip ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ol>
    </div>
<?php endif; ?>


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
use yii\helpers\Html;
$this->title = Yii::t('organization', 'View');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= YII_ENV == YII_ENV_DEV ? Yii::t('organization', 'This is the {name} page. You may modify the following file to customize its content:', ['name' => Yii::t('organization', 'View')]) : ''?>
    </p>

    <code><?= __FILE__ ?></code>
</div>

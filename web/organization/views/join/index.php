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
use yii\web\View;

/* @var $this View */
/* @var $organization Organization */

$this->title = Yii::t('organization', 'Join {name}', ['name' => $organization->profile->name]);
$this->params['breadcrumbs'][] = $this->title;

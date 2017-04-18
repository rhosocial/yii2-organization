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

namespace rhosocial\organization\widgets;

use rhosocial\organization\forms\SetUpForm;
use Yii;
use yii\base\Widget;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpFormWidget extends Widget
{
    public $model;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->model) {
            $this->model = new SetUpForm(['user' => Yii::$app->user->identity]);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('set-up-form', ['model' => $this->model]);
    }
}

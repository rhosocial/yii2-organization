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

use rhosocial\user\User;
use yii\base\Widget;

/**
 * Class SetUpButtonWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpButtonWidget extends Widget
{
    /**
     * @var User
     */
    public $operator;
    /**
     * @var array|string
     */
    public $url = ['set-up-organization'];
    /**
     * @var array
     */
    public $options = ['class' => 'btn btn-primary'];
    /**
     * @return string
     */
    public function run()
    {
        return $this->render('set-up-button', [
            'url' => $this->url,
            'options' => $this->options,
            'operator' => $this->operator,
        ]);
    }
}

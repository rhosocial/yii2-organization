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

use rhosocial\organization\Member;
use yii\base\Widget;
use yii\web\ServerErrorHttpException;

/**
 * Class MemberFormWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberFormWidget extends Widget
{
    /**
     * @var Member
     */
    public $member;
    public function init()
    {
        if (!$this->member) {
            throw new ServerErrorHttpException('Invalid Member Model.');
        }
    }

    /**
     * @return string rendering results.
     */
    public function run()
    {
        return $this->render('member-form', ['model' => $this->member]);
    }
}

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

namespace rhosocial\organization\exceptions;

use yii\base\Exception;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RevokePreventedException extends Exception
{
    public function getName()
    {
        return 'RevokePreventedException';
    }
}

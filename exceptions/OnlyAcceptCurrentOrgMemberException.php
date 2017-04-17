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

use yii\base\InvalidParamException;

/**
 * Class OnlyAcceptCurrentOrgMemberException
 * @package rhosocial\organization\exceptions
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OnlyAcceptCurrentOrgMemberException extends InvalidParamException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Only Accept Current Organization\'s Members';
    }
}

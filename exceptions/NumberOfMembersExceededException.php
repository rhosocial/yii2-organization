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

use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * Class NumberOfMembersExceededException
 * @package rhosocial\organization\exceptions
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class NumberOfMembersExceededException extends UnauthorizedHttpException
{
    /**
     * Constructor.
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($code = 0, \Exception $previous = null)
    {
        parent::__construct(Yii::t('organization', "The number of members has reached the maximum."), $code, $previous);
    }

    public function getName()
    {
        return 'NumberOfMembersExceededException';
    }
}

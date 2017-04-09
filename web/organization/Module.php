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

namespace rhosocial\organization\web\organization;

use rhosocial\organization\Organization;
use Yii;
/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class Module extends \yii\base\Module
{
    const RESULT_SUCCESS = 'success';
    const RESULT_FAILED = 'failed';
    const SESSION_KEY_MESSAGE = 'session_key_message';
    const SESSION_KEY_RESULT = 'session_key_result';

    /**
     * Get organization.
     * @param Organization|string|integer $organization
     * @return Organization
     */
    public static function getOrganization($organization)
    {
        if (!$organization) {
            return null;
        }
        $class = Yii::$app->user->identity->organizationClass;
        if ($organization instanceof $class) {
            $organization = $organization->getID();
        }
        if (is_numeric($organization) || is_int($organization)) {
            return $class::find()->id($organization)->one();
        }
        if (is_string($organization) && strlen($organization) == 16) {
            return $class::find()->guid($organization)->one();
        }
        return null;
    }
}

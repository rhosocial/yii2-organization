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
use rhosocial\user\User;
use Yii;
use yii\base\InvalidParamException;
use yii\validators\IpValidator;

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
        $identityClass = Yii::$app->user->identityClass;
        $noInitIdentity = $identityClass::buildNoInitModel();
        /* @var $noInitIdentity User */
        $class = $noInitIdentity->organizationClass;
        if ($organization instanceof $class) {
            $organization = $organization->getID();
        }
        return $class::find()->guidOrId($organization)->one();
    }

    /**
     * @param string $entrance
     * @return Organization
     */
    public static function getOrganizationByEntrance($entrance)
    {
        $entrance = (string)$entrance;
        if ($entrance === '') {
            throw new InvalidParamException(Yii::t('organization', "Entrance should not be empty."));
        }
        $identityClass = Yii::$app->user->identityClass;
        $noInitIdentity = $identityClass::buildNoInitModel();
        /* @var $noInitIdentity User */
        $class = $noInitIdentity->organizationClass;
        $noInit = $class::buildNoInitModel();
        /* @var $noInit Organization */
        $settingClass = $noInit->organizationSettingClass;
        $setting = $settingClass::find()->andWhere([
            $noInit->getNoInitOrganizationSetting()->idAttribute => $class::SETTING_ITEM_JOIN_ENTRANCE_URL,
            $noInit->getNoInitOrganizationSetting()->contentAttribute => $entrance,
        ])->one();
        if (!$setting) {
            return null;
        }
        return $setting->host;
    }

    /**
     * Validate IP Ranges.
     * @param Organization $organization
     * @param string $ip
     * @param array $errors
     * @return bool
     */
    public static function validateIPRanges($organization, $ip = null, &$errors = null)
    {
        if ($ip === null) {
            $ip = Yii::$app->request->userIP;
        }
        $range = $organization->joinIpAddress;
        if (empty($range) || !is_string($range)) {
            $range = '0.0.0.0/0';
        }
        $validator = new IpValidator(['ranges' => [$range]]);
        return $validator->validate($ip, $errors);
    }
}

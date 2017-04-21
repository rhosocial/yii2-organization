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

namespace rhosocial\organization\web\organization\controllers\my;

use rhosocial\organization\exceptions\UnauthorizedManageProfileException;
use rhosocial\organization\Organization;
use rhosocial\organization\Profile;
use rhosocial\organization\rbac\permissions\ManageProfile;
use rhosocial\organization\web\organization\Module;
use rhosocial\user\User;
use Yii;
use yii\base\Action;

/**
 * Class UpdateAction
 * @package rhosocial\organization\web\organization\controllers\my
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class UpdateAction extends Action
{
    public $updateSuccessMessage;
    public $updateFailedMessage;

    /**
     * Initialize messages.
     */
    protected function initMessages()
    {
        if (!is_string($this->updateSuccessMessage)) {
            $this->updateSuccessMessage = Yii::t('user' ,'Updated.');
        }
        if (!is_string($this->updateFailedMessage)) {
            $this->updateFailedMessage = Yii::t('user', 'Failed to Update.');
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initMessages();
        parent::init();
    }

    /**
     * Check access.
     * @param Organization $org
     * @param User $user
     * @return bool
     * @throws UnauthorizedManageProfileException
     */
    public static function checkAccess($org, $user)
    {
        MemberAction::checkAccess($org, $user);
        if (!Yii::$app->authManager->checkAccess($user->getGUID(), (new ManageProfile)->name, ['organization' => $org])) {
            throw new UnauthorizedManageProfileException();
        }
        return true;
    }

    /**
     * Run action
     * @param string|integer $id
     * @return string
     */
    public function run($id)
    {
        $org = Module::getOrganization($id);
        $user = Yii::$app->user->identity;
        static::checkAccess($org, $user);

        $profile = $org->profile;
        if (!$profile) {
            $profile = $org->createProfile();
        }
        $profile->scenario = Profile::SCENARIO_UPDATE;
        if ($profile->load(Yii::$app->request->post())) {
            if ($profile->save()) {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '(' . $org->getID() . ') ' . $this->updateSuccessMessage);
                return $this->controller->redirect(['update', 'id' => $org->getID()]);
            }
            Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
            Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '(' . $org->getID() . ') ' . $this->updateFailedMessage);
        }
        return $this->controller->render('update', ['organization' => $org, 'model' => $profile]);
    }
}

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

use rhosocial\organization\exceptions\UnauthorizedManageMemberException;
use rhosocial\organization\Member;
use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\organization\web\organization\Module;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;

/**
 * Class UpdateMemberAction
 * @package rhosocial\organization\web\organization\controllers\my
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class UpdateMemberAction extends Action
{
    public $updateSuccessMessage;
    public $updateFailedMessage;

    protected function initMessages()
    {
        if (!is_string($this->updateSuccessMessage)) {
            $this->updateSuccessMessage = Yii::t('user' ,'Updated.');
        }
        if (!is_string($this->updateFailedMessage)) {
            $this->updateFailedMessage = Yii::t('user', 'Failed to Update.');
        }
    }

    public function init()
    {
        $this->initMessages();
        parent::init();
    }

    /**
     * Check access.
     * @param $org
     * @param $user
     * @return bool
     * @throws UnauthorizedManageMemberException
     */
    public static function checkAccess($org, $user)
    {
        MemberAction::checkAccess($org, $user);
        if (!Yii::$app->authManager->checkAccess($user->getGUID(), (new ManageMember)->name, ['organization' => $org])) {
            throw new UnauthorizedManageMemberException();
        }
        return true;
    }

    /**
     * Update member.
     * @param $org string Organization ID.
     * @param $user string User ID.
     * @return string|\yii\web\Response
     */
    public function run($org, $user)
    {
        $organization = Module::getOrganization($org);
        $member = $organization->getMember($user);
        static::checkAccess($organization, Yii::$app->user->identity);

        $member->scenario = Member::SCENARIO_ADMIN_UPDATE;
        if ($member->load(Yii::$app->request->post())) {
            if ($member->save()) {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->updateSuccessMessage);
                return $this->controller->redirect(['member', 'org' => $org]);
            }
            Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
            Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->updateFailedMessage);
        }

        return $this->controller->render('update-member', ['model' => $member]);
    }
}

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

use rhosocial\organization\exceptions\NotMemberOfOrganizationException;
use rhosocial\organization\exceptions\RemovePreventedException;
use rhosocial\organization\Member;
use rhosocial\organization\web\organization\Module;
use rhosocial\user\User;
use Yii;
use yii\base\Action;

/**
 * Class RemoveMemberAction
 * @package rhosocial\organization\web\organization\controllers\my
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RemoveMemberAction extends Action
{
    public $removeMemberSuccessMessage;
    public $removeMemberFailedMessage;

    protected function initMessages()
    {
        if (!is_string($this->removeMemberSuccessMessage)) {
            $this->removeMemberSuccessMessage = Yii::t('organization' ,'Member Removed.');
        }
        if (!is_string($this->removeMemberFailedMessage)) {
            $this->removeMemberFailedMessage = Yii::t('organization', 'Failed to Remove.');
        }
    }

    public function init()
    {
        $this->initMessages();
        parent::init();
    }

    /**
     * Check access.
     * @param Organization $org
     * @param string|integer $id User ID. If access checking passed, it will be re-assigned with the User model.
     * @param User $user
     * @return boolean
     * @throws NotMemberOfOrganizationException
     */
    public static function checkAccess($org, &$id, $user)
    {
        AddMemberAction::checkAccess($org, $user);
        $member = Member::find()->organization($org)->user($id)->one();
        /* @var $member Member */
        if (!$member) {
            throw new NotMemberOfOrganizationException();
        }
        if ($user->isOrganizationAdministrator($org) && $member->isAdministrator()) {
            throw new RemovePreventedException(Yii::t('organization', 'Administrator can not remove other administrators.'));
        }
        $id = $member->memberUser;
        return true;
    }

    /**
     * @param $org
     * @param $user
     * @return \yii\web\Response
     */
    public function run($org, $user)
    {
        $org = Module::getOrganization($org);
        $id = $user;
        if (!static::checkAccess($org, $user, Yii::$app->user->identity)) {
            return $this->controller->redirect(['index']);
        }
        if ($org->removeMember($user)) {
            Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
            Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '(' . $id . ') ' . $this->removeMemberSuccessMessage);
            return $this->controller->redirect(['member', 'org' => $org->getID()]);
        }
        Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
        Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '(' . $id . ') ' . $this->removeMemberFailedMessage);
        return $this->controller->redirect(['member', 'org' => $org->getID()]);
    }
}

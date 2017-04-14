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

use rhosocial\organization\exceptions\UnauthorizedCreatorException;
use rhosocial\organization\Member;
use rhosocial\organization\Organization;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use rhosocial\organization\web\organization\Module;
use rhosocial\user\User;
use Yii;
use yii\base\Action;
use yii\base\InvalidCallException;
use yii\db\IntegrityException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class AssignAdminAction
 * @package rhosocial\organization\web\organization\controllers\my
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AssignAdminAction extends Action
{
    public $assignAdminSuccessMessage;
    public $assignAdminFailedMessage;
    public $revokeAdminSuccessMessage;
    public $revokeAdminFailedMessage;

    protected function initMessages()
    {
        if (!is_string($this->assignAdminSuccessMessage)) {
            $this->assignAdminSuccessMessage = Yii::t('organization', 'Administrator assigned.');
        }
        if (!is_string($this->assignAdminFailedMessage)) {
            $this->assignAdminFailedMessage = Yii::t('organization', 'Failed to assign administrator.');
        }
        if (!is_string($this->revokeAdminSuccessMessage)) {
            $this->revokeAdminSuccessMessage = Yii::t('organization' ,'Administrator revoked.');
        }
        if (!is_string($this->revokeAdminFailedMessage)) {
            $this->revokeAdminFailedMessage = Yii::t('organization', 'Failed to revoke.');
        }
    }

    public function init()
    {
        $this->initMessages();
        parent::init();
    }

    /**
     * @param Organization $org
     * @param User $user
     * @return boolean
     * @throws UnauthorizedCreatorException
     */
    public static function checkAccess($org, $user)
    {
        MemberAction::checkAccess($org, $user);
        if ($org->isOrganization() && !Yii::$app->authManager->checkAccess($user->getGUID(), (new OrganizationCreator)->name, ['organization' => $org])) {
            throw new UnauthorizedCreatorException();
        }
        if ($org->isDepartment() && !Yii::$app->authManager->checkAccess($user->getGUID(), (new DepartmentCreator)->name, ['organization' => $org])) {
            throw new UnauthorizedCreatorException();
        }
        return true;
    }

    /**
     * @param Organization $org
     * @param User $user
     * @return boolean
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    protected function assignAdmin($org, $user)
    {
        try {
            return $org->addAdministrator($user);
        } catch (IntegrityException $ex) {
            throw new ServerErrorHttpException($ex->getMessage());
        } catch (InvalidCallException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
        return false;
    }

    /**
     * @param Organization $org
     * @param Member|User $user
     * @param boolean $keep Keep member after administrator being revoked.
     * @return boolean
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    protected function revokeAdmin($org, &$user, $keep = true)
    {
        try {
            return $org->removeAdministrator($user, $keep);
        } catch (IntegrityException $ex) {
            throw new ServerErrorHttpException($ex->getMessage());
        } catch (InvalidCallException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
        return false;
    }

    /**
     * @param $org
     * @param $user
     * @param string $revoke
     * @return string rendering results.
     */
    public function run($org, $user, $revoke = '0')
    {
        $organization = Module::getOrganization($org);
        static::checkAccess($organization, Yii::$app->user->identity);

        if ($revoke == '1') {
            if ($this->revokeAdmin($organization, $user)) {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->revokeAdminSuccessMessage);
                return $this->controller->redirect(['member', 'org' => $org]);
            }
            Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
            Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->revokeAdminFailedMessage);
            return $this->controller->redirect(['member', 'org' => $org]);
        } elseif ($revoke == '0') {
            if ($this->assignAdmin($organization, $user)) {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->assignAdminSuccessMessage);
                return $this->controller->redirect(['member', 'org' => $org]);
            }
            Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
            Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->assignAdminFailedMessage);
            return $this->controller->redirect(['member', 'org' => $org]);
        }
        return $this->controller->redirect(['member', 'org' => $org]);
    }
}

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

use rhosocial\organization\exceptions\NumberOfMembersExceededException;
use rhosocial\organization\exceptions\UnauthorizedManageMemberException;
use rhosocial\organization\Organization;
use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\organization\web\organization\Module;
use rhosocial\user\User;
use Yii;
use yii\base\Action;
use yii\web\ServerErrorHttpException;

/**
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AddMemberAction extends Action
{
    /**
     * Check access.
     * It will call [[MemberAction::checkAccess()]] first.
     * Then, it will check whether the [[$user]] has the permission to manage member of the organization or department.
     * If not, the UnauthorizedManageMemberException will be thrown.
     *
     * @see MemberAction
     * @param Organization $org
     * @param User $user
     * @return boolean
     * @throws UnauthorizedManageMemberException
     * @throws NumberOfMembersExceededException
     */
    public static function checkAccess($org, $user)
    {
        MemberAction::checkAccess($org, $user);
        if (!Yii::$app->authManager->checkAccess($user->getGUID(), (new ManageMember)->name, ['organization' => $org])) {
            throw new UnauthorizedManageMemberException();
        }
        if ($org->hasReachedMemberLimit()) {
            throw new NumberOfMembersExceededException();
        }
        return true;
    }

    /**
     * Add member.
     * @param Organization $org
     * @param User|string|integer $user
     * @return boolean
     */
    protected function addMember($org, &$user)
    {
        return $org->addMember($user);
    }

    public function run($org, $u = null)
    {
        $organization = Module::getOrganization($org);
        $user = Yii::$app->user->identity;
        static::checkAccess($organization, $user);

        // If $u is not empty and the method is Post, it is considered to be a adding member operation.
        if (!empty($u) && Yii::$app->request->isPost) {
            $member = $u;
            if ($this->addMember($organization, $member)) {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, "($u) " . Yii::t('organization', 'Member added.'));
                return $this->controller->redirect(['add-member', 'org' => $org]);
            }
            Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
            Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, Yii::t('organization', 'Failed to add member.'));
            return $this->controller->redirect(['add-member','org' => $org]);
        }
        $searchModel = Yii::$app->user->identity->getSearchModel();
        $dataProvider = $searchModel->search(Yii::$app->request->post());
        return $this->controller->render('add-member', [
            'organization' => $organization,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
}

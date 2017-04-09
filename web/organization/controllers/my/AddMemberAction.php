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
use rhosocial\organization\web\organization\Module;
use rhosocial\organization\Organization;
use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\user\User;
use rhosocial\user\UserProfileSearch;
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
     * @param Organization $org
     * @param User $user
     * @return boolean
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

    public function run($org, $user = null)
    {
        $organization = Module::getOrganization($org);
        $user = Yii::$app->user->identity;
        static::checkAccess($organization, $user);
        if (!class_exists($this->controller->userProfileSearchClass)) {
            throw new ServerErrorHttpException('Unknown User Profile View.');
        }
        $class = $this->controller->userProfileSearchClass;
        $searchModel = new $class();
        $dataProvider = $searchModel->search(Yii::$app->request->post());
        return $this->controller->render('add-member', ['org' => $org, 'dataProvider' => $dataProvider, 'searchModel' => $searchModel]);
    }
}

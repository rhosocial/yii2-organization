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
use rhosocial\organization\forms\SettingsForm;
use rhosocial\organization\rbac\permissions\ManageProfile;
use rhosocial\organization\web\organization\Module;
use Yii;
use yii\base\Action;

/**
 * Class SettingsAction
 * @package rhosocial\organization\web\organization\controllers\my
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SettingsAction extends Action
{
    /**
     * Check access.
     * @param $org
     * @param $user
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
     * @return string
     */
    public function run($id)
    {
        $organization = Module::getOrganization($id);
        $user = Yii::$app->user->identity;
        static::checkAccess($organization, $user);
        $model = new SettingsForm([
            'organization' => $organization,
        ]);
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->submit()) {
                return $this->controller->redirect('settings', ['id' => $id]);
            }
        }
        return $this->controller->render('settings', [
            'organization' => $organization,
            'model' => $model
        ]);
    }
}

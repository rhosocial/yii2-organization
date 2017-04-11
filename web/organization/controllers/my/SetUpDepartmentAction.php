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

use rhosocial\organization\forms\SetUpForm;
use rhosocial\organization\rbac\permissions\SetUpDepartment;
use rhosocial\organization\web\organization\Module;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpDepartmentAction extends Action
{
    public $departmentSetUpSuccessMessage;
    public $departmentSetUpFailedMessage;

    protected function initMessages()
    {
        if (!is_string($this->departmentSetUpSuccessMessage)) {
            $this->departmentSetUpSuccessMessage = Yii::t('organization' ,'Department Set Up.');
        }
        if (!is_string($this->departmentSetUpFailedMessage)) {
            $this->departmentSetUpFailedMessage = Yii::t('organization', 'Department Set Up Failed.');
        }
    }

    public function init()
    {
        $this->initMessages();
        parent::init();
    }

    /**
     * Set up department.
     * @param string $parent Parent organization or department ID.
     * @return string the rendering result.
     */
    public function run($parent)
    {
        $model = new SetUpForm(['user' => Yii::$app->user->identity, 'parent' => $parent]);
        if (!$model->getParent()) {
            throw new BadRequestHttpException(Yii::t('organization', 'Parent Organization/Department Not Exist.'));
        }
        if (!Yii::$app->authManager->checkAccess(Yii::$app->user->identity, (new SetUpDepartment)->name, ['organization' => $model->getParent()])) {
            throw new UnauthorizedHttpException(Yii::t('organization', 'You do not have access to set up new department.'));
        }
        if ($model->load(Yii::$app->request->post())) {
            try {
                if (($result = $model->setUpDepartment()) === true) {
                    Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '(' . $model->getUser()->lastSetUpOrganization->getID() . ') ' . $this->departmentSetUpSuccessMessage);
                    return $this->controller->redirect(['index']);
                }
                if ($result instanceof \Exception) {
                    throw $result;
                }
            } catch (\Exception $ex) {
                Yii::error($ex->getMessage(), __METHOD__);
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->departmentSetUpFailedMessage);
            }
        }
        return $this->controller->render('set-up-organization', ['model' => $model]);
    }
}

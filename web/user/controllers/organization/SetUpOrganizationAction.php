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

namespace rhosocial\organization\web\user\controllers\organization;

use rhosocial\organization\forms\SetUpForm;
use rhosocial\organization\web\user\controllers\OrganizationController;
use Yii;
use yii\base\Action;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpOrganizationAction extends Action
{
    public $organizationSetUpSuccessMessage;
    public $organizationSetUpFailedMessage;

    protected function initMessages()
    {
        if (!is_string($this->organizationSetUpSuccessMessage)) {
            $this->organizationSetUpSuccessMessage = Yii::t('organization' ,'Organization Set Up.');
        }
        if (!is_string($this->organizationSetUpFailedMessage)) {
            $this->organizationSetUpFailedMessage = Yii::t('organization', 'Organization Set Up Failed.');
        }
    }

    public function init()
    {
        $this->initMessages();
        parent::init();
    }

    public function run()
    {
        $model = new SetUpForm(['user' => Yii::$app->user->identity]);
        if ($model->load(Yii::$app->request->post())) {
            try {
                if (($result = $model->setUpOrganization()) === true) {
                    Yii::$app->session->setFlash(OrganizationController::SESSION_KEY_RESULT, OrganizationController::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(OrganizationController::SESSION_KEY_MESSAGE, '(' . $model->getUser()->lastSetUpOrganization->getID() . ') ' . $this->organizationSetUpSuccessMessage);
                    return $this->controller->redirect(['list']);
                }
                if ($result instanceof \Exception) {
                    throw $result;
                }
            } catch (\Exception $ex) {
                Yii::error($ex->getMessage(), __METHOD__);
                Yii::$app->session->setFlash(OrganizationController::SESSION_KEY_RESULT, OrganizationController::RESULT_FAILED);
                Yii::$app->session->setFlash(OrganizationController::SESSION_KEY_MESSAGE, $this->organizationSetUpFailedMessage);
            }
        }
        return $this->controller->render($this->controller->viewBasePath . 'set-up-organization', ['model' => $model]);
    }
}

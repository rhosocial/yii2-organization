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

use rhosocial\organization\web\organization\Module;
use Yii;
use yii\base\Action;
use yii\web\UnauthorizedHttpException;

/**
 * Class ExitAction
 * @package rhosocial\organization\web\organization\controllers\my
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ExitAction extends Action
{
    public $exitSuccessMessage;
    public $exitFailedMessage;

    /**
     * Initialize messages.
     */
    protected function initMessages()
    {
        if (!is_string($this->exitSuccessMessage) || empty($this->exitSuccessMessage)) {
            $this->exitSuccessMessage = Yii::t('organization', 'Exited.');
        }
        if (!is_string($this->exitFailedMessage) || empty($this->exitFailedMessage)) {
            $this->exitFailedMessage = Yii::t('organization', 'Failed to exit.');
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
     * Run action
     * @param $id
     * @return \yii\web\Response
     * @throws UnauthorizedHttpException
     */
    public function run($id)
    {
        $organization = Module::getOrganization($id);
        try {
            if ($organization->removeMember(Yii::$app->user->identity)) {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->exitSuccessMessage);
                return $this->controller->redirect(['index']);
            } else {
                Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
                Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, $this->exitFailedMessage);
            }
        } catch (\Exception $ex) {
            throw new UnauthorizedHttpException($ex->getMessage());
        }
        return $this->controller->redirect(['index']);
    }
}

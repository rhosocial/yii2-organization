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

use rhosocial\organization\exceptions\RevokePreventedException;
use rhosocial\organization\web\user\controllers\OrganizationController;
use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RevokeAction extends Action
{
    public $organizationRevokeSuccessMessage;
    public $organizationRevokeFailedMessage;

    protected function initMessages()
    {
        if (!is_string($this->organizationRevokeSuccessMessage)) {
            $this->organizationRevokeSuccessMessage = Yii::t('organization', 'Successfully revoked.');
        }
        if (!is_string($this->organizationRevokeFailedMessage)) {
            $this->organizationRevokeFailedMessage = Yii::t('organization', 'Failed to revoke.');
        }
    }

    public function init()
    {
        $this->initMessages();
        parent::init();
    }
    /**
     * Revoke organization or department.
     * @param string|integer $id
     * @throws ServerErrorHttpException
     */
    public function run($id)
    {
        try {
            Yii::$app->user->identity->revokeOrganization($id, true);
        } catch (InvalidParamException $ex) {
            throw new BadRequestHttpException(Yii::t('organization', $ex->getMessage()));
        } catch (RevokePreventedException $ex) {
            throw new BadRequestHttpException(Yii::t('organization', $ex->getMessage()));
        } catch (\Exception $ex) {
            throw new ServerErrorHttpException($ex->getMessage());
        }
        Yii::$app->session->setFlash(OrganizationController::SESSION_KEY_RESULT, OrganizationController::RESULT_SUCCESS);
        Yii::$app->session->setFlash(OrganizationController::SESSION_KEY_MESSAGE, "($id) " . $this->organizationRevokeSuccessMessage);
        return $this->controller->redirect(['list']);
    }
}

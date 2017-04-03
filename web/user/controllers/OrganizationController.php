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

namespace rhosocial\organization\web\user\controllers;

use rhosocial\organization\forms\SetUpForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ServerErrorHttpException;
use yii\web\Controller;

/**
 * Organization Controller, designed for user module.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationController extends Controller
{
    const RESULT_SUCCESS = 'success';
    const RESULT_FAILED = 'failed';
    const SESSION_KEY_MESSAGE = 'session_key_message';
    const SESSION_KEY_RESULT = 'session_key_result';
    public $organizationSetUpSuccessMessage;
    public $organizationSetUpFailedMessage;
    public $departmentSetUpSuccessMessage;
    public $departmentSetUpFailedMessage;

    protected $viewBasePath = '@rhosocial/organization/web/user/views/organization/';

    protected function initMessages()
    {
        if (!is_string($this->organizationSetUpSuccessMessage)) {
            $this->organizationSetUpSuccessMessage = Yii::t('organization' ,'Organization Set Up.');
        }
        if (!is_string($this->organizationSetUpFailedMessage)) {
            $this->organizationSetUpFailedMessage = Yii::t('organization', 'Organization Set Up Failed.');
        }
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

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * List all organization(s) and department(s) which current user has joined in.
     * @return string the rendering result.
     */
    public function actionList()
    {
        $identity = Yii::$app->user->identity;
        if (!$identity) {
            throw new ServerErrorHttpException('User Not Found.');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $identity->getAtOrganizations(),
            'pagination' => [
                'pageParam' => 'oganization-page',
                'pageSize' => 20,
            ],
            'sort' => [
                'sortParam' => 'organization-sort',
            ],
        ]);
        return $this->render($this->viewBasePath . 'list', ['dataProvider' => $dataProvider]);
    }

    /**
     * @return string the rendering result.
     */
    public function actionSetUpOrganization()
    {
        $model = new SetUpForm(['user' => Yii::$app->user->identity]);
        if ($model->load(Yii::$app->request->post())) {
            try {
                if (($result = $model->setUpOrganization()) === true) {
                    Yii::$app->session->setFlash(self::SESSION_KEY_RESULT, self::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(self::SESSION_KEY_MESSAGE, $this->organizationSetUpSuccessMessage);
                    return $this->redirect(['index']);
                }
                if ($result instanceof \Exception) {
                    throw $result;
                }
            } catch (\Exception $ex) {
                Yii::error($ex->getMessage(), __METHOD__);
                Yii::$app->session->setFlash(self::SESSION_KEY_RESULT, self::RESULT_FAILED);
                Yii::$app->session->setFlash(self::SESSION_KEY_MESSAGE, $this->organizationSetUpFailedMessage);
            }
        }
        return $this->render('set-up-organization', ['model' => $model]);
    }

    /**
     * Set up department.
     * @param string $parent Parent organization or department ID.
     * @return string the rendering result.
     */
    public function actionSetUpDepartment($parent)
    {
        $model = new SetUpForm(['user' => Yii::$app->user->identity, 'parent' => $parent]);
        if ($model->load(Yii::$app->request->post())) {
            try {
                if (($result = $model->setUpDepartment()) === true) {
                    Yii::$app->session->setFlash(self::SESSION_KEY_RESULT, self::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(self::SESSION_KEY_MESSAGE, $this->departmentSetUpSuccessMessage);
                    return $this->redirect(['index']);
                }
                if ($result instanceof \Exception) {
                    throw $result;
                }
            } catch (\Exception $ex) {
                Yii::error($ex->getMessage(), __METHOD__);
                Yii::$app->session->setFlash(self::SESSION_KEY_RESULT, self::RESULT_FAILED);
                Yii::$app->session->setFlash(self::SESSION_KEY_MESSAGE, $this->departmentSetUpFailedMessage);
            }
        }
        return $this->render('set-up-organization', ['model' => $model]);
    }
}

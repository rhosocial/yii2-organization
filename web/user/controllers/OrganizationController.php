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
use rhosocial\organization\Organization;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;

/**
 * Organization Controller, designed for user module.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationController extends Controller
{
    public $layout = '@rhosocial/organization/web/user/views/layouts/organization';
    const RESULT_SUCCESS = 'success';
    const RESULT_FAILED = 'failed';
    const SESSION_KEY_MESSAGE = 'session_key_message';
    const SESSION_KEY_RESULT = 'session_key_result';
    public $organizationSetUpSuccessMessage;
    public $organizationSetUpFailedMessage;
    public $departmentSetUpSuccessMessage;
    public $departmentSetUpFailedMessage;
    public $organizationRevokeSuccessMessage;
    public $organizationRevokeFailedMessage;

    public $viewBasePath = '@rhosocial/organization/web/user/views/organization/';

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
     * Get organization by specific parameter.
     * @param Organization|string|integer $organization
     * @return Organization
     */
    public function getOrganization($organization)
    {
        if (!$organization) {
            return null;
        }
        $class = Yii::$app->user->identity->organizationClass;
        if ($organization instanceof $class) {
            $organization = $organization->getID();
        }
        if (is_numeric($organization) || is_int($organization)) {
            return $class::find()->id($organization)->one();
        }
        if (is_string($organization) && strlen($organization) == 16) {
            return $class::find()->guid($organization)->one();
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'list' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\ListAction',
            ],
            'revoke' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\RevokeAction',
            ],
            'view-members' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\ViewMembersAction',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [ // Disallow all unauthorized users to access this controller.
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [ // Disallow user who does not have `setUpOrganization` permission to access this `set-up-organization` action.
                        'actions' => ['set-up-organization'],
                        'allow' => false,
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->can('setUpOrganization');
                        },
                        'denyCallback' => function ($rule, $action) {
                            throw new UnauthorizedHttpException(Yii::t('organization', 'You do not have access to set up new organization.'));
                        },
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'deregister' => ['post'],
                    'revoke' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render($this->viewBasePath . 'index');
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
                    Yii::$app->session->setFlash(self::SESSION_KEY_MESSAGE, '(' . $model->getUser()->lastSetUpOrganization->getID() . ') ' . $this->organizationSetUpSuccessMessage);
                    return $this->redirect(['list']);
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
        return $this->render($this->viewBasePath . 'set-up-organization', ['model' => $model]);
    }

    /**
     * Set up department.
     * @param string $parent Parent organization or department ID.
     * @return string the rendering result.
     */
    public function actionSetUpDepartment($parent)
    {
        $model = new SetUpForm(['user' => Yii::$app->user->identity, 'parent' => $parent]);
        if (!$model->getParent()) {
            throw new BadRequestHttpException(Yii::t('organization', 'Parent Organization/Department Not Exist.'));
        }
        if ($model->load(Yii::$app->request->post())) {
            try {
                if (($result = $model->setUpDepartment()) === true) {
                    Yii::$app->session->setFlash(self::SESSION_KEY_RESULT, self::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(self::SESSION_KEY_MESSAGE, '(' . $model->getUser()->lastSetUpOrganization->getID() . ') ' . $this->departmentSetUpSuccessMessage);
                    return $this->redirect(['list']);
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
        return $this->render($this->viewBasePath . 'set-up-organization', ['model' => $model]);
    }

    public function actionView($id)
    {
        $user = Yii::$app->user->identity;
        $organization = $user->getAtOrganizations()->id($id)->one();
        $profile = $organization->profile;
        return $this->render($this->viewBasePath . 'view');
    }

    public function actionUpdate($id)
    {
        return $this->render($this->viewBasePath . 'update');
    }
}

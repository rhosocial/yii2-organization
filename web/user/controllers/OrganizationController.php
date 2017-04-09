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

use rhosocial\organization\Organization;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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

    public $viewBasePath = '@rhosocial/organization/web/user/views/organization/';

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
            'add-new-member' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\AddNewMemberAction',
            ],
            'list' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\ListAction',
            ],
            'revoke' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\RevokeAction',
            ],
            'view-members' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\ViewMembersAction',
            ],
            'set-up-organization' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\SetUpOrganizationAction',
            ],
            'set-up-department' => [
                'class' => 'rhosocial\organization\web\user\controllers\organization\SetUpDepartmentAction',
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

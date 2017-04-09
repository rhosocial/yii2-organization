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

namespace rhosocial\organization\web\organization\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MyController extends Controller
{
    public $layout = 'main';

    public function actions()
    {
        return [
            'index' => [
                'class' => 'rhosocial\organization\web\organization\controllers\my\IndexAction',
            ],
            'set-up-organization' => [
                'class' => 'rhosocial\organization\web\organization\controllers\my\SetUpOrganizationAction',
            ],
            'revoke' => [
                'class' => 'rhosocial\organization\web\organization\controllers\my\RevokeAction',
            ],
            'member' => [
                'class' => 'rhosocial\organization\web\organization\controllers\my\MemberAction',
            ],
            'add-member' => [
                'class' => 'rhosocial\organization\web\organization\controllers\my\AddMemberAction',
            ],
        ];
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
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
                     'revoke' => ['post'],
                ]
            ],
        ];
    }
}

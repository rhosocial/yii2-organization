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

use rhosocial\organization\exceptions\OrganizationNotFoundException;
use rhosocial\organization\forms\JoinOrganizationForm;
use rhosocial\organization\Organization;
use rhosocial\organization\web\organization\Module;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Class JoinController
 * @package rhosocial\organization\web\organization\controllers
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class JoinController extends Controller
{
    public $layout = 'main';

    /**
     * @inheritdoc
     */
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
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'join' => ['post'],
                    'exit' => ['post'],
                ]
            ],
        ];
    }

    /**
     * @param string $entrance
     * @return Organization
     * @throws BadRequestHttpException
     * @throws OrganizationNotFoundException
     */
    public static function getOrganization($entrance)
    {
        try {
            $organization = Module::getOrganizationByEntrance($entrance);
            if (!$organization) {
                throw new OrganizationNotFoundException();
            }
        } catch (InvalidParamException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
        return $organization;
    }

    /**
     * @param $entrance
     * @return Response|string
     */
    public function actionIndex($entrance)
    {
        $organization = static::getOrganization($entrance);
        $model = new JoinOrganizationForm(['organization' => $organization]);
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * @param string $entrance
     * @return Response|string
     * @throws UnauthorizedHttpException
     */
    public function actionJoin($entrance)
    {
        $organization = static::getOrganization($entrance);
        $user = Yii::$app->user->identity;
        if ($organization->creator->equals($user)) {
            return $this->redirect(['index', 'entrance' => $entrance]);
        }
        $model = new JoinOrganizationForm(['organization' => $organization]);
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                if ($organization->addMember($user)) {
                    Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '');
                } else {
                    Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
                    Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '');
                }
            } catch (\Exception $ex) {
                throw new UnauthorizedHttpException($ex->getMessage());
            }
        }
        return $this->redirect(['index', 'entrance' => $entrance]);
    }

    /**
     * @param string $entrance
     * @return Response
     * @throws UnauthorizedHttpException
     */
    public function actionExit($entrance)
    {
        $organization = static::getOrganization($entrance);
        $user = Yii::$app->user->identity;
        if ($organization->creator->equals($user)) {
            return $this->redirect(['index', 'entrance' => $entrance]);
        }
        $model = new JoinOrganizationForm(['organization' => $organization]);
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                if ($organization->removeMember($user)) {
                    Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_SUCCESS);
                    Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '');
                } else {
                    Yii::$app->session->setFlash(Module::SESSION_KEY_RESULT, Module::RESULT_FAILED);
                    Yii::$app->session->setFlash(Module::SESSION_KEY_MESSAGE, '');
                }
            } catch (\Exception $ex) {
                throw new UnauthorizedHttpException($ex->getMessage());
            }
        }
        return $this->redirect(['index', 'entrance' => $entrance]);
    }
}

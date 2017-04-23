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

use rhosocial\organization\web\organization\Module;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
     * @param $entrance
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @return Response|string
     */
    public function actionIndex($entrance)
    {
        try {
            $organization = Module::getOrganizationByEntrance($entrance);
            if (!$organization) {
                throw new NotFoundHttpException(Yii::t('organization', 'Organization Not Found.'));
            }
        } catch (InvalidParamException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
        return $this->render('index', ['organization' => $organization]);
    }

    /**
     * @param $entrance
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @return Response|string
     */
    public function actionJoin($entrance)
    {
        try {
            $organization = Module::getOrganizationByEntrance($entrance);
            if (!$organization) {
                throw new NotFoundHttpException(Yii::t('organization', 'Organization Not Found.'));
            }
        } catch (InvalidParamException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
        return $this->redirect(['index', 'entrance' => $entrance]);
    }
}

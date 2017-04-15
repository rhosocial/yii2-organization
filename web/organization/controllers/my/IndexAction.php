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

use rhosocial\organization\Organization;
use rhosocial\user\User;
use Yii;
use yii\base\Action;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class IndexAction extends Action
{
    /**
     * 
     * @param string $orgOnly
     * @return string rendering result.
     */
    public function run()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User */
        $noInitOrg = $user->getNoInitOrganization();
        /* @var $noInitOrg Organization */
        $searchModel = $noInitOrg->getSearchModel();
        $searchModel->query = $searchModel->query->andWhere([$searchModel->memberUserAlias . '.guid' => $user->getGUID()]);
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->controller->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'user' => $user,
        ]);
    }
}

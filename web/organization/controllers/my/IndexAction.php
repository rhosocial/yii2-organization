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
    const ORG_ONLY = '1';
    const ORG_ALL = '0';
    /**
     * 
     * @param string $orgOnly
     * @return string rendering result.
     */
    public function run($orgOnly = self::ORG_ALL)
    {
        $user = Yii::$app->user->identity;
        /* @var $user User */
        $noInitOrg = $user->getNoInitOrganization();
        /* @var $noInitOrg Organization */
        $searchModel = $noInitOrg->getSearchModel();
        $dataProvider = $searchModel->search(Yii::$app->request->post());
        $query = ($orgOnly == self::ORG_ONLY) ? $user->getAtOrganizationsOnly() : $user->getAtOrganizations();
        return $this->controller->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'user' => $user,
            'orgOnly' => $orgOnly == self::ORG_ONLY,
        ]);
    }
}

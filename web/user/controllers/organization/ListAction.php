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

use Yii;
use yii\base\Action;
use yii\data\ActiveDataProvider;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ListAction extends Action
{
    public function run()
    {
        $identity = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => $identity->getAtOrganizations(),
            'pagination' => [
                'pageParam' => 'oganization-page',
                'defaultPageSize' => 20,
                'pageSizeParam' => 'organization-per-page',
            ],
            'sort' => [
                'sortParam' => 'organization-sort',
            ],
        ]);
        return $this->controller->render($this->controller->viewBasePath . 'list', [
            'user' => $identity,
            'dataProvider' => $dataProvider
        ]);
    }
}

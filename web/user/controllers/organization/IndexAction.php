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
class IndexAction extends Action
{
    /**
     * List the organizations the current identity is participating in & set up.
     * @return string rendering results.
     */
    public function run()
    {
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => $user->getAtOrganizationsOnly(),
            'pagination' => [
                'pageParam' => 'organization-page',
                'defaltPageSize' => 20,
                'pageSizeParam' => 'organization-per-page',
            ],
            'sort' => [
                'sortParam' => 'organization-sort',
            ],
        ]);
        return $this->controller->render('index', ['dataProvider' => $dataProvider]);
    }
}

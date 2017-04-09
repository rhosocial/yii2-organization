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

use rhosocial\user\User;
use Yii;
use yii\base\Action;
use yii\data\ActiveDataProvider;

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
        $query = ($orgOnly == self::ORG_ONLY) ? $user->getAtOrganizationsOnly() : $user->getAtOrganizations();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageParam' => 'organization-page',
                'defaultPageSize' => 20,
                'pageSizeParam' => 'organization-per-page',
            ],
            'sort' => [
                'sortParam' => 'organization-sort',
                'attributes' => [
                    'id',
                    'parent' => [
                        'asc' => ['parent_guid' => SORT_ASC],
                        'desc' => ['parent_guid' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('organization', 'Parent ID'),
                    ],
                    'createdAt' => [
                        'asc' => ['created_at' => SORT_ASC],
                        'desc' => ['created_at' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Creation Time'),
                    ],
                    'updatedAt' => [
                        'asc' => ['updated_at' => SORT_ASC],
                        'desc' => ['updated_at' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Last Updated Time'),
                    ],
                ],
            ],
        ]);
        return $this->controller->render('index', ['dataProvider' => $dataProvider, 'user' => $user, 'orgOnly' => $orgOnly == self::ORG_ONLY]);
    }
}

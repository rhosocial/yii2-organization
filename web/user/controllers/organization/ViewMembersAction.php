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

use rhosocial\organization\Organization;
use Yii;
use yii\base\Action;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ViewMembersAction extends Action
{
    /**
     * 
     * @param Organization|string|integer $organization
     * @return string rendering result.
     */
    public function run($organization)
    {
        if (empty($organization) || ($organization = $this->controller->getOrganization($organization)) == null) {
            throw new BadRequestHttpException(Yii::t('organization', 'Organization/Department Not Exist.'));
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $organization->getMembers(),
            'pagination' => [
                'pageParam' => 'organization-member-page',
                'defaultPageSize' => 20,
                'pageSizeParam' => 'organization-member-per-page',
            ],
            'sort' => [
                'sortParam' => 'organization-member-sort',
            ],
        ]);
        $viewBasePath = $this->controller->viewBasePath;
        return $this->controller->render($viewBasePath . 'view-members', [
            'user' => Yii::$app->user->identity,
            'organization' => $organization,
            'dataProvider' => $dataProvider
        ]);
    }
}

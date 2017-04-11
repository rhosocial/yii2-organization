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

use rhosocial\organization\exceptions\NotMemberOfOrganizationException;
use rhosocial\organization\exceptions\OrganizationNotFoundException;
use rhosocial\organization\web\organization\Module;
use rhosocial\organization\Organization;
use rhosocial\user\User;
use Yii;
use yii\base\Action;
use yii\data\ActiveDataProvider;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberAction extends Action
{
    /**
     * Check access.
     * @param Organization $org
     * @param User $user
     * @return boolean
     * @throws OrganizationNotFoundException
     * @throws NotMemberOfOrganizationException
     */
    public static function checkAccess($org, $user)
    {
        if (!$org) {
            throw new OrganizationNotFoundException();
        }
        if (!$org->hasMember($user)) {
            throw new NotMemberOfOrganizationException();
        }
        return true;
    }

    /**
     * 
     * @param Organization|string|integer $org
     * @return string rendering results.
     */
    public function run($org)
    {
        $organization = Module::getOrganization($org);
        $user = Yii::$app->user->identity;
        static::checkAccess($organization, $user);
        $dataProvider = new ActiveDataProvider([
            'query' => $organization->getMembers(),
            'pagination' => [
                'pageParam' => 'member-param',
                'pageSizeParam' => 'member-per-param',
            ],
            'sort' => [
                'sortParam' => 'member-sort',
                'attributes' => [
                    'user_id',
                ],
            ],
        ]);

        return $this->controller->render('member', ['dataProvider' => $dataProvider]);
    }
}

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

namespace rhosocial\organization\queries;

use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\user\User;
use rhosocial\organization\Member;
use rhosocial\organization\Organization;

/**
 * Member Query.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberQuery extends BaseBlameableQuery
{
    public $modelClass = Member::class;

    /**
     * Specify user.
     * @param User $user
     * @return static
     */
    public function user($user)
    {
        $model = $this->noInitModel;
        /* @var $model Member */
        if (!is_string($model->memberAttribute) || empty($model->memberAttribute)) {
            return $this;
        }
        if ($user instanceof BaseUserModel) {
            $user = $user->getGUID();
        }
        return $this->andWhere([$model->memberAttribute => $user]);
    }

    /**
     * Specify organization.
     * Alias of `createdBy` method.
     * @param Organization $organization
     * @return static
     */
    public function organization($organization)
    {
        return $this->createdBy($organization);
    }
}

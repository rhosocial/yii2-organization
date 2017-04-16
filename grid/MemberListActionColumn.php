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

namespace rhosocial\organization\grid;

use rhosocial\user\grid\ActionColumn;
use rhosocial\organization\rbac\permissions\ManageMember;
use rhosocial\organization\Member;
use Yii;
use yii\helpers\Url;

class MemberListActionColumn extends ActionColumn
{
    public $template = '{update} {delete}';
    /**
     * @var User
     */
    public $operator;

    public function init()
    {
        if (!isset($this->operator)) {
            $this->operator = Yii::$app->user->identity;
        }
        parent::init();
        if (!isset($this->header)) {
            $this->header = Yii::t('user', 'Action');
        }
        $this->initUrlCreator();
        $this->initVisibleButtons();
    }

    protected function initUrlCreator()
    {
        if (isset($this->urlCreator)) {
            return;
        }
        $this->urlCreator = function ($action, $model, $key, $index, MemberListActionColumn $column) {
            /* @var $model Member */
            if ($action == 'update') {
                return Url::to(['update-member', 'user' => $model->memberUser->getID(), 'org' => $model->organization->getID()]);
            } elseif ($action == 'delete') {
                return Url::to(['remove-member', 'user' => $model->memberUser->getID(), 'org' => $model->organization->getID()]);
            }
            return '#';
        };
    }

    protected function initVisibleButtons()
    {
        if (!empty($this->visibleButtons)) {
            return;
        }
        $this->visibleButtons = [
            'update' => function ($model, $key, $index) {
                /* @var $model Member */
                return Yii::$app->authManager->checkAccess($this->operator->getGUID(), (new ManageMember)->name, ['organization' => $model->organization]);
            },
            'delete' => function ($model, $key, $index) {
                /* @var $model Member */
                if ($model->isCreator()) {
                    return false;
                }
                if ($model->isAdministrator() && $this->operator->isOrganizationAdministrator($model->organization)) {
                    return false;
                }
                return Yii::$app->authManager->checkAccess($this->operator->getGUID(), (new ManageMember)->name, ['organization' => $model->organization]);
            },
        ];
    }

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('update', false);
        $this->initDefaultButton('delete', false, [
            'data-confirm' => Yii::t('organization', 'Are you sure you want to remove this member from the organization / department?'),
            'data-method' => 'post',
            'title' => Yii::t('organization', 'Remove'),
            'aria-label' => Yii::t('organization', 'Remove'),
        ]);
    }
}

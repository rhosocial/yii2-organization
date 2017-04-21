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
use rhosocial\user\User;
use rhosocial\organization\rbac\permissions\ManageProfile;
use rhosocial\organization\rbac\permissions\SetUpDepartment;
use rhosocial\organization\rbac\permissions\RevokeDepartment;
use rhosocial\organization\rbac\permissions\RevokeOrganization;
use rhosocial\organization\Organization;
use Yii;
use yii\helpers\Url;

/**
 * Class OrganizationListActionColumn
 * @package rhosocial\organization\grid
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationListActionColumn extends ActionColumn
{
    public $template = '{view} {member} {add} {update} {settings} {delete}';

    /**
     * @var User
     */
    public $operator;

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    protected function initUrlCreator()
    {
        if (isset($this->urlCreator)) {
            return;
        }
        $this->urlCreator = function ($action, $model, $key, $index, OrganizationListActionColumn $column) {
            /* @var $model Organization */
            if ($action == 'member') {
                return Url::to(['member', 'org' => $model->getID()]);
            } elseif ($action == 'add') {
                return Url::to(['set-up-department', 'parent' => $model->getID()]);
            } elseif ($action == 'view') {
                return Url::to(['view', 'id' => $model->getID()]);
            } elseif ($action == 'update') {
                return Url::to(['update', 'id' => $model->getID()]);
            } elseif ($action =='settings') {
                return Url::to(['settings', 'id' => $model->getID()]);
            } elseif ($action == 'delete') {
                return Url::to(['revoke', 'id' => $model->getID()]);
            }
            return '#';
        };
    }

    /**
     * @inheritdoc
     */
    protected function initVisibleButtons()
    {
        if (!empty($this->visibleButtons)) {
            return;
        }
        $this->visibleButtons = [
            'view' => true,
            'member' => true,
            'add' => function ($model, $key, $index) {
                return Yii::$app->authManager->checkAccess($this->operator->getGUID(), (new SetUpDepartment)->name, ['organization' => $model]);
            },
            'update' => function ($model, $key, $index) {
                return Yii::$app->authManager->checkAccess($this->operator->getGUID(), (new ManageProfile)->name, ['organization' => $model]);
            },
            'settings' => function ($model, $key, $index) {
                return Yii::$app->authManager->checkAccess($this->operator->getGUID(), (new ManageProfile)->name, ['organization' => $model]);
            },
            'delete' => function ($model, $key, $index) {
                $permission = ($model->isOrganization()) ? (new RevokeOrganization)->name : (new RevokeDepartment)->name;
                return Yii::$app->authManager->checkAccess($this->operator->getGUID(), $permission, ['organization' => $model]);
            },
        ];
    }

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', false);
        $this->initDefaultButton('member', false, [
            'title' => Yii::t('organization', 'Member'),
            'aria-label' => Yii::t('organization', 'Member'),
        ]);
        $this->initDefaultButton('add', false, [
            'title' => Yii::t('organization', 'Set Up New Department'),
            'aria-label' => Yii::t('organization', 'Set Up New Department'),
        ]);
        $this->initDefaultButton('update', false);
        $this->initDefaultButton('settings', false, [
            'title' => Yii::t('organization', 'Settings'),
            'aria-label' => Yii::t('organization', 'Settings'),
        ]);
        $this->initDefaultButton('delete', false, [
            'data-confirm' => Yii::t('organization', 'Are you sure you want to revoke this organization / department?'),
            'data-method' => 'post',
            'title' => Yii::t('organization', 'Remove'),
            'aria-label' => Yii::t('organization', 'Remove'),
        ]);
    }
}

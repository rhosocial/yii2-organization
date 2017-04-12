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

use rhosocial\organization\rbac\permissions\ManageProfile;
use rhosocial\organization\rbac\permissions\SetUpDepartment;
use rhosocial\organization\rbac\permissions\RevokeDepartment;
use rhosocial\organization\rbac\permissions\RevokeOrganization;
use rhosocial\organization\Organization;
use Yii;
use yii\grid\ActionColumn;
use yii\helpers\Url;

/**
 * Class OrganizationListActionColumn
 * @package rhosocial\organization\grid
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationListActionColumn extends ActionColumn
{
    public $template = '{view} {member} {add} {update} {delete}';

    public function init()
    {
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
            } elseif ($action == 'delete') {
                return Url::to(['revoke', 'id' => $model->getID()]);
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
            'view' => true,
            'member' => true,
            'add' => function ($model, $key, $index) {
                return Yii::$app->user->can((new SetUpDepartment)->name, ['organization' => $model]);
            },
            'update' => function ($model, $key, $index) {
                return Yii::$app->user->can((new ManageProfile)->name, ['organization' => $model]);
            },
            'delete' => function ($model, $key, $index) {
                $permission = ($model->isOrganization()) ? (new RevokeOrganization)->name : (new RevokeDepartment)->name;
                return Yii::$app->user->can($permission, ['organization' => $model]);
            },
        ];
    }

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye-open');
        $this->initDefaultButton('member', 'user', [
            'title' => Yii::t('organization', 'Member'),
            'aria-label' => Yii::t('organization', 'Member'),
        ]);
        $this->initDefaultButton('add', 'plus', [
            'title' => Yii::t('organization', 'Add'),
            'aria-label' => Yii::t('organization', 'Add'),
        ]);
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('organization', 'Are you sure you want to revoke this organization / department?'),
            'data-method' => 'post',
            'title' => Yii::t('organization', 'Remove'),
            'aria-label' => Yii::t('organization', 'Remove'),
        ]);
    }
}

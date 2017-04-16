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

use rhosocial\user\User;
use rhosocial\user\grid\ActionColumn;
use rhosocial\organization\Organization;
use Yii;
use yii\helpers\Url;

/**
 * Class AddMemberActionColumn
 * This class is used for GridView, and insert action column in it.
 * This class contains only one action: Add Member.
 * By default, if the user in the list are already member, the "Add" button will not be displayed.
 *
 * Typical usage:
 * ```php
 * echo GridView::widget([
 *     'columns' => [
 *         ...
 *         [
 *             'class' => AddMemberActionColumn::class,
 *             'organization' => <Organization Instance>, // You must specify organization.
 *         ],
 *     ],
 * ]);
 * ```
 *
 * @package rhosocial\organization\grid
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class AddMemberActionColumn extends ActionColumn
{
    /**
     * @var string
     */
    public $template = '{add}';

    /**
     * @var array This array should contain two elements:
     * - iconName
     * - buttonAddOptions
     * @see initDefaultButton()
     */
    public $addButtonConfig;

    /**
     * @var bool If you want to confirm before adding, you should set true.
     */
    public $addConfirm = false;

    /**
     * @var string If you want to modify confirmation text, please set it with your own.
     */
    public $addConfirmText;

    /**
     * @var Organization The organization which the user tend to join in.
     */
    public $organization;

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $buttonAddOptions = [
            'data-method' => 'post',
            'title' => Yii::t('organization', 'Add'),
            'aria-label' => Yii::t('organization', 'Add'),
        ];
        if ($this->addConfirm) {
            if (!isset($this->addConfirmText)) {
                $this->addConfirmText = Yii::t('organization', 'Are you sure to add this user to the organization / department?');
            }
            $buttonAddOptions['data-confirm'] = $this->addConfirmText;
        }
        if (!isset($this->addButtonConfig['iconName'])) {
            $this->addButtonConfig['iconName'] = false;
        }
        if (!isset($this->addButtonConfig['buttonAddOptions'])) {
            $this->addButtonConfig['buttonAddOptions'] = $buttonAddOptions;
        }
        $this->initDefaultButton('add', $this->addButtonConfig['iconName'], $this->addButtonConfig['buttonAddOptions']);
    }

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
        $this->urlCreator = function ($action, $model, $key, $index, AddMemberActionColumn $column) {
            /* @var $model User */
            if ($action == 'add') {
                return Url::to(['add-member', 'org' => $column->organization->getID(), 'u' => $model->id]);
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
            'add' => function ($model, $key, $index) {
                return !$this->organization->hasMember($model->id);
            }
        ];
    }
}

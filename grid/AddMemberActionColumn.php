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
     * @var bool
     */
    public $addConfirm = false;
    /**
     * @var Organization
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
            $buttonAddOptions['data-confirm'] = Yii::t('organization', 'Are you sure to add this user to the organization / department?');
        }
        $this->initDefaultButton('add', false, $buttonAddOptions);
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

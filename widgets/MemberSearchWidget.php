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

namespace rhosocial\organization\widgets;

use rhosocial\organization\MemberSearch;
use rhosocial\organization\Organization;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * Class MemberSearchWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberSearchWidget extends Widget
{
    public $formId = 'member-search-form';
    /**
     * @var null|array
     */
    public $formConfig = null;

    /**
     * @var Organization
     */
    public $organization;
    /**
     * @var MemberSearch
     */
    public $model;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->model == null) {
            throw new InvalidConfigException("The search model should not be empty.");
        }
        if ($this->formConfig == null) {
            $action = isset($this->organization) ? ['member', 'org' => $this->organization->getID()] : ['member'];
            $this->formConfig = [
                'id' => !empty($this->formId) ? $this->formId : 'member-search-form',
                'action' => $action,
                'method' => 'get',
            ];
        }
    }

    public function run()
    {
        return $this->render('member-search', [
            'formId' => $this->formId,
            'formConfig' => $this->formConfig,
            'model' => $this->model,
        ]);
    }
}

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

use rhosocial\organization\OrganizationSearch;
use yii\base\InvalidParamException;
use yii\base\Widget;

/**
 * Class OrganizationSearchWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationSearchWidget extends Widget
{
    public $formId = 'organization-search-form';
    /**
     * @var null|array
     */
    public $formConfig = null;
    /**
     * @var OrganizationSearch
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
            $this->formConfig = [
                'id' => !empty($this->formId) ? $this->formId : 'organization-search-form',
                'action' => ['index'],
                'method' => 'post',
            ];
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('organization-search', [
            'formId' => $this->formId,
            'formConfig' => $this->formConfig,
            'model' => $this->model,
        ]);
    }
}

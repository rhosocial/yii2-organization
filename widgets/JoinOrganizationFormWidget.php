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

use rhosocial\organization\forms\JoinOrganizationForm;
use rhosocial\organization\Organization;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * Class JoinOrganizationFormWidget
 * @package rhosocial\organization\widgets
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class JoinOrganizationFormWidget extends Widget
{
    /**
     * @var JoinOrganizationForm
     */
    public $model;

    /**
     * @var array
     */
    public $formConfig;

    /**
     * @var bool
     */
    public $join = true;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->model->organization) {
            throw new InvalidConfigException("The organization should not be empty.");
        }
        if (empty($this->formConfig)) {
            $this->formConfig = [
                'id' => 'join-organization-form-' . $this->model->organization->getID(),
                'action' => [$this->join ? 'join' : 'exit', 'entrance' => $this->model->organization->getJoinEntranceUrl()],
            ];
        }
    }

    /**
     * Run action
     * @return string
     */
    public function run()
    {
        return $this->render('join-organization-form', [
            'model' => $this->model,
            'join' => $this->join,
            'formConfig' => $this->formConfig,
        ]);
    }
}

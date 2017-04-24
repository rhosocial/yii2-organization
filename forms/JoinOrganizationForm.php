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

namespace rhosocial\organization\forms;

use rhosocial\organization\Organization;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class JoinOrganizationForm
 * @package rhosocial\organization\forms
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class JoinOrganizationForm extends Model
{
    /**
     * @var Organization
     */
    public $organization;

    /**
     * @var string
     */
    public $password = '';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->organization) {
            throw new InvalidConfigException("The organization should not be empty.");
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('organization', 'Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'safe', 'when' => function ($model) {
                /* @var $model static */
                return empty($model->organization->joinPassword);
            }],
            ['password', 'string', 'max' => 255, 'when' => function ($model) {
                /* @var $model static */
                return !empty($model->organization->joinPassword);
            }],
            ['password', 'required', 'when' => function ($model) {
                /* @var $model static */
                return !empty($model->organization->joinPassword);
            }],
            ['password', 'validatePassword', 'when' => function ($model) {
                /* @var $model static */
                return !empty($model->organization->joinPassword);
            }],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @param $validator
     */
    public function validatePassword($attribute, $params, $validator)
    {
        $value = $this->$attribute;
        if ($this->organization->joinPassword != $value) {
            $this->addError($attribute, Yii::t('organization', 'Incorrect Password.'));
        }
    }
}

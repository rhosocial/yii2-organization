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

use rhosocial\user\User;
use rhosocial\organization\Organization;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SetUpForm extends Model
{
    public $name;
    public $nickname = '';
    public $gravatar_type = 0;
    public $gravatar = '';
    public $timezone;
    public $description = '';
    /**
     * @var Organization 
     */
    private $_parent;
    /**
     * @var User 
     */
    private $_user;

    /**
     * Finds user.
     *
     * @return User|null
     * @throws InvalidConfigException
     */
    public function getUser()
    {
        if ($this->_user instanceof Yii::$app->user->identityClass) {
            return $this->_user;
        }
        throw new InvalidConfigException('User Class Invalid.');
    }

    /**
     * Set user.
     * @param User $user
     * @return boolean
     */
    public function setUser($user)
    {
        if ($user instanceof Yii::$app->user->identityClass) {
            $this->_user = $user;
            return true;
        }
        $this->_user = null;
        return false;
    }

    /**
     * Get parent organization or department.
     * @return Organization
     */
    public function getParent()
    {
        if ($this->_parent instanceof Organization) {
            return $this->_parent;
        }
        return null;
    }

    /**
     * Set parent organization or department.
     * If you want to set up organization, please set it null.
     * @param Organization|string|integer $parent
     * @return boolean
     */
    public function setParent($parent)
    {
        if (is_numeric($parent) || is_int($parent)) {
            $class = $this->getUser()->organizationClass;
            $parent = $class::find()->id($parent)->one();
        }
        if ($parent instanceof Organization) {
            $this->_parent = $parent;
            return true;
        }
        $this->_parent = null;
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('organization', 'Name'),
            'nickname' => Yii::t('user', 'Nickname'),
            'gravatar_type' => Yii::t('user', 'Gravatar Type'),
            'gravatar' => Yii::t('user', 'Gravatar'),
            'timezone' => Yii::t('user', 'Timezone'),
            'description' => Yii::t('organization', 'Description'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            [['nickname', 'gravatar', 'description'], 'default', 'value' => ''],
            ['gravatar_type', 'default', 'value' => 0],
            ['timezone', 'default', 'value' => Yii::$app->timeZone],
            [['name', 'nickname', 'gravatar', 'timezone'], 'string', 'max' => 255],
            ['description', 'string', 'max' => 65535],
            ['gravatar_type', 'integer'],
        ];
    }

    /**
     * Set up organization.
     * You need to make sure that the user who want to set up the organization
     * who has the `orgCreator` permission.
     * @return boolean
     */
    public function setUpOrganization()
    {
        return $this->getUser()->setUpOrganization($this->name, $this->nickname, $this->gravatar_type, $this->gravatar, $this->timezone, $this->description);
    }

    /**
     * Set up department.
     * You need to make sure that the user who want to set up the department who
     * is the creator or administrator of parent one.
     * @return boolean
     */
    public function setUpDepartment()
    {
        return $this->getUser()->setUpDepartment($this->name, $this->getParent(), $this->nickname, $this->gravatar_type, $this->gravatar, $this->timezone, $this->description);
    }

    /**
     *
     */
    public function init()
    {
        if(!isset($this->timezone)) {
            $this->timezone = Yii::$app->timeZone;
        }
    }
}

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

namespace rhosocial\organization;

use rhosocial\organization\queries\MemberQuery;
use rhosocial\organization\rbac\roles\DepartmentAdmin;
use rhosocial\organization\rbac\roles\DepartmentCreator;
use rhosocial\organization\rbac\roles\OrganizationAdmin;
use rhosocial\organization\rbac\roles\OrganizationCreator;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class MemberSearch
 * @package rhosocial\organization
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MemberSearch extends Model
{
    /**
     * @var Organization
     */
    public $organization;
    public $memberClass = Member::class;
    public function getQuery()
    {
        $class = $this->memberClass;
        if (empty($class)) {
            return null;
        }
        return $class::find();
    }
    public $memberAlias = 'm_alias';
    public $memberUserAlias = 'u_alias';
    public $profileAlias = 'p_alias';
    public $id;
    public $first_name;
    public $last_name;
    public $nickname;
    public $gender;
    public $position;
    public $description;
    public $role;
    protected $roleInDb;
    /**
     * @var string
     */
    public $createdFrom;
    protected $createdFromInUtc;

    /**
     * @var string
     */
    public $createdTo;
    protected $createdToInUtc;
    /**
     * @var MemberQuery;
     */
    public $query;

    const ROLE_ADMIN = 'admin';
    const ROLE_CREATOR = 'creator';

    public static function getRolesWithEmpty()
    {
        return [
            '' => Yii::t('user', 'All'),
            self::ROLE_ADMIN => Yii::t('organization', 'Administrator'),
            self::ROLE_CREATOR => Yii::t('organization', 'Creator'),
        ];
    }

    public function rules()
    {
        return [
            ['id', 'integer', 'min' => 0],
            [['nickname', 'first_name', 'last_name', 'position', 'description'], 'string', 'max' => 255],
            [['createdFrom', 'createdTo'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm'],
            [['createdFrom', 'createdTo'], 'gmdate'],
            ['gender', 'in', 'range' => array_keys(\rhosocial\user\Profile::getGenderDescsWithEmpty())],
            ['role', 'in', 'range' => array_keys(static::getRolesWithEmpty())],
            ['role', 'judgeRoles'],
        ];
    }

    /**
     * Convert time attribute to UTC time.
     * @param string $attribute
     * @param array $params
     * @param mixed $validator
     */
    public function gmdate($attribute, $params, $validator)
    {
        if (isset($this->$attribute)) {
            $timestamp = strtotime($this->$attribute);
            $this->{$attribute . 'InUtc'} = gmdate('Y-m-d H:i:s', $timestamp);
        }
    }

    /**
     * @param string $attribute
     * @param array $params
     * @param mixed $validator
     */
    public function judgeRoles($attribute, $params, $validator)
    {
        if (isset($this->$attribute)) {
            if ($this->$attribute == self::ROLE_ADMIN) {
                $this->roleInDb = [(new DepartmentAdmin)->name, (new OrganizationAdmin)->name];
            } elseif ($this->$attribute == self::ROLE_CREATOR) {
                $this->roleInDb = [(new DepartmentCreator)->name, (new OrganizationCreator)->name];
            }
        }
    }

    public function init()
    {
        if (!isset($this->query)) {
            $this->query = $this->prepareQuery();
        }
    }

    /**
     * @param MemberQuery $query
     * @return null|MemberQuery
     */
    protected function prepareQuery($query = null)
    {
        if (!$query) {
            $query = $this->getQuery();
        }
        /* @var $query MemberQuery */
        $class = $this->memberClass;
        $query = $query->from("{$class::tableName()} {$this->memberAlias}");
        if (isset($this->organization)) {
            $query = $query->organization($this->organization);
        }
        $noInitMember = new $class;
        /* @var $noInitMember Member */
        $userClass = $noInitMember->memberUserClass;
        $query = $query->joinWith(["memberUser {$this->memberUserAlias}"]);

        $noInitUser = $noInitMember->getNoInitMemberUser();
        $profileClass = $noInitUser->profileClass;
        if (!empty($profileClass)) {
            $query = $query->joinWith(["memberUser.profile {$this->profileAlias}"]);
        }
        return $query;
    }

    public function search($params)
    {
        $query = $this->query;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageParam' => 'member-page',
                'defaultPageSize' => 20,
                'pageSizeParam' => 'member-per-page',
            ],
            'sort' => [
                'sortParam' => 'member-sort',
                'attributes' => [
                    'id' => [
                        'asc' => [$this->memberUserAlias . '.id' => SORT_ASC],
                        'desc' => [$this->memberUserAlias . '.id' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'User ID'),
                    ],
                    'name' => [
                        'asc' => [$this->profileAlias . '.first_name' => SORT_ASC, $this->profileAlias . '.last_name' => SORT_ASC],
                        'desc' => [$this->profileAlias . '.first_name' => SORT_DESC, $this->profileAlias . '.last_name' => SORT_DESC],
                        'default' => SORT_DESC,
                        'label' => Yii::t('user', 'Name'),
                    ],
                    'position',
                    'role',
                    'created_at' => [
                        'asc' => [$this->memberAlias . '.created_at' => SORT_ASC],
                        'desc' => [$this->memberAlias . '.created_at' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Creation Time'),
                    ],
                    'updated_at' => [
                        'asc' => [$this->memberAlias . '.updated_at' => SORT_ASC],
                        'desc' => [$this->memberAlias . '.updated_at' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Last Updated Time'),
                    ],
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query = $query->andFilterWhere([
            'LIKE', $this->memberUserAlias . '.id', $this->id,
        ])->andFilterWhere([
            'LIKE', $this->profileAlias . '.nickname', $this->nickname,
        ])->andFilterWhere([
            'LIKE', $this->profileAlias . '.first_name', $this->first_name,
        ])->andFilterWhere([
            'LIKE', $this->profileAlias . '.last_name', $this->last_name,
        ])->andFilterWhere([
            $this->profileAlias . '.gender' => $this->gender,
        ])->andFilterWhere([
            'LIKE', $this->memberAlias . '.position', $this->position,
        ])->andFilterWhere([
            'LIKE', $this->memberAlias . '.description', $this->description,
        ])->andFilterWhere([
            $this->memberAlias . '.role' => $this->roleInDb,
        ])->andFilterWhere([
            '>=', $this->memberAlias . '.created_at', $this->createdFromInUtc,
        ])->andFilterWhere([
            '<=', $this->memberAlias . '.created_at', $this->createdToInUtc,
        ]);
        $dataProvider->query = $query;
        return $dataProvider;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['id'] = Yii::t('user', 'User ID');
        $attributeLabels['first_name'] = Yii::t('user', 'First Name');
        $attributeLabels['last_name'] = Yii::t('user', 'Last Name');
        $attributeLabels['nickname'] = Yii::t('user', 'Nickname');
        $attributeLabels['gender'] = Yii::t('user', 'Gender');
        $attributeLabels['position'] = Yii::t('organization', 'Member Position');
        $attributeLabels['description'] = Yii::t('organization', 'Description');
        $attributeLabels['role'] = Yii::t('organization', 'Role');
        $attributeLabels['createdFrom'] = Yii::t('user', 'From');
        $attributeLabels['createdTo'] = Yii::t('user', 'To');
        return $attributeLabels;
    }
}

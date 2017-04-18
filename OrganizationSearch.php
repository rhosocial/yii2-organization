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

use rhosocial\organization\queries\OrganizationQuery;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class OrganizationSearch
 * @package rhosocial\organization
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OrganizationSearch extends Model
{
    public $organizationClass = Organization::class;
    public $memberClass = Member::class;
    public function getQuery()
    {
        $class = $this->organizationClass;
        if (empty($class)) {
            return null;
        }
        return $class::find();
    }
    public $organizationAlias = 'o_alias';
    public $memberAlias = 'm_alias';
    public $memberUserAlias = 'u_alias';
    public $profileAlias = 'op_alias';
    public $id;
    public $name;
    public $nickname;
    public $type;
    public $parentId;
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
     * @var OrganizationQuery;
     */
    public $query;

    public function init()
    {
        if (!isset($this->query)) {
            $this->query = $this->prepareQuery();
        }
    }

    /**
     * @return array
     */
    public static function getTypesWithEmpty()
    {
        return [
            '' => Yii::t('user', 'All'),
            Organization::TYPE_ORGANIZATION => Yii::t('organization', 'Organization'),
            Organization::TYPE_DEPARTMENT => Yii::t('organization', 'Department'),
        ];
    }

    public function rules()
    {
        return [
            [['id', 'parentId'], 'integer', 'min' => 0],
            [['name', 'nickname'], 'string'],
            [['createdFrom', 'createdTo'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm'],
            [['createdFrom', 'createdTo'], 'gmdate'],
            ['type', 'in', 'range' => array_keys(static::getTypesWithEmpty())],
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
     * @param OrganizationQuery $query
     * @return null|OrganizationQuery
     */
    protected function prepareQuery($query = null)
    {
        if (!$query) {
            $query = $this->getQuery();
        }
        /* @var $query OrganizationQuery */
        $class = $this->organizationClass;
        $query = $query->from("{$class::tableName()} {$this->organizationAlias}");
        $noInitOrg = new $class;
        /* @var $noInitOrg Organization */
        $profileClass = $noInitOrg->profileClass;
        if (!empty($profileClass)) {
            $query = $query->joinWith(["profile {$this->profileAlias}"]);
        }
        $memberClass = $noInitOrg->memberClass;
        if (!empty($memberClass)) {
            $query = $query->joinWith(["members {$this->memberAlias}"]);
        }
        $memberUserClass = $noInitOrg->getNoInitMember()->memberUserClass;
        if (!empty($memberUserClass)) {
            $query = $query->joinWith(["members.memberUser {$this->memberUserAlias}"]);
        }
        $query = $query->select($this->organizationAlias . '.guid')->distinct()
            ->addSelect([
                $this->organizationAlias . '.id',
                $this->organizationAlias . '.ip',
                $this->organizationAlias . '.ip_type',
                $this->organizationAlias . '.parent_guid',
                $this->organizationAlias . '.created_at',
                $this->organizationAlias . '.updated_at',
                $this->organizationAlias . '.status',
                $this->organizationAlias . '.type',
            ]);
        //In MySQL 5.7 and earlier versions, it is necessary to specify the table name.
        if (!empty($profileClass)) {
            $query = $query->addSelect([
                $this->profileAlias . '.name',
                $this->profileAlias . '.nickname',
            ]);
        }
        return $query;
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->query;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageParam' => 'organization-page',
                'defaultPageSize' => 20,
                'pageSizeParam' => 'organization-per-page',
            ],
            'sort' => [
                'sortParam' => 'organization-sort',
                'attributes' => [
                    'id',
                    'nickname' => [
                        'asc' => [$this->profileAlias . '.nickname' => SORT_ASC],
                        'desc' => [$this->profileAlias . '.nickname' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Nickname'),
                    ],
                    'name',
                    'type' => [
                        'asc' => [$this->organizationAlias . '.type' => SORT_ASC],
                        'desc' => [$this->organizationAlias . '.type' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Type'),
                    ],
                    'created_at' => [
                        'asc' => [$this->organizationAlias . '.created_at' => SORT_ASC],
                        'desc' => [$this->organizationAlias . '.created_at' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Creation Time'),
                    ],
                    'updated_at' => [
                        'asc' => [$this->organizationAlias . '.updated_at' => SORT_ASC],
                        'desc' => [$this->organizationAlias . '.updated_at' => SORT_DESC],
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
            'LIKE', $this->organizationAlias . '.id', $this->id,
        ])->andFilterWhere([
            'LIKE', $this->profileAlias . '.nickname', $this->nickname,
        ])->andFilterWhere([
            '>=', $this->organizationAlias . '.created_at', $this->createdFromInUtc,
        ])->andFilterWhere([
            '<=', $this->organizationAlias . '.created_at', $this->createdToInUtc,
        ])->andFilterWhere([
            'LIKE', $this->profileAlias . '.name', $this->name,
        ])->andFilterWhere([
            $this->organizationAlias . '.type' => $this->type,
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
        $attributeLabels['id'] = Yii::t('user', 'ID');
        $attributeLabels['parentId'] = Yii::t('organization', 'Parent ID');
        $attributeLabels['name'] = Yii::t('organization', 'Name');
        $attributeLabels['nickname'] = Yii::t('user', 'Nickname');
        $attributeLabels['type'] = Yii::t('user', 'Type');
        $attributeLabels['createdFrom'] = Yii::t('user', 'From');
        $attributeLabels['createdTo'] = Yii::t('user', 'To');
        return $attributeLabels;
    }
}

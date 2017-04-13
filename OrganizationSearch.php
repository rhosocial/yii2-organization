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
    public static function find()
    {
        $noInit = new static;
        $class = $noInit->organizationClass;
        if (empty($class)) {
            return null;
        }
        return $class::find();
    }
    public $organizationAlias = 'o_alias';
    public $memberAlias = 'm_alias';
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

    public function search($params)
    {
        $query = static::find();
        /* @var $query OrganizationQuery */
        $class = $this->organizationClass;
        $query = $query->from("{$class::tableName()} {$this->organizationAlias}");
        $noInitOrg = new $class;
        /* @var $noInitOrg Organization */
        $profileClass = $noInitOrg->profileClass;
        if (!empty($profileClass)) {
            $query = $query->joinWith(["profile {$this->profileAlias}"]);
        }
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
                    'nickname',
                    'name',
                    'createdAt' => [
                        'asc' => [$this->organizationAlias . '.created_at' => SORT_ASC],
                        'desc' => [$this->organizationAlias . '.created_at' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => Yii::t('user', 'Creation Time'),
                    ],
                    'updatedAt' => [
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
            'type' => $this->type,
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

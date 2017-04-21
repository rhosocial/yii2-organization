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

use rhosocial\organization\Organization;
use rhosocial\organization\widgets\OrganizationListWidget;
use yii\base\View;
use yii\data\ActiveDataProvider;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $organization Organization */
Pjax::begin([
    'id' => 'organization-children-pjax-' . $organization->getID(),
]);
echo OrganizationListWidget::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => $organization->getChildren(),
        'sort' => [
            'sortParam' => 'organization-children-sort-' . $organization->getID(),
        ],
        'pagination' => [
            'defaultPageSize' => 20,
            'pageParam' => 'organization-children-page-' . $organization->getID(),
            'pageSizeParam' => 'organization-children-per-page-' . $organization->getID(),
        ],
    ]),
    'gridCaption' => $organization->isOrganization() ? Yii::t('organization', 'All subordinates of the current organization:') : Yii::t('organization', 'All subordinates of the current department:'),
    'showParent' => false,
    'showChildren' => false,
    'showCreator' => false,
    'showAdmin' => false,
    'showMember' => false,
    'showType' => false,
    'actionColumn' => null,
    'tips' => false,
]);
Pjax::end();

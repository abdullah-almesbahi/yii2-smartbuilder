<?php

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \backend\components\SearchModel
 * @var $this \yii\web\View
 */

use SmartBuilder\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\icons\Icon;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;
use backend\components\Helper;

$this->params['breadcrumbs'] = [
    [
        'label' => ucfirst(Yii::$app->controller->id),
        'url' => ['index'],
    ],

];
$this->title = Yii::t('app', 'Additional Fields');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-4">
        <?php
//        TreeWidget::widget([
//            'treeDataRoute' => ['getAfTree'],
//            'reorderAction' => 'reorder-af-tree',
//            'contextMenuItems' => [
//                'edit' => [
//                    'label' => 'Edit',
//                    'icon' => 'fa fa-pencil',
//                    'action' => ContextMenuHelper::actionUrl(
//                        ['edit', 'returnUrl' => Helper::getReturnUrl()],
//                        [
//                            'parent_id' => 'parent_id',
//                            'id' => 'id'
//                        ]
//                    ),
//                ],
//                'open' => [
//                    'label' => 'Open',
//                    'icon' => 'fa fa-folder-open',
//                    'action' => ContextMenuHelper::actionUrl(
//                        ['index'],
//                        [
//                            'parent_id' => 'id',
//                        ]
//                    ),
//                ],
//                'create' => [
//                    'label' => 'Create',
//                    'icon' => 'fa fa-plus-circle',
//                    'action' => ContextMenuHelper::actionUrl(
//                        ['edit', 'returnUrl' => Helper::getReturnUrl()],
//                        [
//                            'parent_id' => 'id',
//                        ]
//                    ),
//                ],
//                'delete' => [
//                    'label' => 'Delete',
//                    'icon' => 'fa fa-trash-o',
//                    'action' => new \yii\web\JsExpression(
//                        "function(node) {
//                                jQuery('#delete-confirmation')
//                                    .attr('data-url', 'delete?id=' + jQuery(node.reference[0]).data('id'))
//                                    .attr('data-items', '')
//                                    .modal('show');
//                                return true;
//                            }"
//                    ),
//                ],
//            ],
//        ]);
        ?>
    </div>
    <div class="col-md-8" id="jstree-more">
    <?=

    DynaGrid::widget([
        'columns' => [
            [
                'attribute' => 'name',
                'label' => Yii::t('app', 'Name (Internal)'),
                'order'=>DynaGrid::ORDER_FIX_LEFT,
            ],

            [
                'attribute' => 'title',
                'label' => Yii::t('app', 'Title (For Display)'),
                'order'=>DynaGrid::ORDER_FIX_LEFT,
            ],
            [
                'attribute' => 'sql_type',
                'label' => Yii::t('app', 'Sql Type'),
                'order'=>DynaGrid::ORDER_FIX_LEFT,
            ],
            [
                'attribute' => 'field_type',
                'label' => Yii::t('app', 'Field Type'),
                'order'=>DynaGrid::ORDER_FIX_LEFT,

            ],
            [
                'attribute' => 'validate_func',
                'label' => Yii::t('app', 'Validation function'),
                'order'=>DynaGrid::ORDER_FIX_LEFT,

            ],
            //'create_time:datetime',
            [
                'class' => ActionColumn::className(),
                'options' => [
                    'width' => '100px',
                ],
                'buttons' => [
                    [
                        'url' => 'af-update',
                        'icon' => 'pencil',
                        'class' => 'btn-default',

                        'label' => Yii::t('app', 'Edit'),

                    ],
                    [
                        'url' => 'af-delete',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'data-method' => 'post',
                        'label' => Yii::t('app', 'Delete'),
                    ],
                ],
            ],
        ],
        'options' => [
            'id' => 'users-grid',
        ],
        'storage'=>'cookie',
        //'configView' => '@app/kartik-v/yii2-dynagrid/views/config',
        'showPersonalize'=>true,
        //'minPageSize' => '50',
        'theme' => 'simple-bordered',
        'gridOptions'=>[
            'export' => false,
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'bordered' => false,
            'condensed' => true,
            //'pjax'=>true,
            'panelTemplate' => '<div class="panel {type}">
                    {panelHeading}
                    <div class="panel-body">
                        {panelBefore}
                        {items}
                        {panelAfter}
                    </div>
                    {panelFooter}
                </div>',
            'panelHeadingTemplate' => '
                <h3 class="panel-title">
                    {heading}
                </h3>
                <div class="tools pull-right">
                    <a href="javascript:;" class="collapse"></a>
                    {dynagrid}
                    <a href="javascript:;" class="remove"></a>
                    </div>
                <div class="clearfix"></div>
                ',
            'panel' => [
                'heading' =>  $this->title,
                'after' =>  \SmartBuilder\widgets\RemoveAllButton::widget([
                        'url' => '/backend/user/remove-all',
                        'gridSelector' => '.grid-view',
                        'htmlOptions' => [
                            'class' => 'btn btn-danger'
                        ],
                    ]).'<div class="pull-right">{summary}</div>',

            ],
            'toolbar' =>  [
                ['content'=> \yii\helpers\Html::a('<i class="glyphicon glyphicon-plus"></i>', ['af-update'] , ['data-pjax'=>0, 'class' => 'btn btn-primary', 'title'=>Yii::t('app','Add') ])],
                ['content'=>'{dynagridFilter}{dynagridSort}'],
                '{export}',
            ]
        ]
    ]);
    ?>
    </div>
</div>
<?php

use backend\modules\smartbuilder\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use kartik\icons\Icon;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\EvooySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ucfirst(Yii::$app->controller->id);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="evooy-index">
    <?php

    $columns = [
        [
            'class' => \kartik\grid\CheckboxColumn::className(),
            'options' => [
                'width' => '10px',
            ],
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'id',
            'options' => [
                'width' => '70px',
            ],
        ],
    ];
    $delete_permission = isset($delete_permission)?$delete_permission:'delete';
    if(Yii::$app->user->can($delete_permission) || Yii::$app->user->can("delete ".Yii::$app->controller->table_name)){
        $af_columns[] = [
            'class' => ActionColumn::className(),
            'options' => [
                'width' => '100px',
            ],
            'buttons' => [
                [
                    'url' => 'update',
                    'icon' => 'pencil',
                    'class' => 'btn-default',

                    'label' => Yii::t('app', 'Edit'),

                ],
                [
                    'url' => 'delete',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'data-method' => 'post',
                    'label' => Yii::t('app', 'Delete'),
                ],
            ],
        ];
    }else{
        $af_columns[] = [
            'class' => ActionColumn::className(),
            'options' => [
                'width' => '100px',
            ],
            'buttons' => [
                [
                    'url' => 'update',
                    'icon' => 'pencil',
                    'class' => 'btn-default',

                    'label' => Yii::t('app', 'Edit'),

                ]
            ],
        ];
    }

    //print_r($af_columns);die();

    $f_columns = array_merge($columns,$af_columns);
    echo DynaGrid::widget([
        'columns' => $f_columns,
        'options' => [
            'id' => Yii::$app->controller->table_name.'-grid',
        ],
        'storage'=>'cookie',
        'showPersonalize'=>true,
        'theme' => 'simple-bordered',
        'userSpecific' => true,
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
                'after' =>  \backend\modules\smartbuilder\widgets\RemoveAllButton::widget([
                        'url' => '/backend/user/remove-all',
                        'gridSelector' => '.grid-view',
                        'htmlOptions' => [
                            'class' => 'btn btn-danger'
                        ],
                    ]).'<div class="pull-right">{summary}</div>',

            ],
            'toolbar' =>  [
                ['content'=> \yii\helpers\Html::a('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('app','Add New'), ['update'] , ['data-pjax'=>0, 'class' => 'btn btn-primary', 'title'=>Yii::t('app','Add') ])],
                ['content'=>'{dynagridFilter}{dynagridSort}'],
                '{export}',
            ]
        ]
    ]);
    ?>


</div>

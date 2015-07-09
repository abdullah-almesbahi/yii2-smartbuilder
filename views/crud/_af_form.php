<?php


use backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\web\View;

$inline_radio_options = ['options' => ['class' => 'radio radio-primary radio-inline'], 'template' => '{input}{label}{hint}{error}'];
$radio_options = ['options' => ['class' => 'radio radio-primary'], 'template' => '{input}{label}{hint}{error}'];

/* @var $this yii\web\View */
/* @var $model backend\models\Evooy */
/* @var $form yii\widgets\ActiveForm */
?>
<div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12" xmlns="http://www.w3.org/1999/html"
     xmlns="http://www.w3.org/1999/html">

<?php $form = ActiveForm::begin(['id' => 'form-fields']); ?>
<?php

BackendWidget::begin(
    [
        'icon' => 'user',
        'title' => $model->isNewRecord ?Yii::t('app', 'Add additional field'):Yii::t('app', 'update additional field'),
        //'showLabels' => true,
        'footer' => Html::submitButton(
            Icon::show('save') .( $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update')), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary' , 'name' => 'button' , 'value' => 'update']
        ).Html::submitButton(
                 ( $model->isNewRecord ? Yii::t('app', 'Create & reload') : Yii::t('app', 'Update & reload')), ['class' => $model->isNewRecord ? 'btn btn-success m-l-10 m-r-10' : 'btn btn-primary m-l-10 m-r-10' , 'name' => 'button' , 'value' => 'reload']
            ),
    ]
);
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255])->label('Field Name (ENGLISH) - internal') ?>
<?= $form->field($model, 'title')->textInput(['maxlength' => 255])->label('Field Name') ?>
<?= $form->field($model, 'description')->textarea(['maxlength' => 255])->label('Description about the field'); ?>

<div class="form-group field-af-sql-type">
    <label><?= Yii::t('app', 'SQL Type') ?></label>
<?= $form->field($model, 'sql_type', $inline_radio_options)->radio(
    [
        'value' => 'varchar(255)',
        'id' => 'varchar',
        'uncheck' => null,
        'labelOptions' => ['for' => 'varchar']
    ], false)->label(Yii::t('app', 'String ( VARCHAR(255) )')) ?>
<?= $form->field($model, 'sql_type', $inline_radio_options)->radio(
    [
        'value' => 'blob',
        'id' => 'blob',
        'uncheck' => null,
        'labelOptions' => ['for' => 'blob']
    ], false)->label(Yii::t('app', 'Blob (unlimited length string/data)')) ?>
<?= $form->field($model, 'sql_type', $inline_radio_options)->radio(
    [
        'value' => 'INT',
        'id' => 'INT',
        'uncheck' => null,
        'labelOptions' => ['for' => 'init']
    ], false)->label(Yii::t('app', 'Integer field (only numbers)')) ?>
<?= $form->field($model, 'sql_type', $inline_radio_options)->radio(
    [
        'value' => 'decimal(12,2)',
        'id' => 'DECIMAL(12,2)',
        'uncheck' => null,
        'labelOptions' => ['for' => 'DECIMAL(12,2)']
    ], false)->label(Yii::t('app', 'Numeric field (DECIMAL(12,2))')) ?>
</div>

<div class="form-group field-af-field-type">
    <label><?= Yii::t('app', 'Field Type') ?></label>
<?php
    $field_types = array(
        'text' => Yii::t('app', 'String'),
        'select' => Yii::t('app', 'Select'),
        'multi_select' => Yii::t('app', 'Multi Select'),
        'textarea' => Yii::t('app', 'Text Area'),
        'editor' => Yii::t('app', 'Editor'),
        'radio' => Yii::t('app', 'Radio'),
        'checkbox' => Yii::t('app', 'Checkbox'),
        'date' => Yii::t('app', 'Date'),
        'image' => Yii::t('app', 'Image'),
        'multi_image' => Yii::t('app', 'Multi Images'),
        'custom' => Yii::t('app', 'Custom Field'),
        'array' => Yii::t('app', 'Array'),
    );
    foreach($field_types as $k => $v){
        echo $form->field($model, 'field_type', $inline_radio_options)->radio(
            [
                'value' => $k,
                'id' => $k,
                'uncheck' => null,
                'onclick' => 'switch_layers(this.value)',
                'labelOptions' => ['for' => $k]
            ], false)->label($v);
    }

?>
</div>

<?= $form->field($model, 'options',['options' => ['style' => 'display:none;','id'=>'values']])->textarea(['maxlength' => 1000])->hint(Yii::t('app','This list displays pipe-separated<br>
                    list of field keys (internal values),<br>
                    values (human-readable) and<br>
                    default value indicators<br>
                    (1-default value, 0-not default value)<br>
                    For example, the following lines will<br>
                    create country list, where USA<br>
                    is selected by default:<br>
                    USA|United States|1<br>
                    UK|United Kingdom|0<br>
                    CA|Canada|0<br>')); ?>
<div id="show_time" style='display: none;' dir="ltr">
    <?= $form->field($model, 'show_time')->textInput(['maxlength' => 255]) ?>
</div>

<?= $form->field($model, 'sql_query',['options' => ['style' => 'display:none;','id'=>'sql_query']])->textInput() ?>
<?= $form->field($model, 'size',['options' => ['style' => 'display:none;','id'=>'size']])->textInput(['maxlength' => 2550]) ?>
<div id=textarea_size style='display: none;'>
    <?= $form->field($model, 'cols')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'rows')->textInput(['maxlength' => 255]) ?>
</div>
<div id="image_size" style='display: none;'>
    <?= $form->field($model, 'width')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'width2')->textInput(['maxlength' => 255]) ?>
</div>
<?= $form->field($model, 'default',['options' => ['style' => 'display:none;','id'=>'text_default']])->textInput(['maxlength' => 255]) ?>
<div id=image style='display: none;'>
    image
    <?php //$form->field($model, 'image')->textInput(['maxlength' => 255]) ?>
</div>
<div id="custom" style='display: none;'>
    <?php // $form->field($model, 'custom')->textInput(['maxlength' => 255]) ?>
</div>

<div class="form-group field-af-display">
    <label><?= Yii::t('app', 'Display Field In frontend') ?></label>
    <?php
    $validate_funcs = array(
        'no_edit' => Yii::t('app', 'Prevent editing'),
        'signup' => Yii::t('app', 'Display in signup Page'),
        'profile' => Yii::t('app', 'Display in Profile Page'),
        'all_pages' => Yii::t('app', 'Display in All Other Pages'),
    );
    echo \kartik\select2\Select2::widget([
        'model' => $model,
        'attribute' => 'display',
        'data' => $validate_funcs,
        'options' => ['placeholder' =>  Yii::t('app', 'Select display options.'),'multiple'=>true],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]);
//    foreach($validate_funcs as $k => $v){
//        echo $form->field($model, 'display', $inline_radio_options)->radio(
//            [
//                'value' => $k,
//                'id' => $k,
//                'uncheck' => null,
//                'labelOptions' => ['for' => $k]
//            ], false)->label($v);
//    }
    ?>
</div>

<div class="form-group field-af-admin-display">
    <label><?= Yii::t('app', 'Allow to Display Field In Backend for/in:-') ?></label>
    <?php
    $admin_display = array(
        'index' => Yii::t('app', 'Display in managing table page'),
        'update' => Yii::t('app', 'Display in edit page'),
    );
    echo \kartik\select2\Select2::widget([
        'model' => $model,
        'attribute' => 'admin_display',
        'data' => $admin_display,
        'options' => ['placeholder' =>  Yii::t('app', 'Select where to display this field.'),'multiple'=>true],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]);
    ?>
</div>

<div class="form-group field-af-validate-func">
    <label><?= Yii::t('app', 'Validate function') ?></label>
    <?php
    $validate_funcs = array(
        'none' => Yii::t('app', 'No validation'),
        'require' => Yii::t('app', 'Required value'),
        'integer' => Yii::t('app', 'Integer value'),
        'number' => Yii::t('app', 'Numeric value'),
        'email' => Yii::t('app', 'Email value'),
    );
    foreach($validate_funcs as $k => $v){
        echo $form->field($model, 'validate_func', $inline_radio_options)->radio(
            [
                'value' => $k,
                'id' => $k,
                'uncheck' => null,
                'labelOptions' => ['for' => $k]
            ], false)->label($v);
    }
    ?>
</div>

<div class="field-af-validate-func m-b-10">
    <?=
         $form->field($model, 'enable_condition',['options' => ['class' => 'checkbox check-primary'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
        [
            'value' => '1',
            'id' => 'enable_conditional',
            'uncheck' => null,
            'labelOptions' => ['for' => 'enable_conditional']
        ], false)->label(Yii::t('app', 'Enable Conditional Logic'));
    ?>
    <div id="enable_conditional_content" style="display: none;">

    <table class="table table-bordered" id="table_conditional">
        <thead>
        <tr>
            <th width="300"><?= Yii::t('app', 'Condition Status') ?></th>
            <th><?= Yii::t('app', 'Conditional logical') ?></th>
            <th width="400"><?= Yii::t('app', 'Actions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        //Loading default values if not set to avoid warning message
        $model->c_action = !is_array($model->c_action)?['0'=> \Yii::t('app','-- action --')]:$model->c_action;
        $model->c_if = !is_array($model->c_if)?['0'=> \Yii::t('app','all')]:$model->c_if;
        $model->c_table = !is_array( $model->c_table)?['0'=> \Yii::t('app','all')]:$model->c_table;
        $model->c_field = !is_array( $model->c_field)?['0'=> \Yii::t('app','all')]:$model->c_field;
        $model->c_option = !is_array( $model->c_option)?['0'=> \Yii::t('app','all')]:$model->c_option;
        $model->c_template = !is_array( $model->c_template)?['0'=> \Yii::t('app','all')]:$model->c_template;
        $model->c_user = !is_array( $model->c_user)?['0'=> \Yii::t('app','all')]:$model->c_user;

        $c_action_options = [
            '0' => \Yii::t('app','-- Action --'),
            'update_field' => \Yii::t('app','Update Field'),
            'prevent' => \Yii::t('app','Prevent'),
            'email' => \Yii::t('app','Send Email'),
            'sms' => \Yii::t('app','Send SMS'),
            'hide' => \Yii::t('app','Hide'),
            'show' => \Yii::t('app','Show'),
        ];
        $c_if = [
            'logicalAnd' => \Yii::t('app','All'),
            'logicalOr' => \Yii::t('app','Any'),
        ];

        $c_condition = [
            'equalTo' => Yii::t('app', 'Equal To'),
            'notEqualTo' => Yii::t('app', 'Not Equal To'),
            'greaterThan' => Yii::t('app', 'Greater Than'),
            'greaterThanOrEqualTo' => Yii::t('app', 'Greater Than Or Equal To'),
            'lessThan' => Yii::t('app', 'Less Than'),
            'lessThanOrEqualTo' => Yii::t('app', 'Less Than Or Equal To'),
            'stringContains' => Yii::t('app', 'Contain'),
            'stringDoesNotContain' => Yii::t('app', 'Does Not Contain'),
            'stringContainsInsensitive' => Yii::t('app', 'Contain (Insensitive)'),
            'stringDoesNotContainInsensitive' => Yii::t('app', 'Does Not Contain (Insensitive)'),
            'startsWith' => Yii::t('app', 'Start with'),
            'startsWithInsensitive' => Yii::t('app', 'Start with (Insensitive)'),
            'endsWith' => Yii::t('app', 'End with'),
            'endsWithInsensitive' => Yii::t('app', 'End with (Insensitive)'),
            'sameAs' => Yii::t('app', 'Same as'),
            'notSameAs' => Yii::t('app', 'Not same as'),
        ];

        foreach($model->c_action as $k => $v):
        ?>
        <tr>
            <td>
                <div class="form-inline">
                        <?= $form->field($model,"c_action[{$k}]")->dropDownList( $c_action_options , ['style' => 'width:150px;','class' => 'actions'])->label(Yii::t('app', 'Do')); ?>
                        <?= $form->field($model,"c_if[{$k}]")->dropDownList($c_if , ['style' => 'width:80px;','class' => 'if'])->label(Yii::t('app', 'IF')); ?>
                        <label> <?= Yii::t('app', 'Following conditions match:-') ?></label>
                </div>

            </td>
            <td>

                <?php

                    $model->c_condition = !is_array( $model->c_condition)?['0'=> ['0' => 'equalto']]:$model->c_condition;
                    $model->c_value = !is_array( $model->c_value)?[]:$model->c_value;
                    foreach($model->c_condition[$k] as $kk => $vv):
                        echo '<div class="form-inline">';
                        echo $form->field($model,"c_condition[{$k}][$kk]")->dropDownList( $c_condition , ['style' => 'width:150px;','class' => 'c_condition'])->label(Yii::t('app', 'This field value'));
                        echo $form->field($model,"c_value[{$k}][$kk]")->textInput( ['style' => 'width:150px;','class' => 'c_value'])->label(\Yii::t('app','Value'));
                        echo '
                                <a class="delete_field_choice remove-rule m-t-30" title="remove this rule"><i class="fa fa-minus-square fa-lg"></i></a>
                        </div>';
                    endforeach;
                ?>
                <button class="btn btn-info btn-xs btn-mini add-rule" type="button"><i class="fa fa-plus-square fa-lg"></i></button>
            </td>
            <td>
                <div class="extra_options form-inline">
                    <?php
                    //print_r($tables);die();
                    switch($model->c_action[$k]){
                        case 'update_field':
                            echo $form->field($model,"c_table[{$k}]")->dropDownList(
                                array_merge(['0' => \Yii::t('app','-- choose --')],\yii\helpers\ArrayHelper::map($tables, 'table', 'table')) ,
                                ['style' => 'width:100px;','class' => 'c_table'])->label(Yii::t('app', 'Table')
                            );

                            echo $form->field($model,"c_field[{$k}]")->dropDownList( [] , ['style' => 'width:100px;','class' => 'c_field', 'data-selected' => isset($model->c_field[$k])?$model->c_field[$k]:0])->label(Yii::t('app', 'Field'));
                            echo $form->field($model,"c_option[{$k}]")->textInput( ['style' => 'width:150px;','class' => 'c_value'])->label(\Yii::t('app','Value'));
                            break;
                        case 'prevent':
                            echo $form->field($model,"c_option[{$k}]")->textarea()->label(Yii::t('app', 'Prevent Reason'));
                            break;
                        case 'sms':
                        case 'email':
                            echo $form->field($model,"c_template[{$k}]")->dropDownList(
                                array_merge(['0' => '-- choose --'],\yii\helpers\ArrayHelper::map($templates, 'id', 'name')),
                                ['style' => 'width:100px;','class' => 'c_template'])->label(Yii::t('app', 'Send This')
                            );
                            echo $form->field($model,"c_user[{$k}]")->dropDownList( ['user' => 'User','other' => 'Other'] , ['style' => 'width:100px;','class' => 'c_user'])->label(Yii::t('app', 'To'));
                            echo $form->field($model,"c_option[{$k}]" , ['options' => ['class' => 'hide check-hide']])->textInput( ['style' => 'width:150px;','class' => 'c_option'])->label(\Yii::t('app','Email'));
                            break;
                    }
                    ?>

                </div>
                <button type="button" class="remove-condition btn btn-danger btn-xs btn-mini"><?= Yii::t('app', 'Remove') ?></button>
            </td>
        </tr>
        <?php
        endforeach;
        ?>
        </tbody>
    </table>
    <button class="btn btn-info btn-xs btn-mini" type="button" id="add_new_action"><?= Yii::t('app', 'Add New Condition') ?></button>
    </div>
</div>

<!-- Workflow form start -->
<!-- Only display Workflow form if this field is status -->
<?php if($model->name == 'status' || $model->name == 'status_ex'):  ?>
<div class="field-af-workflow-func m-b-10">
    <?=
         $form->field($model, 'enable_workflow',['options' => ['class' => 'checkbox check-primary'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
        [
            'value' => '1',
            'id' => 'enable_workflow',
            'uncheck' => null,
            'labelOptions' => ['for' => 'enable_workflow']
        ], false)->label(Yii::t('app', 'Enable Workflow Engine'));
    ?>
    <div id="enable_workflow_content" style="display: none;">
    <div class="alert alert-info"><?= \Yii::t('app','Note : You need to save the page so you can select workflow status if it was empty , Do the same thing if you add new status')?></div>
    <?= $form->field($model,"wf_initial")->textInput( ['style' => 'width:150px;','class' => 'wf_initial'])->label(\Yii::t('app','Initial Status')); ?>
    <table class="table table-bordered" id="table_workflow">
        <thead>
        <tr>
            <th width="200"><?= Yii::t('app', 'User Roles') ?></th>
            <?php if($model->name == 'status'): ?>
            <th><?= Yii::t('app', 'Fields in table') ?></th>
            <th><?= Yii::t('app', 'Fields in form') ?></th>
            <th><?= Yii::t('app', 'Fields For viewing Only') ?></th>
            <?php endif; ?>

            <th width="400"><?= Yii::t('app', 'Workflow status') ?></th>

            <?php if($model->name == 'status_ex'): ?>
            <th><?= Yii::t('app', 'Validate with Primary Status') ?></th>
            <?php endif; ?>

            <?php if($model->name == 'status'): ?>
            <th><?= Yii::t('app', 'Options') ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php

        if ($model->isNewRecord || empty($model->options)) {
            $workflow_field = [];
        }else{
            preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $model->options, $regs);
            $workflow_field = array();
            foreach ($regs[1] as $i => $k) {
                $workflow_field[$k] = $regs[2][$i];
            }
        }

        $i=0;
        foreach(\Yii::$app->getAuthManager()->getRoles() as $k => $v):
        ?>
        <tr>
            <td>
                <div class="form-inline">
                        <label> <?= (!empty($v->description))?$v->description:$v->name; ?></label>
                </div>

            </td>
            <?php if($model->name == 'status'): ?>
            <td>
                <?php
                echo \kartik\select2\Select2::widget([
                    'model' => $model,
                    'attribute' => "wf_field_index[{$k}]",
                    'data' => \yii\helpers\ArrayHelper::map($all_fields_index, 'id' , 'title'),
                    'options' => ['placeholder' =>  Yii::t('app', 'Select fields to display in table.'),'multiple'=>true],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);
                ?>
            </td>
            <td>
                <div class="wf_field_update-wrapper form-inline">
                    <?php
                    echo \kartik\select2\Select2::widget([
                        'model' => $model,
                        'attribute' => "wf_field_update[{$k}]",
                        'data' => \yii\helpers\ArrayHelper::map($all_fields_update, 'id' , 'title'),
                        'options' => ['placeholder' =>  Yii::t('app', 'Select fields to display in form.'),'multiple'=>true],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]);
                    //print_r($model->wf_enable_md);die();
                    if(isset($model->wf_enable_md[$k]) && $model->wf_enable_md[$k] == 1) {
                        echo "<hr />";
                        if (isset($model->wf_definition_from[$k]) && is_array($model->wf_definition_to)) {
                            //print_r($model->wf_definition_from[$k]);print_r($workflow_field);die();
                            foreach ($model->wf_definition_from[$k] as $kk => $vv):
                                if (!isset($workflow_field[$vv])) {
                                    continue;
                                }
                                //foreach($vv as $kkk => $vvv):
                                echo '<div class="form-inline">';
                                //print_r($workflow_field);die($vv);

                                echo $workflow_field[$vv];
                                echo \kartik\select2\Select2::widget([
                                    'model' => $model,
                                    'attribute' => "wf_fields_md[{$k}][{$kk}]",
                                    'data' => \yii\helpers\ArrayHelper::map($all_fields_update, 'id', 'title'),
                                    'options' => ['placeholder' => Yii::t('app', 'Select fields to display in form.'), 'multiple' => true],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]);
                                echo ' </div>';
                                echo $form->field($model, "wf_ignore_md[{$k}][$kk]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                                    [
                                        'value' => 1,
                                        'id' => "wf_ignore_md_{$k}_" . $vv,
                                        'uncheck' => null,
                                        'labelOptions' => ['for' => "wf_ignore_md_{$k}" . $vv]
                                    ], false)->label(\Yii::t('app', 'Ignore?'));
                                //endforeach;
                            endforeach;
                        }
                    }


                    ?>
                </div>
            </td>
            <td>

                <?php
                echo \kartik\select2\Select2::widget([
                    'model' => $model,
                    'attribute' => "wf_field_view[{$k}]",
                    'data' => \yii\helpers\ArrayHelper::map($all_fields_update, 'id' , 'title'),
                    'options' => ['placeholder' =>  Yii::t('app', 'Select fields for viewing only in form.'),'multiple'=>true],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);
                ?>
            </td>
            <?php endif; ?>
            <td>
                <div class="workflow-wrapper form-inline">
                    <?php if (!$model->isNewRecord || !empty($model->options)) :?>
                        <?php
                        echo $form->field($model, "wf_definition_initial[{$k}]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                            [
                                'value' => 1,
                                'id' => "wf_definition_initial_{$k}",
                                'uncheck' => null,
                                'labelOptions' => ['for' => "wf_definition_initial_{$k}"]
                            ], false)->label(\Yii::t('app','Initial Status'));
                        ?>
                        <?php

                        if(!isset($model->wf_definition_from[$k]) && is_array($model->wf_definition_from)){
                            $model->wf_definition_from = array_merge($model->wf_definition_from,[$k=> ['0' => '']]);
                        }elseif(!isset($model->wf_definition_from[$k]) && !is_array($model->wf_definition_from)){
                            $model->wf_definition_from = [$k=> ['0' => '']];
                        }

                        $model->wf_definition_to = !is_array( $model->wf_definition_to)?'':$model->wf_definition_to;
                        foreach($model->wf_definition_from[$k] as $kk => $vv):
                        echo '<div class="form-inline">';
                            echo $form->field($model,"wf_definition_from[{$k}][$kk]")->dropDownList( array_merge([''=>\Yii::t('app','Please select')],$workflow_field) , ['style' => 'width:150px;','class' => 'wf_definition'])->label(Yii::t('app', 'From'));
                            echo $form->field($model,"wf_definition_to[{$k}][$kk]")->textInput( ['style' => 'width:150px;','class' => 'wf_transtition'])->label(\Yii::t('app','Transitions'));
                            echo '
                            <a class="delete_field_choice remove-workflow m-t-60" title="remove this rule"><i class="fa fa-minus-square fa-lg"></i></a>
                        </div>';
                        endforeach;
                    endif; ?>
                    <button class="btn btn-info btn-xs btn-mini add-workflow" type="button"><i class="fa fa-plus-square fa-lg"></i></button>
                </div>
            </td>

            <?php if($model->name == 'status_ex'): ?>
            <td>
                <div class="workflow-wrapper form-inline">
                    <?php if (!$model->isNewRecord || !empty($model->options)) :?>

                        <?php

                        if(!isset($model->wf_definition_from[$k])){
                            $model->wf_definition_from = array_merge($model->wf_definition_from,[$k=> ['0' => '']]);
                        }

                        $model->wf_definition_to = !is_array( $model->wf_definition_to)?'':$model->wf_definition_to;

                        $status = (new \backend\models\AF($model->table))->getStatusField();
                        $status['wf_validate_from'] = is_array($status['wf_validate_from'])?$status['wf_validate_from']:unserialize($status['wf_validate_from']);
                        $status['wf_validate_to'] = is_array($status['wf_validate_to'])?$status['wf_validate_to']:unserialize($status['wf_validate_to']);
                        if(!isset($status['wf_validate_from'][$k])){
                            $status['wf_validate_from'] = [$k=> ['0' => '']];
                        }
                        preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $status['options'], $_regs);
                        $p_workflow_field = array();
                        foreach ($_regs[1] as $__i => $__k) {
                            $p_workflow_field[$__k] = $_regs[2][$__i];
                        }
                        //print_r($model->wf_definition_from);die($k);
                        foreach($model->wf_definition_from[$k] as $kk => $vv):
                        echo '<div class="form-inline">';
                            echo $form->field($model,"wf_definition_from[{$k}][$kk]")->dropDownList( array_merge([''=>\Yii::t('app','Please select')],$workflow_field) , ['style' => 'width:150px;','class' => 'wf_definition'])->label(Yii::t('app', 'From'));
                            echo $form->field($model,"wf_definition_to[{$k}][$kk]")->textInput( ['style' => 'width:150px;','class' => 'wf_transtition'])->label(\Yii::t('app','Transitions'));
                            ?>
                            <div class="well">
                                <strong>Related validation with Primary status:</strong><br /><br />
                                <?php

                                if(isset($model->wf_validate_from[$k])){
                                    foreach($model->wf_validate_from[$k] as $kk2 => $vv2):
                                        //get a key of array

                                        if($vv == $kk2){
                                            foreach($vv2 as $related_key => $related_value):
                                                ?><div class="form-inline"><?php
                                                echo $form->field($model,"wf_validate_from[{$k}][$vv][{$related_key}]")->dropDownList( array_merge([''=>\Yii::t('app','Please select')],$p_workflow_field) , ['style' => 'width:150px;','class' => 'wf_definition'])->label(Yii::t('app', 'From'));
                                                echo $form->field($model,"wf_validate_to[{$k}][$vv][{$related_key}]")->textInput( ['style' => 'width:150px;','class' => 'wf_transtition'])->label(\Yii::t('app','Transitions'));
                                                ?>
                                                <a class="delete_field_choice remove-workflow-related m-t-60" title="remove this rule"><i class="fa fa-minus-square fa-lg"></i></a>
                                                </div><?php
                                            endforeach;
                                        }
                                    endforeach;
                                }
                                ?>
                                <button class="btn btn-info btn-xs btn-mini add-workflow-related" type="button"><i class="fa fa-plus-square fa-lg"></i></button>
                            </div>
                            <?php
                        echo '</div>';
                        endforeach;
                    endif; ?>

                </div>
            </td>
            <?php endif; ?>

            <?php if($model->name == 'status'): ?>
            <td>

                <?php
                echo $form->field($model, "wf_view_all[{$k}]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                    [
                        'value' => 1,
                        'id' => "wf_view_all_{$k}",
                        'uncheck' => null,
                        'labelOptions' => ['for' => "wf_view_all_{$k}"]
                    ], false)->label(\Yii::t('app','Ability To VIEW ONLY All Results in any status.'));
                echo $form->field($model, "wf_view_by_owner[{$k}]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                    [
                        'value' => 1,
                        'id' => "wf_view_by_owner_{$k}",
                        'uncheck' => null,
                        'labelOptions' => ['for' => "wf_view_by_owner_{$k}"]
                    ], false)->label(\Yii::t('app','Ability To VIEW ONLY All Results Added by this user'));
                echo $form->field($model, "wf_view_audit_trail[{$k}]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                    [
                        'value' => 1,
                        'id' => "wf_view_audit_trail_{$k}",
                        'uncheck' => null,
                        'labelOptions' => ['for' => "wf_view_audit_trail_{$k}"]
                    ], false)->label(\Yii::t('app','Ability To View Audit trail'));
                echo $form->field($model, "wf_assign[{$k}]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                    [
                        'value' => 1,
                        'id' => "wf_assign_{$k}",
                        'uncheck' => null,
                        'labelOptions' => ['for' => "wf_assign_{$k}"]
                    ], false)->label(\Yii::t('app','Ability To Assign record to an agent and hide from other users in same role group'));
                echo $form->field($model, "wf_enable_md[{$k}]", ['options' => ['class' => 'checkbox check-primary m-l-20'], 'template' => '{input}{label}{hint}{error}'])->checkbox(
                    [
                        'value' => 1,
                        'id' => "wf_enable_md_{$k}",
                        'uncheck' => null,
                        'labelOptions' => ['for' => "wf_enable_md_{$k}"]
                    ], false)->label(\Yii::t('app','Ability To display fields on specific transition.'));
                ?>

            </td>
            <?php endif; ?>
        </tr>
        <?php
        $i++;
        endforeach;
        ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>
<!-- Workflow form end-->

<input type="radio" name="sql" value="Y" checked="checked" style="border: none; display: none;">

<?php
BackendWidget::end();
ActiveForm::end();

$script = <<< JS

    function autoLoadFieldsValues(t){
         $.ajax({
            type: 'post',
            case: 'false',
            dataType: 'json',
            url: 'af-get-all-fields',
            data:  {'table':$(t).val()},
            success  : function(response) {
                if(true === response.success){
                    var sel = $(t).closest('.extra_options').find('.c_field');
                    sel.html('');
                    selected = sel.data('selected');
                    for(key in response.data) {
                        if(selected == key){
                            $('<option>').text(response.data[key]).val(key).prop( "selected", true ).appendTo(sel);
                        }else{
                            $('<option>').text(response.data[key]).val(key).appendTo(sel);
                        }
                    }
                }
            }
        });
    }

    function autoLoadUserValues (t){
        if($(t).val() == 'other'){
            $(t).closest('.extra_options').find('.check-hide').removeClass('hide');
        }else{
            $(t).closest('.extra_options').find('.check-hide').addClass('hide');
        }
    }
    function updateExtraFields (t){
         var content = $(t).closest('tr').find('.extra_options');
            content.html($('#'+$(t).val()+'_actions').html());

            var newCondition = jQuery("#table_conditional tr:last");
            var ReplaceWith = $(newCondition).find('select').attr("name").match(/\d+(?!.*\d+)/)*1 ;

            $(content).find('input[type="text"], select , textarea').each(function(){
                var attr_name = $(this).attr('name');
                // For some browsers, `attr` is undefined; for others,
                // `attr` is false.  Check for both.
                if (typeof attr_name !== 'undefined' && attr_name !== false){
                    var pattren = /AF\[c_\w+\]\[(\d+)\]/;
                    var string = $(this).attr('name');

                    $(this).attr('name', string.replace(pattren,  function(original , group){
                        return original.replace(group,ReplaceWith);
                    }));
                }
            });
    }

    jQuery(document).ready(function(){

        /**
        * Condition logic
        **/

        //toggle Conditional logic
        jQuery('#enable_conditional').click(function(){
            $( '#enable_conditional_content' ).slideToggle( 'slow', function() {});
        });

        //show Conditional logic on loading page if Conditional logic is enabled
        if (jQuery('#enable_conditional').is(':checked')){
             $('#enable_conditional_content' ).slideToggle( 'slow', function() {});
        }

        //create new condition
        jQuery('#add_new_action').click(function(){
            var newCondition = jQuery("#table_conditional tr:last");
            var ReplaceWith = $(newCondition).find('select').attr("name").match(/\d+(?!.*\d+)/)*1 + 1;

            jQuery('#table_conditional tr:last').after( $('#content_actions').html());
            var content = jQuery("#table_conditional tr:last");

            $(content).find('input[type="text"], select').each(function(){
                var attr_name = $(this).attr('name');
                // For some browsers, `attr` is undefined; for others,
                // `attr` is false.  Check for both.
                if (typeof attr_name !== 'undefined' && attr_name !== false){
                    var pattren = /AF\[c_\w+\]\[(\d+)\]/;
                    var string = $(this).attr('name');

                    $(this).attr('name', string.replace(pattren,  function(original , group){
                        return original.replace(group,ReplaceWith);
                    }));
                }
            });

            updateExtraFields(content.find('.actions'));

        });

        //remove existing condition
        jQuery('#table_conditional').on('click','.remove-condition' ,function(){
                var target = jQuery(this).closest('tr');
                target.addClass('danger');
                target.hide('slow', function(){ target.remove(); });
        });

        //adding rule
        jQuery('#table_conditional').on('click','.add-rule' ,function(){
            var newCondition = jQuery(this).closest('td').find('.form-inline:last').clone(true, true);
            var conditionCount1 = $(newCondition).find('input[type="text"]').attr("name").match(/\d+(?!.*\d+)/)*1 + 1;
            jQuery(this).closest('td').find('.form-inline:last').after(newCondition);

            $(newCondition).find('input[type="text"], select').each(function(){
                var attr_name = $(this).attr('name');

                // For some browsers, `attr` is undefined; for others,
                // `attr` is false.  Check for both.
                if (typeof attr_name !== 'undefined' && attr_name !== false)
                    $(this).attr('name', $(this).attr('name').replace(/\d+(?!.*\d+)/, conditionCount1) );
            });

        });

        //removing rule
        jQuery('#table_conditional').on('click','.remove-rule' , function(){
               jQuery(this).closest('div').remove();
        });

        //on action changed , display extra fields
        $('#table_conditional').on('change','.actions', function() {
           updateExtraFields(this);
        });

        //on load , auto select values
        $( ".c_table" ).each(function( index ) {
            autoLoadFieldsValues(this);
        });
        $( ".c_user" ).each(function( index ) {
            autoLoadUserValues(this);
        });

        //on selecting table , display table fields
        $('#table_conditional').on('change','.c_table', function() {
            autoLoadFieldsValues(this);
        });

        //on selecting other option to send mail to , then show email field
        $('#table_conditional').on('change','.c_user', function() {
            autoLoadUserValues(this);
        });



        /**
        * Workflow engine
        **/

        //toggle workflow
        jQuery('#enable_workflow').click(function(){
            $( '#enable_workflow_content' ).slideToggle( 'slow', function() {});
        });

        //show Workflow on loading page if workflow is enabled
        if (jQuery('#enable_workflow').is(':checked')){
             $('#enable_workflow_content' ).slideToggle( 'slow', function() {});
        }

        //adding workflow
        jQuery('#table_workflow').on('click','.add-workflow' ,function(){
            var newCondition = jQuery(this).closest('td').find('.form-inline:last').clone(true, true);
            var conditionCount1 = $(newCondition).find('input[type="text"]').attr("name").match(/\d+(?!.*\d+)/)*1 + 1;
            jQuery(this).closest('td').find('.form-inline:last').after(newCondition);

            $(newCondition).find('input[type="text"], select').each(function(){
                var attr_name = $(this).attr('name');

                // For some browsers, `attr` is undefined; for others,
                // `attr` is false.  Check for both.
                if (typeof attr_name !== 'undefined' && attr_name !== false)
                    $(this).attr('name', $(this).attr('name').replace(/\d+(?!.*\d+)/, conditionCount1) );
            });

        });

        //removing workflow
        jQuery('#table_workflow').on('click','.remove-workflow' , function(){
               jQuery(this).closest('div').remove();
        });

        //adding workflow related
        jQuery('#table_workflow').on('click','.add-workflow-related' ,function(){
            var newCondition = jQuery(this).closest('.well').find('.form-inline:last').clone(true, true);
            var conditionCount1 = $(newCondition).find('input[type="text"]').attr("name").match(/\d+(?!.*\d+)/)*1 + 1;
            jQuery(this).closest('.well').find('.form-inline:last').after(newCondition);

            $(newCondition).find('input[type="text"], select').each(function(){
                var attr_name = $(this).attr('name');

                // For some browsers, `attr` is undefined; for others,
                // `attr` is false.  Check for both.
                if (typeof attr_name !== 'undefined' && attr_name !== false)
                    $(this).attr('name', $(this).attr('name').replace(/\d+(?!.*\d+)/, conditionCount1) );
            });

        });

        //removing workflow related
        jQuery('#table_workflow').on('click','.remove-workflow-related' , function(){
               jQuery(this).closest('div').remove();
        });


    });

    var DHTML = (document.getElementById || document.all || document.layers);

    function showLayer(name,visibility)
    {
        if (!DHTML) return;
        if (name)
        {
            var x = getObj(name);
            x.display = visibility ? '' : 'none';
        }
    }
    function getObj(name)
    {
      if (document.getElementById)
      {
        return document.getElementById(name).style;
      }
      else if (document.all)
      {
        return document.all[name].style;
      }
      else if (document.layers)
      {
        return document.layers[name];
      }
      else return false;
    }
    function switch_layers(type){
        switch (type){
            case 'text':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 1);
                showLayer('textarea_size', 0);
                showLayer('text_default', 1);
                showLayer('image', 0);
                showLayer('custom', 0);
                showLayer('image_size', 0);
                showLayer('show_time', 0);
                back_sql_types();
                break;
            case 'date':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 1);
                showLayer('textarea_size', 0);
                showLayer('text_default', 1);
                showLayer('image', 0);
                showLayer('custom', 0);
                showLayer('image_size', 0);
                showLayer('show_time', 1);
                back_sql_types();
                break;
            case 'editor':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 1);
                showLayer('textarea_size', 0);
                showLayer('text_default', 1);
                showLayer('image', 0);
                showLayer('custom', 0);
                showLayer('image_size', 0);
                showLayer('show_time', 0);
                back_sql_types();
                break;
            case 'textarea':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 0);
                showLayer('textarea_size', 1);
                showLayer('text_default', 1);
                showLayer('image', 0);
                showLayer('custom', 0);
                    showLayer('image_size', 0);
                showLayer('show_time', 0);
                clear_sql_types();
                break;
                break;
            case 'multi_select':
            case 'select':
                showLayer('sql_query', 1);
                showLayer('values', 1);
                showLayer('size', 1);
                showLayer('textarea_size', 0);
                showLayer('text_default', 0);
                showLayer('image', 0);
                showLayer('custom', 0);
                    showLayer('image_size', 0);
                showLayer('show_time', 0);
                clear_sql_types();
                break;
            case 'checkbox':
            case 'radio':
                showLayer('sql_query', 0);
                showLayer('values', 1);
                showLayer('size', 0);
                showLayer('textarea_size', 0);
                showLayer('text_default', 0);
                showLayer('image', 0);
                showLayer('custom', 0);
                    showLayer('image_size', 0);
                showLayer('show_time', 0);
                clear_sql_types();
                break;
            break;
            case  'multi_image':
            case 'image':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 0);
                showLayer('textarea_size', 0);
                showLayer('text_default', 0);
                showLayer('image', 1);
                showLayer('custom', 0);
                    showLayer('image_size', 1);
                showLayer('show_time', 0);
                clear_sql_types();
                break;
            case 'custom':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 0);
                showLayer('textarea_size', 0);
                showLayer('text_default', 0);
                showLayer('image', 0);
                showLayer('custom', 1);
                    showLayer('image_size', 0);
                showLayer('show_time', 0);
                clear_sql_types();
                break;
             case 'array':
                showLayer('sql_query', 0);
                showLayer('values', 0);
                showLayer('size', 1);
                showLayer('textarea_size', 0);
                showLayer('text_default', 1);
                showLayer('image', 0);
                showLayer('custom', 0);
                showLayer('image_size', 0);
                showLayer('show_time', 0);
                back_sql_types();
                break;
        }
        if (type == 'checkbox' || type == 'multi_select'){
            toggle_sql_type(false);
        } else {
            toggle_sql_type(true);
        }
    }



    frm = jQuery('#form-fields');


    elem = frm.find('input[name="AF[field_type]"]');
    for (i=0;i<frm.find('input[name="AF[field_type]"]').length;i++)
        if (frm.find('input[name="AF[field_type]"]')[i].checked)
            switch_layers(elem[i].value);
    elem = frm.find('input[name="AF[sql]"]');
    for (i=0;i<elem.length;i++)
        if (elem[i].checked) {
            showLayer('sql_type_l', elem[i].value);
        }

    function clear_sql_types(){

        elem = jQuery('#form-fields').find('input[name="AF[sql_type]"]:checked').val();
        if (elem != 1) {
            prev_opt = elem;
            elem = 1;

        }
    }
    function back_sql_types(){
        elem = jQuery('#form-fields').find('input[name="AF[sql_type]"]:checked').val();
        if ((elem == 1) && prev_opt)
            elem = prev_opt;
    }
    function toggle_sql_type(enable){
//        field = document.getElementById('form-fields').sql;
////        if (enable == true){
//            field[0].disabled = false;
////        } else {
////            field[0].disabled = true;
////            field[1].checked = true;
////        }
    }

JS;
$this->registerJs($script, View::POS_END);
?>

<script type="text/template" id="update_field_actions">
    <?php
    echo $form->field($model,"c_table[0]")->dropDownList(
    array_merge(['0' => '-- choose --'],\yii\helpers\ArrayHelper::map($tables, 'table', 'table')) ,
    ['style' => 'width:100px;','class' => 'c_table'])->label(Yii::t('app', 'Table')
    );
    echo $form->field($model,"c_field[0]")->dropDownList( [] , ['style' => 'width:100px;','class' => 'c_field'])->label(Yii::t('app', 'Field'));
    echo $form->field($model,"c_option[0]")->textInput( ['style' => 'width:150px;','class' => 'c_value'])->label(\Yii::t('app','Value'));
    ?>
</script>

<script type="text/template" id="prevent_actions">
    <?= $form->field($model,"c_option[0]")->textarea()->label(Yii::t('app', 'Prevent Reason')); ?>
</script>

<script type="text/template" id="email_actions">
    <?php
    echo $form->field($model,"c_template[0]")->dropDownList(
    array_merge(['0' => '-- choose --'],\yii\helpers\ArrayHelper::map($templates, 'id', 'name')),
    ['style' => 'width:100px;','class' => 'c_template'])->label(Yii::t('app', 'Send This')
    );
    echo $form->field($model,"c_user[0]")->dropDownList( ['user' => \Yii::t('app','User'),'other' => \Yii::t('app','Other')] , ['style' => 'width:100px;','class' => 'c_user'])->label(Yii::t('app', 'To'));
    echo $form->field($model,"c_option[{$k}]" , ['options' => ['class' => 'hide check-hide']])->textInput( ['style' => 'width:150px;','class' => 'c_option'])->label(\Yii::t('app','Email'));
    ?>
</script>

<script type="text/template" id="content_actions">
    <tr>
        <td>
            <div class="form-inline">
                <?= $form->field($model,"c_action[0]")->dropDownList( $c_action_options , ['style' => 'width:150px;','class' => 'actions'])->label(Yii::t('app', 'Do')); ?>
                <?= $form->field($model,"c_if[0]")->dropDownList($c_if , ['style' => 'width:80px;','class' => 'if'])->label(Yii::t('app', 'IF')); ?>
                <label> <?= Yii::t('app', 'Following conditions match:-') ?></label>
            </div>

        </td>
        <td>
            <div class="form-inline">
                <?php
                echo $form->field($model,"c_condition[0][0]")->dropDownList( $c_condition , ['style' => 'width:150px;','class' => 'c_condition'])->label(Yii::t('app', 'This field value'));
                echo $form->field($model,"c_value[0][0]")->textInput( ['style' => 'width:150px;','class' => 'c_value'])->label(\Yii::t('app','Value'));
                ?>
                <a class="delete_field_choice remove-rule m-t-30" title="remove this rule"><i class="fa fa-minus-square fa-lg"></i></a>
            </div>
            <button class="btn btn-info btn-xs btn-mini add-rule" type="button"><i class="fa fa-plus-square fa-lg"></i></button>
        </td>
        <td>
            <div class="extra_options form-inline"></div>
            <button type="button" class="remove-condition btn btn-danger btn-xs btn-mini"><?= Yii::t('app', 'Remove') ?></button>
        </td>
    </tr>
</script>

</div>

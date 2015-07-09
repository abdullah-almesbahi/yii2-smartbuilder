<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use backend\widgets\BackendWidget;
use kartik\icons\Icon;
use yii\base\Event;
use yii\web\View;
use vova07\imperavi\Widget as ImperaviWidget;

?>

<?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>



<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-items', // required: css class selector
    'widgetItem' => '.form-options-body', // required: css class
    'limit' => 30, // the maximum times, an element can be cloned (default 999)
    'min' => 1, // 0 or 1 (default 1)
    'insertButton' => '.add-item', // css class
    'deleteButton' => '.remove-item', // css class
    'model' => $models[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'plate_type',
        'plate_source',
        'plate_size',
        'arrow',
    ],
]);

?>

<div class="container-items"><!-- widgetContainer -->
    <?php foreach ($models as $ii => $model): ?>
        <div class="form-options-body row" style="border: 1px solid #eaeaea">
            <?= "#".$model->id ?>

    <!-- widgetBody -->

            <?php
            // necessary for update action.
            echo Html::activeHiddenInput($model, "[{$ii}]id" , ['class' => 'id']);

            $where = [
                ['like','display' , 'all_pages'],
            ];

            $af = new \backend\models\AF($tabel);
            $fields = $af->getAll($where);

             Yii::$app->trigger('backend/crud-in/form/begin'); ?>
            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?php Yii::$app->trigger('backend/crud-in/form/before'); ?>
                <?php Yii::$app->trigger('backend/crud-in/form/before/grid'); ?>
                <?php

                //footer button

                //footer Class
                ob_start();
                Yii::$app->trigger('backend/crud-in/form/submit/before');
                if($model->isNewRecord){
                    if( Yii::$app->hasEventHandlers('backend/crud-in/form/submit/add') ){
                        Yii::$app->trigger('backend/crud-in/form/submit/add');
                    }else{
                        echo Html::submitButton( Icon::show('plus').' '.Yii::t('app', 'Create'), ['class' => 'btn btn-success']);
                    }
                }else{
                    if(Yii::$app->hasEventHandlers('backend/crud-in/form/submit/update') ){
                        Yii::$app->trigger('backend/crud-in/form/submit/update');
                    }else{
                        echo Html::submitButton( Icon::show('save').' '.Yii::t('app', 'Update'), ['class' => 'btn btn-primary']);
                    }
                }
                Yii::$app->trigger('backend/crud-in/form/submit/after');
                $footer = ob_get_contents();
                ob_end_clean();


                $workflow = false;
                $mutliTransationDisplay = false;
                $af = (new \backend\models\AF($this->context->table_name));
                //We check here is Workflow is enabled or not
                if( $af->isWorkflowEnabled() ){
                    //We will save all fields that are only for displaying and not for editing as array , so we can check by it in loop blew
                    $ids = $af->getWorkflowFieldsIds('wf_field_view');
                    if(sizeof($model->getBehavior('w1')) > 0){
                        $af->w1 = $model->getBehavior('w1')->defaultWorkflowId;
                    }

                    $workflow = true;
                }


                //render additional fields
                if(count($fields) > 0){

                    foreach($fields as $k => $v){

                        unset($field);
                        $field = $form->field($model, "[{$ii}]".$v->attributes['name']);
                        if($v->attributes['title'] != ''){
                            $field->label($v->attributes['title']);
                        }
                        if($v->attributes['description'] != ''){
                            $field->hint($v->attributes['description']);
                        }

                        //go inside if workflow is enabled
                        if(true === $workflow){

                            //if this field id is exists in field only for viewing , echo and go to next field
                            if(in_array($v->attributes['id'],$ids)){
                                $name = $v->attributes['name'];
                                switch ($v->attributes['field_type']) {
                                    case 'text':
                                        echo $field->textInput(['readonly' => true]);
                                        break;
                                    case 'textarea':
                                        echo $field->textarea(['readonly' => true,'cols' => $v->attributes['cols'], 'rows' => $v->attributes['rows']]);
                                        break;
                                    case 'select':
                                        if (!empty($v->attributes['sql_query'])) {
                                            $results = Yii::$app->db->createCommand($v->attributes['sql_query'])->queryAll();
                                            if(sizeof($results) > 0){
                                                $c[''] = Yii::t('app' , '-- Select --' );
                                                foreach($results as $result){
                                                    list($a, $b) = array_values($result);
                                                    $c[$a] = $b;
                                                }
                                                $field->dropDownList($c,['disabled' => 'disabled']);
                                            }

                                        } else {
                                            preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                                            $values = array();
                                            foreach ($regs[1] as $i => $k) {
                                                $values[$k] = $regs[2][$i];
                                            }
                                            echo $field->dropDownList($values, ['disabled' => 'disabled']);
                                        }
                                        break;
                                    case 'multi_image':
                                        //
                                        echo '<label class="control-label">'.$v->attributes['title'].'</label>';
                                        $image_directory_uri = '/uploads';
                                        echo \backend\widgets\image\ImageDropzone::widget([
                                            'name' => 'file',
                                            'url' => \yii\helpers\Url::to(['upload']),
                                            'uploadDir' => $image_directory_uri,
                                            'sortable' => false,
                                            'objectId' => 3,
                                            'modelId' => $model->id,
                                            'htmlOptions' => [
                                                'class' => 'table table-striped files',
                                                'id' => 'previews',
                                            ],
                                            'options' => [
                                                'previewTemplate' =>
                                                    '<div class="file-row">
                                        ' . Html::input('hidden', 'id[]') . '
                                        <!-- This is used as the file preview template -->
                                        <div>
                                            <span class="preview"><a href="#" data-toggle="lightbox"><img style="width: 80px; height: 80px;" data-dz-thumbnail /></a></span>
                                        </div>
                                        <div>
                                            <p class="name" data-dz-name></p>
                                            <div class="dz-error-message"><span data-dz-errormessage></span></div>
                                        </div>
                                        <div class="description">
                                            ' . Html::textarea('description', '', ['style' => 'width: 100%; min-width: 80px; height: 80px;','readonly' => true]) . '
                                        </div>
                                        <div>
                                            <p class="size" data-dz-size></p>
                                            <div class="dz-progress progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                              <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                            </div>
                                            <div class="dz-success-mark"><span>✔</span> OK</div>
                                            <div class="dz-error-mark"><span>✘</span> ERROR</div>
                                        </div>
                                      </div>',
                                                'clickable' => false,
                                            ],
                                        ]);
                                        break;
                                }
                                continue;
                            }

                            //if current field is status , then remove options that user can't access to it
                            if($v->attributes['name'] == 'status'){
                                $value = $v->attributes['name'];

                                if(isset($model->oldAttributes[$value])){
                                    $allowedTransition = $af->getWorkflowAllowedTransitions($model->oldAttributes[$value]);
                                }else{
                                    $allowedTransition = $af->getWorkflowAllowedTransitions($model->$value);
                                }

                                preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                                $values = array();
                                //we need to get option name for the status
                                foreach($allowedTransition as $at_k => $at_v){
                                    foreach ($regs[1] as $i => $option_name) {
                                        if($option_name == $at_k){
                                            $values[$at_k] = $regs[2][$i];
                                        }
                                    }

                                }
                                if(count($values) == 0){
                                    echo $field->dropDownList([' '=>\Yii::t('app','You do not have permission to change status')],['readonly' => true]);
                                    continue;
                                }
                                echo $field->dropDownList($values);
                                continue;
                            }
                        }

                        Yii::$app->trigger('backend/crud-in/form/field/before');
                        Yii::$app->trigger('backend/crud-in/form/field/before/'.$v->attributes['name']);
                        if(Yii::$app->hasEventHandlers('backend/crud-in/form/field/'.$v->attributes['name'])){
                            Yii::$app->trigger('backend/crud-in/form/field/'.$v->attributes['name'] , new Event(['sender' => (object) array_merge((array) ['m'=>$model], (array) ['f'=>$form]) ]));
                        }else {
                            switch ($v->attributes['field_type']) {
                                case 'text':
                                    $field->textInput(['maxlength' => 255, 'size' => $v->attributes['size']]);
                                    break;
                                case 'date':
                                    break;
                                case 'image':
                                    break;
                                case 'multi_image':
                                    //
                                    echo '<label class="control-label">'.$v->attributes['title'].'</label>';
                                    $image_directory_uri = '/uploads';
                                    echo \yii\helpers\Html::tag(
                                        'span',
                                        Yii::t('app', 'Add files..'),
                                        [
                                            'class' => 'btn btn-success m-l-10 fileinput-button'
                                        ]
                                    );
                                    echo \backend\widgets\image\ImageDropzone::widget([
                                        'name' => 'file',
                                        'url' => \yii\helpers\Url::to(['upload']),
                                        'removeUrl' => \yii\helpers\Url::to(['remove']),
                                        'uploadDir' => $image_directory_uri,
                                        'sortable' => false,
                                        'sortableOptions' => [
                                            'items' => '.dz-image-preview',
                                        ],
                                        'objectId' => 3,
                                        'modelId' => $model->id,
                                        'htmlOptions' => [
                                            'class' => 'table table-striped files',
                                            'id' => 'previews',
                                        ],
                                        'options' => [
                                            'clickable' => ".fileinput-button",
                                            'acceptedFiles' => 'image/*,.zip,.rar',
                                        ],
                                    ]);
                                    continue 2;
                                    break;
                                case 'textarea':
                                    $field->textarea(['cols' => $v->attributes['cols'], 'rows' => $v->attributes['rows']]);
                                    break;
                                case 'editor':
                                    $field->widget(ImperaviWidget::className(), [
                                        'settings' => [
                                            'replaceDivs' => false,
                                            'minHeight' => 200,
                                            'paragraphize' => true,
                                            'pastePlainText' => true,
                                            'buttonSource' => true,
                                            'plugins' => [
                                                'table',
                                                'fontsize',
                                                'fontfamily',
                                                'fontcolor',
                                                'video',
                                            ],
                                        ],
                                    ]);
                                    break;
                                case 'multi_select':
                                    break;
                                case 'select':
                                    if (!empty($v->attributes['custom_func'])) {

                                    } elseif (!empty($v->attributes['sql_query'])) {
                                        $results = Yii::$app->db->createCommand($v->attributes['sql_query'])->queryAll();
                                        if(sizeof($results) > 0){
                                            $c[''] = Yii::t('app' , '-- Select --' );
                                            foreach($results as $result){
                                                list($a, $b) = array_values($result);
                                                $c[$a] = $b;
                                            }
                                            $field->dropDownList($c,['class' => $v->attributes['name']." form-control"]);
                                        }

                                    } else {
                                        preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                                        $default = $values = array();
                                        foreach ($regs[1] as $i => $k) {
                                            $values[$k] = $regs[2][$i];
                                            if ($regs[4][$i] == 1)
                                                $default[] = $k;
                                        }
                                        if ($model->isNewRecord && !empty($default[0])) {
                                            $name = $v->attributes['name'];
                                            $model->$name = $default[0];
                                        }
                                        $field->dropDownList($values,['class' => $v->attributes['name']." form-control"]);
                                    }
                                    break;
                                case 'radio':
                                    break;
                                case 'checkbox':
                                    break;
                                case 'custom':
                                    break;
                                case 'array':
                                    $field->widget(
                                        \devgroup\jsoneditor\Jsoneditor::className(),
                                        [
                                            'editorOptions' => [
                                                'modes' => ['code', 'tree'],
                                                'mode' => 'tree',
                                                'editable' => new \yii\web\JsExpression('function(node) {
                                        return {
                                            field : false,
                                            value : true
                                        };
                                    }
                                '),
                                            ],
                                        ]
                                    );
                                    break;
                            }
                            echo '<div class="col-md-3">'.$field.'</div>';
                        }


                        Yii::$app->trigger('backend/crud-in/form/field/after');
                        Yii::$app->trigger('backend/crud-in/form/field/after/'.$v->attributes['name']);
                    }
                    ?>
                    <div class="col-md-3 m-b-5 ">
                        <button type="button" class="add-item btn btn-xs btn-mini">+
                        </button>
                        <button type="button" class="remove-item btn-danger btn  btn-xs btn-mini">x</button>
                    </div>
                <?php

                }


                ?>



                <?php Yii::$app->trigger('backend/crud-in/form/after/grid'); ?>
<!--                --><?php //ActiveForm::end(); ?>


            </div>
            <?php Yii::$app->trigger('backend/crud-in/form/end'); ?>

            <?php
            ob_start();
            Yii::$app->trigger('backend/crud-in/form/javascript');
            $script = ob_get_contents();
            ob_end_clean();
            $this->registerJs($script, View::POS_END);


            ?>

        </div>

    <?php endforeach; ?>

</div>
<?php DynamicFormWidget::end(); ?>
<br />
<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save & Refresh', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Yii::$app->trigger('backend/crud-in/form/after'); ?>

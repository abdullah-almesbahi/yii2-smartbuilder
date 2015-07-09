<?php

namespace backend\modules\smartbuilder\traits;

use backend\models\AuditTrail;
use kartik\grid\GridView;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

trait Workflow
{
    public function getAFColumns($fields) {
        //enter if fields is not empty
        if(sizeof($fields) > 0) {
            //save all columns in this variable
            $af_columns = [];

            $i = 1;

            $auditTrail = $this->getAuditTrail($fields);

            //loop fields
            foreach ($fields as $k => $v) {

                //if audit trail option is enabled for this user
                if(1 === $i && true === $this->isAuditTrailEnabled($auditTrail)){
                    $af_columns[] = [

                        'class'=>'kartik\grid\ExpandRowColumn',
                        'width'=>'50px',
                        'value'=>function ($model, $key, $index, $column) {
                            return GridView::ROW_COLLAPSED;
                        },
                        'detail'=>function ($model, $key, $index, $column) {
                            if(isset(static::$modelClass)){
                                $at = AuditTrail::find()->where(['model_id' => $model->id])->andWhere(['model' => substr(static::$modelClass,1)]);
                                return Yii::$app->controller->renderPartial('@app/views/audit-trail/view', ['model'=>$at]);
                            }else{
                                throw new ServerErrorHttpException('You need to define $modelClass in your controller, Example : public static $modelClass = \'\backend\models\Test\';');
                            }
                        },
                        'headerOptions'=>['class'=>'kartik-sheet-style'],
                        'expandIcon' => '<i class="fa fa-file-text-o"></i>',
                        'collapseIcon' => '<i class="fa fa-file-text"></i>',

                    ];
                }

                //if current field is status and workflow is enabled , enter then.
                if($v->attributes['name'] == 'status' && 1 === $v->attributes['enable_workflow']){
                    //prepare status to use it later
                    preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                    //display all status option if this group have access to it
                    if(!empty($v->attributes['wf_view_all'])){
                        $wf_view_all = is_array($v->attributes['wf_view_all'])?$v->attributes['wf_view_all']:unserialize($v->attributes['wf_view_all']);
                        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));

                        if(isset($wf_view_all[$role]) && 1 == $wf_view_all[$role]){
                            $all_options['all'] = Yii::t('app','All').' '.$v->attributes['title'];
                        }
                    }else{
                        $all_options = isset($all_options) && is_array($all_options)?$all_options:[];
                    }

                    //display my items option if this user have access to it
                    if(!empty($v->attributes['wf_view_by_owner'])){
                        $wf_view_by_owner = is_array($v->attributes['wf_view_by_owner'])?$v->attributes['wf_view_by_owner']:unserialize($v->attributes['wf_view_by_owner']);
                        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
                        if(isset($wf_view_by_owner[$role]) && 1 == $wf_view_by_owner[$role]){
                            $all_options['myItems'] = Yii::t('app','My Items');
                        }
                    }else{
                        $all_options = isset($all_options) && is_array($all_options)?$all_options:[];
                    }


                    foreach ($regs[1] as $i => $value) {
                        $all_options[$value] = $regs[2][$i];
                    }

                    $af_columns[] = array(
                        'attribute' => $v->attributes['name'],
                        'value'=>function ($model, $key, $index, $column) use ($v) {
                            if(empty($v->attributes['options'])){
                                return $model->status;
                            }
                            preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                            foreach ($regs[1] as $i => $value) {
                                if($value == $model->status){
                                    return $regs[2][$i];
                                }
                            }
                            return $model->status;
                        },
                        'vAlign'=>'middle',
                        'width'=>'180px',
                        'filterType'=>GridView::FILTER_SELECT2,
                        'filter'=>$all_options,
                        'filterWidgetOptions'=>[
                            'pluginOptions'=>['allowClear'=>true],
                        ],
                        'filterInputOptions'=>['placeholder'=>Yii::t('app','My')." ".$v->attributes['title']],
                        'format'=>'raw',
                        'label' => $v->attributes['title'],
                    );

                } else {
                    switch($v->attributes['field_type']){
                        case 'select':
                            if (!empty($v->attributes['custom_func'])) {

                            } elseif (!empty($v->attributes['sql_query'])) {
                                $results = Yii::$app->db->createCommand($v->attributes['sql_query'])->queryAll();
                                if(sizeof($results) > 0){
                                    $values[''] = Yii::t('app' , '-- Select --' );
                                    foreach($results as $result){
                                        list($a, $b) = array_values($result);
                                        $values[$a] = $b;
                                    }
                                }
                            } else {
                                preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                                $default = $values = array();
                                foreach ($regs[1] as $i => $k) {
                                    $values[$k] = $regs[2][$i];
                                }
                            }
                            $af_columns[] = array(
                                'attribute' => $v->attributes['name'],
                                'label' => $v->attributes['title'],
                                'filterType'=>GridView::FILTER_SELECT2,
                                'filter'=>$values,
                                'filterWidgetOptions'=>[
                                    'pluginOptions'=>['allowClear'=>true],
                                ],
                                'filterInputOptions'=>['placeholder'=>Yii::t('app','Choose')." ".$v->attributes['title']],
                                'value'=>function ($model, $key, $index, $column) use ($v,$values) {
                                    $_value = $v->attributes['name'];
                                    $_value = $model->$_value;
                                    if (!empty($v->attributes['sql_query'])) {

                                        return isset($values[$_value])?$values[$_value]:'';
                                    }else {
                                        if (empty($v->attributes['options'])) {
                                            return $_value;
                                        }
                                        preg_match_all('/^\s*(.*?)\s*\|\s*(.+?)\s*(|\|(.+?))\s*$/m', $v->attributes['options'], $regs);
                                        foreach ($regs[1] as $i => $value) {
                                            if ($value == $_value) {
                                                return $regs[2][$i];
                                            }
                                        }
                                    }
                                    return $_value;
                                },
                            );
                            break;
                        default:
                            $af_columns[] = array(
                                'attribute' => $v->attributes['name'],
                                'label' => $v->attributes['title'],
                            );
                            break;
                    }

                }
                $i++;
            }
            return $af_columns;
        }
        return [];
    }

    /**
     * Check if Audit Trail is enabled for this Role group or not
     * @param $auditTrail
     * @return bool Return true on success and false on failure
     */
    public function isAuditTrailEnabled($auditTrail){

        if(empty($auditTrail)){
            return false;
        }

        //unserialize variable if not serialized yet
        $auditTrail = is_array($auditTrail)?$auditTrail:unserialize($auditTrail);

        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        if(isset($auditTrail[$role]) && 1 == $auditTrail[$role]){
            return true;
        }
        return false;
    }

    /**
     * Extract audit trail field from all fields
     * @param $fields
     * @return bool|mixed
     */
    public function getAuditTrail($fields){
        //enter if fields is not empty
        if(sizeof($fields) > 0) {
            //loop fields
            foreach ($fields as $k => $v) {
                if($v->attributes['name'] == 'status' && 1 === $v->attributes['enable_workflow']){
                    return is_array($v->attributes['wf_view_audit_trail'])?$v->attributes['wf_view_audit_trail']:unserialize($v->attributes['wf_view_audit_trail']);
                }
            }
        }
        return false;
    }

}

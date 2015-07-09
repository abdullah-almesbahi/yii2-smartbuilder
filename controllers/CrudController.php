<?php

namespace SmartBuilder\controllers;

use SmartBuilder\traits\Workflow;
use backend\widgets\BackendWidget;
use backend\actions\AdjacencyFullTreeDataAction;
use kartik\grid\GridView;
use Ruler\RuleBuilder;
use Yii;
use  SmartBuilder\models\Crud;
use  SmartBuilder\models\CrudSearch;
use  SmartBuilder\models\AF;
use  SmartBuilder\models\AFSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use SmartBuilder\widgets\image\RemoveAction;
use SmartBuilder\widgets\image\SaveInfoAction;
use SmartBuilder\widgets\image\UploadAction;
use backend\models\Model;


/**
 * CrudController implements the CRUD actions for Crud model.
 *
 * @author Abdullah Al-Mesbahi
 */
class CrudController extends Controller
{

    use Workflow;
    public $table_name;
    public $instance;
    public $af;
    public $ruler_data;
    public $updateModifyModelHandler = null;
    public $updateExtraUpdateHandler = null;
    public $updateAjaxResponseHandler = null;

    static  $image_directory_uri = '/uploads';

    /**
     * @param string $id the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($id, $module, $config = [])
    {
        $this->id = $id;
        $this->module = $module;

        //Instance for the current table
        $this->table_name = strtolower($this->getTableName());
        $this->instance = new Crud();
        $this->instance->setTableName(strtolower($this->table_name));

        //Instance for additional field
        $this->af = new AF(strtolower($this->table_name));

        $this->check_default_additional_fields();
    }

    public function check_default_additional_fields() {

        $columns = $this->instance->getTableSchema()->columns;

        $found = array();
        if (!empty($columns) && count($columns) > 0) {

            $count = 0;

            foreach ($columns as $k => $v) {

                //if column is exists in current table , enter
                if ($v->name == 'create_time' || $v->name == 'status' || $v->name == 'status_ex' || $v->name == 'created_by' || $v->name == 'sort' || $v->name == 'id' ||
                    $v->name == 'title' || $v->name == 'update_time' || $v->name == 'assign_id')
                {
                    $found[$v->name] = true;
                    $count++;
                }
                if(isset($found[$v->name])){
                    if ( false === $this->af->findByName($v->name) && ($v->name == 'title' ||  $v->name == 'status' ||  $v->name == 'status_ex' ||  $v->name == 'assign_id'  ) ) {
                        //add field to additional field table
                        $result = $this->af->add_af($v);
                        continue;
                    }
                }else{

                    if (!$this->af->findByName($v->name)) {
                        //add field to additional field table
                        $this->af->add_af($v);
                    }
                }
            }

            if ($count < 9) {
                $this->instance->alter_table($found);
            }
        }
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'af-delete' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadAction::className(),
                'upload' => self::$image_directory_uri,
            ],
            'remove' => [
                'class' => RemoveAction::className(),
                'uploadDir' => self::$image_directory_uri,
            ],
            'save-info' => [
                'class' => SaveInfoAction::className(),
            ],
//            'getAfTree' => [
//                'class' => AdjacencyFullTreeDataAction::className(),
//                'class_name' => AF::className(),
//                'model_label_attribute' => 'name',
//                'model_parent_attribute' => 'id',
//                'query_sort_order' => 'ord',
//                'whereCondition' => ['table' => strtolower($this->table_name)],
//                'vary_by_type_attribute' => null,
//            ],
        ];
    }

    /**
     * Lists all Crud models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = $this->createCrudSearchClass();
        $searchModel->setTableName(strtolower($this->getTableName()));
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);



        if(true === $this->af->isWorkflowEnabled()){
            $ids = $this->af->getWorkflowFieldsIds('wf_field_index');
            $where = [
                ['like','admin_display' , 'index'],
                ['in','id' , $ids],
            ];
        }else{
            $where = [
                ['like','admin_display' , 'index'],
            ];
        }
        $all = $this->af->getAll($where);
        $af_columns = $this->getAFColumns($all);
        return $this->render('@SmartBuilder/views/crud/index', [
            'af_columns' => $af_columns,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Crud model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('@SmartBuilder/views/crud/view', [
            'model' => $this->findModel($id),
        ]);
    }



    /**
     *      * Updates an existing Crud model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null $id
     * @param array $redirect
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate($id = null ,$redirect = ['index'] )
    {


        $model = null;
        if (null === $id || 0 === $id) {

            $model = $this->createCrudClass();
            $model->setTableName(strtolower($this->getTableName()));
            $model->loadDefaultValues();
        } else {
            $model = $this->findModel($id);
        }

        if (null === $model) {
            throw new ServerErrorHttpException;
        }

        $post = \Yii::$app->request->post();

        if ($model->load($post) && $model->validate() && $this->rulerValidate($model)) {

            if (isset($this->updateModifyModelHandler)) {
                $model = call_user_func($this->updateModifyModelHandler, $post , $model);
            }

            $save_result = $model->save(false);

            if (isset($this->updateExtraUpdateHandler)) {
                if ($result = call_user_func($this->updateExtraUpdateHandler, $post , $model)) {

                }
            }
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                if (Yii::$app->request->isAjax) {
                    if (isset($this->updateAjaxResponseHandler)) {
                        if ($result = call_user_func($this->updateAjaxResponseHandler, isset($post['Crud'])?$post['Crud']:$post)) {
                            die( Json::encode($result));
                        }
                    }
                    die( Json::encode([
                        'access'=>'true',
                        'data' => $post['Crud'],
                    ]));
                }
                if(isset($post['redirect']) && $post['redirect'] == 'referrer'){
                    return $this->redirect(Yii::$app->request->referrer);
                }elseif(isset($post['redirect']) && $post['redirect'] == 'refresh'){
                    return $this->refresh();
                }else {
                    return $this->redirect($redirect);
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }else {
            // validation failed: $errors is an array containing error messages
            $errors = $model->errors;
            if (is_array($errors) && count($errors) > 0) {
                foreach($errors as $key => $message) {
                    Yii::$app->session->addFlash('error', implode(', ' ,$message));
                }
            }
            if(true === $this->af->isWorkflowEnabled()){

                //if multi display depending on transation is enabled and display fields on this status is not ignored , go inside
                if($this->af->isEnabledMultiTransationDisplay() && false === $this->af->isTransationIgnored($model) ){

                    $transationKey = $this->af->getTransationKey($model);
                    $ids = $this->af->getWorkflowFieldsIds('wf_fields_md');
                    $_ids = $ids[$transationKey];
                    $where = [
                        ['like','admin_display' , 'update'],
                        ['in','id' , $_ids],
                    ];
                }else{
                    $ids = $this->af->getWorkflowFieldsIds();
                    $where = [
                        ['like','admin_display' , 'update'],
                        ['in','id' , $ids],
                    ];
                }

            }else{
                $where = [
                    ['like','admin_display' , 'update'],
                ];
            }
            return $this->render('@SmartBuilder/views/crud/update', [
                'fields' => $this->af->getAll($where),
                'model' => $model,
            ]);
        }

    }

    /**
     * Deletes an existing Crud model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id , $redirect = true)
    {
        Yii::$app->session->setFlash('error', Yii::t('app', 'Record deleted successfully'));
        $this->findModel($id)->delete();
        if (Yii::$app->request->isAjax) {
            die( Json::encode([
                'access'=>'true',
            ]));
        }
        if(true === $redirect) {
            return $this->redirect(['index']);
        }
    }


    /**
     * @return class Crud
     */
    public function createCrudClass(){
        //override the class if is defined
        if(isset(static::$modelClass)){
            $modelClass = static::$modelClass;
            return new $modelClass();
        }

        return new Crud();

    }

    /**
     * @return  class CrudSearch
     */
    public function createCrudSearchClass(){
        //override the class if is defined
        if(isset(static::$modelSearchClass)){
            $searchClass = static::$modelSearchClass;
            return new $searchClass();
        }
        return new CrudSearch();
    }

    /**
     * Finds the Crud model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * the model class can be override
     * @param integer $id
     * @return Crud the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id)
    {
        //override the class if is defined
        $modelClass = '\backend\models\Crud';
        if(isset(static::$modelClass)){
            $modelClass = static::$modelClass;
        }

        if (($model = $modelClass::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('app','The requested page does not exist.'));
        }

    }


    /**
     * Lists all Additional fields for CRUD models.
     * @return mixed
     */
    public function actionAf()
    {

        //$ff = $this->af->get_additional_fields();
        $searchModel = new AFSearch(strtolower($this->table_name));
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('@SmartBuilder/views/crud/_af_index', [
//            'fields' => $ff,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    /**
     * Updates an existing Additonal Field model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAfUpdate($id = null)
    {

        $model = null;
        if (null === $id) {
            $model = new AF(strtolower($this->table_name));
            $model->loadDefaultValues();
            $model->table = strtolower($this->table_name);
        } else {
            $model =  $this->findAFModel($id);
            $model->parent_table = strtolower($this->table_name);

        }

        if (null === $model) {
            throw new ServerErrorHttpException;
        }

        $post = \Yii::$app->request->post();
        //ugly fix for enable_workflow and enable_condition if not isset
        if(sizeof($post) > 0 && !isset($post['AF']['enable_workflow'])){
            $post['AF']['enable_workflow'] = 0;
        }
        if(sizeof($post) > 0 && !isset($post['AF']['enable_condition'])){
            $post['AF']['enable_condition'] = 0;
        }


        if ($model->load($post) && $model->validate()) {
            //if new
            if (null === $id) {
                $model->add_sql_field($model->attributes);
            }elseif($model->oldAttributes['sql_type'] != $model->attributes['sql_type']) {
                // handle change sql type
               $model->change_sql_field($model);
            }

            $model->display = is_array( $model->display)?join(', ',$model->display):'';
            $model->admin_display = is_array( $model->admin_display)?join(', ',$model->admin_display):'';

            $model->c_action = is_array( $model->c_action)?serialize($model->c_action):'';
            $model->c_if = is_array( $model->c_if)?serialize($model->c_if):'';
            $model->c_condition = is_array( $model->c_condition)?serialize($model->c_condition):'';
            $model->c_value = is_array( $model->c_value)?serialize($model->c_value):'';
            $model->c_table = is_array( $model->c_table)?serialize($model->c_table):'';
            $model->c_field = is_array( $model->c_field)?serialize($model->c_field):'';
            $model->c_option = is_array( $model->c_option)?serialize($model->c_option):'';
            $model->c_template = is_array( $model->c_template)?serialize($model->c_template):'';
            $model->c_user = is_array( $model->c_user)?serialize($model->c_user):'';

            //Workflow : only work with status field
            if($model->name == 'status' || $model->name == 'status_ex'){
                $model->wf_field_index = is_array($model->wf_field_index) ? serialize($model->wf_field_index) : '';
                $model->wf_field_update = is_array($model->wf_field_update) ? serialize($model->wf_field_update) : '';
                $model->wf_field_view = is_array($model->wf_field_view) ? serialize($model->wf_field_view) : '';
                $model->wf_definition_from = is_array($model->wf_definition_from) ? serialize($model->wf_definition_from) : '';
                $model->wf_definition_to = is_array($model->wf_definition_to) ? serialize($model->wf_definition_to) : '';
                $model->wf_definition_initial = is_array($model->wf_definition_initial) ? serialize($model->wf_definition_initial) : '';
                $model->wf_view_all = is_array($model->wf_view_all) ? serialize($model->wf_view_all) : '';
                $model->wf_view_by_owner = is_array($model->wf_view_by_owner) ? serialize($model->wf_view_by_owner) : '';
                $model->wf_view_audit_trail = is_array($model->wf_view_audit_trail) ? serialize($model->wf_view_audit_trail) : '';
                $model->wf_assign = is_array($model->wf_assign) ? serialize($model->wf_assign) : '';
                $model->wf_validate_from = is_array($model->wf_validate_from) ? serialize($model->wf_validate_from) : '';
                $model->wf_validate_to = is_array($model->wf_validate_to) ? serialize($model->wf_validate_to) : '';
                $model->wf_enable_md = is_array($model->wf_enable_md) ? serialize($model->wf_enable_md) : '';
                $model->wf_ignore_md = is_array($model->wf_ignore_md) ? serialize($model->wf_ignore_md) : '';
                $model->wf_fields_md = is_array($model->wf_fields_md) ? serialize($model->wf_fields_md) : '';
            }

            $save_result = $model->save(false);
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                if(isset($post['button']) && $post['button'] == 'reload'){
                    return $this->refresh();
                }else {
                    return $this->redirect(['af']);
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }else {
            // validation failed: $errors is an array containing error messages
            $errors = $model->errors;
            //define variables to avoide warnning
            $all_fields_index = $all_fields_update = [];
            if (is_array($errors) && count($errors) > 0) {
                foreach($errors as $key => $message) {
                    Yii::$app->session->addFlash('error', implode(', ' ,$message));
                }
            }else{

                $model->display = !empty($model->display) ? explode(', ', $model->display) : $model->display;
                $model->admin_display = !empty($model->admin_display) ? explode(', ', $model->admin_display) : $model->admin_display;

                $model->c_action = !empty($model->c_action) ? unserialize($model->c_action) : '';
                $model->c_if = !empty($model->c_if) ? unserialize($model->c_if) : '';
                $model->c_condition = !empty($model->c_condition) ? unserialize($model->c_condition) : '';
                $model->c_value = !empty($model->c_value) ? unserialize($model->c_value) : '';
                $model->c_table = !empty($model->c_table) ? unserialize($model->c_table) : '';
                $model->c_field = !empty($model->c_field) ? unserialize($model->c_field) : '';
                $model->c_option = !empty($model->c_option) ? unserialize($model->c_option) : '';
                $model->c_template = !empty($model->c_template) ? unserialize($model->c_template) : '';
                $model->c_user = !empty($model->c_user) ? unserialize($model->c_user) : '';


                //Workflow : only work with status field
                if($model->name == 'status' || $model->name == 'status_ex'){
                    $all_fields_index = $this->af->getAll([['like','admin_display' , 'index']]);
                    $all_fields_update = $this->af->getAll([['like','admin_display' , 'update']]);
                    $model->wf_field_index = !empty($model->wf_field_index) ? unserialize($model->wf_field_index) : '';
                    $model->wf_field_update = !empty($model->wf_field_update) ? unserialize($model->wf_field_update) : '';
                    $model->wf_field_view = !empty($model->wf_field_view) ? unserialize($model->wf_field_view) : '';
                    $model->wf_definition_from = !empty($model->wf_definition_from) ? unserialize($model->wf_definition_from) : '';
                    $model->wf_definition_to = !empty($model->wf_definition_to) ? unserialize($model->wf_definition_to) : '';
                    $model->wf_definition_initial = !empty($model->wf_definition_initial) ? unserialize($model->wf_definition_initial) : '';
                    $model->wf_view_all = !empty($model->wf_view_all) ? unserialize($model->wf_view_all): '';
                    $model->wf_view_by_owner = !empty($model->wf_view_by_owner) ? unserialize($model->wf_view_by_owner) : '';
                    $model->wf_view_audit_trail = !empty($model->wf_view_audit_trail) ? unserialize($model->wf_view_audit_trail) : '';
                    $model->wf_assign = !empty($model->wf_assign) ? unserialize($model->wf_assign) : '';
                    $model->wf_validate_from = !empty($model->wf_validate_from) ? unserialize($model->wf_validate_from) : '';
                    $model->wf_validate_to = !empty($model->wf_validate_to) ? unserialize($model->wf_validate_to) : '';
                    $model->wf_enable_md = !empty($model->wf_enable_md) ? unserialize($model->wf_enable_md) : '';
                    $model->wf_ignore_md = !empty($model->wf_ignore_md) ? unserialize($model->wf_ignore_md) : '';
                    $model->wf_fields_md = !empty($model->wf_fields_md) ? unserialize($model->wf_fields_md) : '';
                }

            }
            $tables = $model->getAllTables();
            $templates = \backend\modules\mailer\models\MailerTemplate::find()->select('id,name')->asArray()->all();

            return $this->render('@SmartBuilder/views/crud/_af_update', [
                'model' => $model,
                'tables' => $tables,
                'templates' => $templates,
                'all_fields_index' => $all_fields_index,
                'all_fields_update' => $all_fields_update,
            ]);
        }

    }

    /**
     * Deletes an existing Crud model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAfDelete($id)
    {
        $model = $this->findAFModel($id);
        $this->af->drop_sql_field($model->attributes);
        $model->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been deleted'));
        return $this->redirect(['af']);
    }

    public function actionAfOrder( )   {
        $post = Yii::$app->request->post( );
        if (isset( $post['key'], $post['pos'] ))   {
            $this->findAFModel( $post['key'] )->order( $post['pos'] );
        }
    }


    public function actionAfGetAllFields(){
        $post = Yii::$app->request->post('table');
        if(empty( $post )) {
            return array(
                'body' => date('Y-m-d H:i:s'),
                'success' => false,
            );
        }
        $result = AF::find()
            ->select('name , title')
            ->where(['table' => Yii::$app->request->post('table')])
            ->asArray()
            ->all();

        $res = array(
            'data' => \yii\helpers\ArrayHelper::map($result, 'name', 'title'),
            'success' => true,
        );

        return \yii\helpers\Json::encode($res);

    }

    protected function findAFModel($id)
    {
        if (($model = AF::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('app','The requested page does not exist.'));
        }
    }

    /**
     * Returns the class name.
     * @return string the name of the class.
     */
    public function getTableName()
    {
        if(!empty($this->table_name))
        {
            return $this->table_name;
        }

        $basename = basename(str_replace('\\','/' ,get_called_class()));
        return str_replace('Controller' , '' , $basename);
    }

    public function rulerValidate ($model){
        $fields = AF::find()
            ->where(['table' => strtolower($this->table_name)])
            ->andWhere(['!=', 'c_option', ''])
            ->asArray()
            ->all();
        $rb = new RuleBuilder();
        if(is_array($fields) && count($fields) > 0){
            //loop all fields to validate
            foreach($fields as $k => $col){

                //only validate if this field allowed to be displayed in backend
                $col['admin_display'] = explode(', ',$col['admin_display']);

                if(in_array('update',$col['admin_display']) || in_array(key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId())),$col['admin_display']) ){
                    $user_value = $model->attributes[$col['name']];
                    $c_if = unserialize($col['c_if']);
                    $c_condition = unserialize($col['c_condition']);
                    $c_value = unserialize($col['c_value']);

                    //loop actions
                    foreach($c_if as $i => $logic){

                        unset($operators,$values);
                        //loop conditions in action
                        foreach($c_condition[$i] as $ii => $condition){
                            $condition = '\backend\lib\Ruler\Operator\\'.ucfirst($condition);
                            $operators[] = new $condition(
                                new \backend\lib\Ruler\Variable($col['name'].'_user_'.$ii, $user_value),
                                new \backend\lib\Ruler\Variable($col['name'].'_name_'.$ii,$c_value[$i][$ii])
                            );
                        }

                        $logic = '\backend\lib\Ruler\Operator\\'.ucfirst($logic);
                        $rule = new \backend\lib\Ruler\Rule(
                            new  $logic($operators),
                            function() use ( $i , $col , $user_value , $model ) {

                                $c_action = unserialize($col['c_action']);
                                $c_option = unserialize($col['c_option']);

                                switch($c_action[$i]){
                                    case 'update_field':
                                        $c_table = unserialize($col['c_table']);
                                        $c_field = unserialize($col['c_field']);
                                        //update field in same table
                                        if($col['table'] == $c_table[$i]){
                                            //check if the value is method , then call it
                                            if(method_exists(get_class($this),$c_option[$i])){
                                                $function = $c_option[$i];
                                                $class = get_class($this);
                                                $user_value = $class::$function($user_value);
                                                $u = $c_field[$i];
                                                //updating database
                                                $model->$u = $user_value;
                                            }
                                        }

                                        break;
                                    case 'prevent':
                                        Yii::$app->session->addFlash('error', Yii::t('app', $c_option[$i]));
                                        $this->ruler_data = false;
                                        break;
                                    case 'sms':
                                        break;
                                    case 'email':
                                        $c_template = unserialize($col['c_template']);
                                        $c_user = unserialize($col['c_user']);
                                        break;
                                }
                            }

                        );

                        $rule->execute(new \backend\lib\Ruler\Context());

                    }
                }
            }
            if(false === $this->ruler_data){
                return false;
            }
        }
        return true;
    }

    /**
     * @param $model The model of parent
     * @param $class The class name of parent
     */
    public function relatedDynamic($model,$class){

        //Add related form
        Yii::$app->on('backend/crud/form/end', function ($event) use($model,$class) {

            echo Html::beginTag('div',['class' => ' col-xs-12 col-sm-6 col-md-6 col-lg-6']);
            BackendWidget::begin(
                [
                    'icon' => 'user',
                    'title' => $this->getBasenameClass($class),
                    'footer' => FALSE,
                ]
            );

            $modelsRelated= $class::find()->where([$class::getDynamicPrimaryId() => $model->id])->all();
//            $modelsRelated->setTableName('stock');
            $postData = Yii::$app->request->post($this->getBasenameClass($class));
            if($postData !== null && is_array($postData) && $this->dynamicViewMode == false) {
                $modelsRelated = $this->updateRelatedDynamic($model , $modelsRelated , $class);
            }

            echo $this->renderPartial('@SmartBuilder/views/crud/related-dynamic', [
                'dataProvider' =>  new ActiveDataProvider(['query' => $class::find()->where([$class::getDynamicPrimaryId() => $model->id])->indexBy('id')]),
                'models' => (empty($modelsRelated)) ? [new $class()] : $modelsRelated,
                'model' => $model,
                'tabel' => strtolower($this->getBasenameClass($class))
            ]);


            BackendWidget::end();
            echo Html::endTag('div');
        });
    }

    /**
     * @param $model
     * @param $modelsRelated
     * @param $class
     * @return mixed
     */
    public function updateRelatedDynamic($model,$modelsRelated , $class){

        $oldIDs = ArrayHelper::map($modelsRelated, 'id', 'id');

        $modelsRelated = Model::createMultiple($class::classname(), $modelsRelated);
        Model::loadMultiple($modelsRelated, Yii::$app->request->post());

        $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelsRelated, 'id', 'id')));

        // validate all models
        $valid = Model::validateMultiple($modelsRelated);

        if ($valid) {

            if (! empty($deletedIDs)) {
                $class::deleteAll(['id' => $deletedIDs]);
            }
            $primaryID = $class::getDynamicPrimaryId();
            foreach ($modelsRelated as $modelRelated) {
                $modelRelated->$primaryID = $model->id;
                $modelRelated->save(false);
            }

        }
        return $modelsRelated;
    }

    /**
     * Get Class name without namespaces
     * @param $class
     * @return mixed
     */
    public function getBasenameClass($class) {
        $path = explode('\\', $class);
        return array_pop($path);
    }

    public function actionReorderAfTree(){

        if(isset($_POST['order'])){

            foreach($_POST['order'] as $id => $order){
                if($order == ''){
                    continue;
                }
                $b = AF::findOne($id);
                $b->ord = $order;
                $b->save(false);
            }
        }
    }

}

<?php

namespace  backend\modules\smartbuilder\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use \PetrGrishin\ArrayField\ArrayAccessFieldBehavior;

/**
 * This is the model class for table "{{%Crud}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $des
 * @property string $test
 */
class AF extends ActiveRecord
{

    /*
     * configuration
     */
    var $config;

    var $parent_table;

    /**
     * @var string default Workflow Id for Primary workflow
     */
    var $w1;

    /**
     * @var string default Workflow Id for secondry workflow
     */
    var $w2;

    private static $identity_map = [];

    public static $table_name;

    public function __construct($parent_table = null)
    {
        if(!is_null($parent_table)){
            $this->parent_table = $parent_table;
        }
        parent::__construct([]);

    }

    public function init($parent_table = null)
    {
        parent::init();

        // we check if there a record for configuration in the table , if not then insert new record
        $config = Yii::$app->cache->get("af:all");
        if (false === is_array($config)) {
            $config = static::find()->where(['table' => self::tableName()])->asArray()->all();
            Yii::$app->cache->set(
                "af:all",
                $config,
                86400,
                new TagDependency([
                    'tags' => [
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                    ]
                ])
            );
            //die( \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className()));
        }
        if (!empty($config)) {
            $this->config = $config;
        } else {
            $this->config = '';
        }
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title','name','sql_type','field_type'], 'required'],
            [['table','description','validate_func','custom_func','default','show_time'], 'string', 'max' => 255],
            ['sql_query', 'string'],
            [['cols','rows','width','height','width2','height2','enable_condition','enable_workflow','size'], 'integer'],
            [['c_action','c_if','c_condition','c_value','c_table','c_field','c_option','c_template','c_user',
              'admin_display','display','options','wf_field_index','wf_field_update','wf_field_view',
              'wf_definition_from','wf_definition_to','wf_initial','wf_definition_initial','wf_view_all',
              'wf_view_by_owner','wf_view_audit_trail','wf_assign','wf_validate_from','wf_validate_to','wf_enable_md','wf_ignore_md','wf_fields_md'] , 'safe'],
            [['name'],'match', 'pattern' => '/^[a-z0-9_]+$/' , 'enableClientValidation' => false , 'message' => Yii::t('app','Name must be entered and it may contain lowercase letters, underscopes and digits')],
            // [['title'],'match', 'pattern' => '/.+/' , 'enableClientValidation' => false , 'message' => Yii::t('app','Name must be entered and it may contain lowercase letters, underscopes and digits')],
            ['name' , 'in' , 'not' =>  true  , 'range' => ['id','sort','create_time','update_time'] , 'message' => Yii::t('app','Please choose another field name. This is already used') ],
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAf($af)
    {
        $model = static::findOne(['table' => self::tableName()]);

        if ($model === null) {
            $model = new static();
            //$model->active = 1;
        }
        $model->table  = self::tableName();
        $model->key = $key;
        $model->value = $value;

        if ($type !== null) {
            $model->type = $type;
        } else {
            $model->type = gettype($value);
        }

        return $model->save();
    }
    public function add_af($af)
    {
        $new_fileds = new AF;
        $new_fileds->name = $af->name;
        $new_fileds->title = $af->name;
        $new_fileds->table = strtolower($this->parent_table);
        $new_fileds->field_type = 'text';
        $new_fileds->sql = 'Y';
        $new_fileds->description = $af->comment;
        $new_fileds->sql_type = $af->dbType;
        try {
            $xx = $new_fileds->save(false);
        } catch (\yii\db\Exception $e) {
            print_r($e);die();
        }
    }

    public function get_field_from_saved($vars) {
        // get default
        #print"<pre>";print_r($vars);die();
        if(isset($vars['additional_fields'])){
            $vars = $vars['additional_fields'];
        }
        $field = Yii::createObject(__CLASS__);

        $field->id = isset($vars['id'])?$vars['id']:NULL;
        $field->name = $vars['name'];
        $field->name = $vars['name'];
        $field->title = $vars['title'];
        $field->field_type = $vars['field_type'];
        $field->description = $vars['description'];
        $field->validate_func = isset($vars['validate_func'])?$vars['validate_func']:'';
        $field->sql = (isset($vars['sql'])) ? $vars['sql'] :'';
        $field->sql_type = (isset($vars['sql_type'])) ? $vars['sql_type'] : '';
        $field->sql_query = (isset($vars['sql_query'])) ? $vars['sql_query'] : '';
        $field->custom_func =(isset($vars['custom_func'])) ? $vars['custom_func'] :'';
        $field->size = (isset($vars['size'])) ? $vars['size'] : '';
        $field->default = (isset($vars['default'])) ? $vars['default'] : '';
        $field->options = (isset($vars['options'])) ? (array) $vars['options'] : array();
        $field->cols = (isset($vars['cols'])) ? $vars['cols'] : '';
        $field->rows =(isset($vars['rows'])) ? $vars['rows'] : '';
        $field->width =(isset($vars['width'])) ? $vars['width'] : '';
        $field->height = (isset($vars['height'])) ? $vars['height'] : '';
        $field->width2 = (isset($vars['width2'])) ? $vars['width2'] : '';
        $field->height2 =(isset($vars['height2'])) ? $vars['height2'] : '';
        $field->display = (isset($vars['display'])) ? $vars['display'] : '';
        $field->show_time = (isset($vars['show_time'])) ? $vars['show_time'] : '';
        $field->admin_display = (isset($vars['admin_display'])) ? $vars['admin_display'] : '';

        return $field;
    }





    function get_additional_fields() {

        if (count($this->config) > 0 && is_array($this->config)) {
            foreach ($this->config as $f) {
                if (isset($f['hidden_anywhere']))
                    continue;
                /* if (in_array($f['name'], array(
                  'cc_city', 'cc_company', 'cc_country', 'cc_name_f', 'cc_name_l',
                  'cc_phone', 'cc_state', 'cc_zip', 'is_locked', 'is_approved'
                  )))
                  continue; */
                $fl[$f['name']] = $f;
                $fl[$f['name']]['from_config'] = 1;
            }
        }
        //usort($fl, 'cmp_fields');
        return $fl;
    }

    /**
     * @inheritdoc
     */
    public function setParentTableName($table_name)
    {
        $this->parent_table = $table_name;
    }


    /**
     *
     * @param string $field_name
     * @return bealon false
     */
    function get_config($field_name) {
        if (is_array($this->config) && count($this->config) > 0) {
            foreach ($this->config as $k => $v) {
                if ($v['name'] == $field_name) {
                    return $v;
                }
            }
        }
        return false;
    }


    public function findByName($name)
    {
        if(empty($this->parent_table)){
            return false;
        }
        $key = $this->parent_table.":$name";
        //TODO :: cache is not working correct if table was new
//        if (!isset(static::$identity_map[$key])) {
            static::$identity_map[$key] = Yii::$app->cache->get($key);
//            if (!is_object(static::$identity_map[$key]) && static::$identity_map[$key] != 'none') {
                $r = $this->find()
                    ->where(['name' => $name , 'table' => $this->parent_table])
                    ->one();
                static::$identity_map[$key] = (is_null($r) || empty($r))?'none':$r;
                //if (is_object(static::$identity_map[$key])) {
                    Yii::$app->cache->set(
                        $key,
                        static::$identity_map[$key],
                        86400,
                        new \yii\caching\TagDependency([
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                            ]
                        ])
                    );
                //}
//            }
//        }
        $result = static::$identity_map[$key];
        if (is_object($result)) {
            return $result;
        }else{
            return false;
        }
    }

    public function getAll($where = array() ,$x = false)
    {

        $result = $this->find()->where(['table' => $this->parent_table]);

        if(is_string($where)){
            $result->andWhere($where);
        }elseif(is_array($where) && count($where) > 0){
            foreach($where as $k => $v){
                $result->andWhere($v);
            }

        }

        $r = $result->orderBy( 'ord' )->all();


        if (is_array($r)) {
            return $r;
        }else{
            return false;
        }
    }

    public function add_sql_field($data) {

        foreach ( Yii::$app->db->schema->getTableSchema($this->parent_table)->columns  as $k => $v) {
            if (strcasecmp($k, $data['name']) == 0){
                    die( "Field '$name' is already exists in table $this->parent_table");
            } else {
                continue;
            }

        }
        // actually add field
        $this->db->createCommand( "ALTER TABLE `$this->parent_table` ADD `".$data['name']."`  ".$data['sql_type']." ")->execute();
        if (mysql_errno()) {
            die( "Couldn't add field - mysql error: " . mysql_error() );
        }
    }

    public function change_sql_field($data) {
        // actually add field
        $this->db->createCommand($s = "ALTER TABLE `$this->parent_table` CHANGE `".$data->oldAttributes['name']."` `".$data->attributes['name']."` ".$data->attributes['sql_type']."")->execute();
        if (mysql_errno()) {
            die( "Couldn't change field type - mysql error: " . mysql_error());
        }
    }

    public function drop_sql_field($data) {
        $this->db->createCommand($s = "ALTER TABLE `$this->parent_table` DROP  `".$data['name']."` ")->execute();
        if (mysql_errno()) {
            die( "Couldn't drop field - mysql error: " . mysql_error());
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
        ];
    }


    /**
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getAllTables(){
        $result = AF::find()->select('table')->groupBy('table')->all();
        if (is_array($result)) {
            return $result;
        }else{
            return false;
        }
    }

    public function getWorkflowFieldsIds($column = 'wf_field_update') {
        $status = $this->getStatusField();
        if(empty($status[$column])){
            return [];
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        //if ability to display fields on specific transition is enabled ?
        if($column == 'wf_field_update' && (isset($status['wf_enable_md'][$role]) && $status['wf_enable_md'][$role]) ){

        }
        $status[$column] = is_array($status[$column])?$status[$column]:unserialize($status[$column]);

        $r = (isset($status[$column][$role]) && is_array($status[$column][$role]))?$status[$column][$role]:[];
        return $r;
    }

    public function isWorkflowEnabled(){
        $status = $this->getStatusField();
        return (1 == $status['enable_workflow'])?true:false;
    }

    public function isWorkflow2Enabled(){
        $status = $this->getStatusExField();
        return (1 == $status['enable_workflow'])?true:false;
    }

    public function getWorkflowAllowedTransitions($value){
        $status = $this->getStatusField();
        if(empty($status['wf_definition_from']) || empty($status['wf_definition_to'])){
            return [];
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_definition_from'] = is_array($status['wf_definition_from'])?$status['wf_definition_from']:unserialize($status['wf_definition_from']);
        $status['wf_definition_to'] = is_array($status['wf_definition_to'])?$status['wf_definition_to']:unserialize($status['wf_definition_to']);
        $status['wf_definition_initial'] = is_array($status['wf_definition_initial'])?$status['wf_definition_initial']:unserialize($status['wf_definition_initial']);

        $finalStatuses = [];
        //show initial status if this is a new record
        if(isset($status['wf_definition_initial'][$role]) && !empty($status['wf_initial']) && empty($value)){
            $finalStatuses[$this->getWorkflowId($status['wf_initial'])] = $status['wf_initial'];
        }
        if(isset($status['wf_definition_from'][$role])) {
            foreach ($status['wf_definition_from'][$role] as $k => $statusFrom) {
                if ($statusFrom == $value) {
                    //if transition field has more than one transition then explode it into array
                    if (strpos($status['wf_definition_to'][$role][$k], ',') !== false) {
                        $trans = explode(', ', $status['wf_definition_to'][$role][$k]);
                        foreach ($trans as $_status) {
                            $finalStatuses[$this->getWorkflowId($_status)] = $_status;
                        }
                        continue;
                    }
                    $finalStatuses[$this->getWorkflowId($status['wf_definition_to'][$role][$k])] = $status['wf_definition_to'][$role][$k];
                }

            }
        }
        return $finalStatuses;
    }

    public function getWorkflowAllowedStatus(){
        $status = $this->getStatusField();
        if(empty($status['wf_definition_from'])){
            return [];
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $wf_definition_from = is_array($status['wf_definition_from'])?$status['wf_definition_from']:unserialize($status['wf_definition_from']);

        //remove initial status if not assigned to it
        $final_wf_definition_from = $this->cleanUnusedStatus($wf_definition_from);

        return (isset($final_wf_definition_from[$role]))?$final_wf_definition_from[$role]:[];
    }

    public function cleanUnusedStatus($wf_definition_from){
        $_status = $this->getStatusField();
        $initial = unserialize($_status['wf_definition_initial']);
        $wf_definition_to = is_array($_status['wf_definition_to'])?$_status['wf_definition_to']:unserialize($_status['wf_definition_to']);
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));

        if(isset($wf_definition_from[$role]) && isset($wf_definition_to[$role]) ) {

            foreach ($wf_definition_from[$role] as $k => $v) {
                if(!empty($v) && empty($wf_definition_to[$role][$k])){
                    unset($wf_definition_from[$role][$k]);
                }

            }
        }
        return $wf_definition_from;
    }

    public function getInitialStatus(){
        $status = $this->getStatusField();
        if(empty($status['wf_initial'])){
            return false;
        }
        return $status['wf_initial'];
    }

    public function isAllowedAllStatus(){
        $status = $this->getStatusField();
        if(empty($status['wf_view_all'])){
            return false;
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_view_all'] = is_array($status['wf_view_all'])?$status['wf_view_all']:unserialize($status['wf_view_all']);
        return (isset($status['wf_view_all'][$role]) && 1 == $status['wf_view_all'][$role])?true:false;
    }

    public function isAllowedMyCreated(){
        $status = $this->getStatusField();
        if(empty($status['wf_view_by_owner'])){
            return false;
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_view_by_owner'] = is_array($status['wf_view_by_owner'])?$status['wf_view_by_owner']:unserialize($status['wf_view_by_owner']);
        return (isset($status['wf_view_by_owner'][$role]) && 1 == $status['wf_view_by_owner'][$role])?true:false;
    }

    public function isEnabledAssignId(){
        $status = $this->getStatusField();
        if(empty($status['wf_assign'])){
            return false;
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_assign'] = is_array($status['wf_assign'])?$status['wf_assign']:unserialize($status['wf_assign']);
        return (isset($status['wf_assign'][$role]) && 1 == $status['wf_assign'][$role])?true:false;
    }

    public function isEnabledMultiTransationDisplay(){
        $status = $this->getStatusField();
        if(empty($status['wf_enable_md'])){
            return false;
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_enable_md'] = is_array($status['wf_enable_md'])?$status['wf_enable_md']:unserialize($status['wf_enable_md']);
        return (isset($status['wf_enable_md'][$role]) && 1 == $status['wf_enable_md'][$role])?true:false;
    }
    public function isTransationIgnored($model){

        $status = $this->getStatusField();
        if(empty($status['wf_ignore_md']) || empty($status['wf_definition_from'])){
            return false;
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_ignore_md'] = is_array($status['wf_ignore_md'])?$status['wf_ignore_md']:unserialize($status['wf_ignore_md']);
        $status['wf_definition_from'] = is_array($status['wf_definition_from'])?$status['wf_definition_from']:unserialize($status['wf_definition_from']);
        if(isset($status['wf_definition_from'][$role]) && sizeof($status['wf_definition_from'][$role]) > 0 ){

            //get current status
            if(!isset($model->status) || empty($model->status)){
                if(sizeof($model->getBehavior('w1')) > 0){
                    $this->w1 = $model->getBehavior('w1')->defaultWorkflowId;
                }
                $currentStatus = $this->getWorkflowId($this->getInitialStatus());
            }else{
                $currentStatus = $model->status;
            }


            foreach($status['wf_definition_from'][$role] as $k => $v){
               //get key for current status
                if($currentStatus == $v){
                    //if key is exists in ignore array , means ignore is enabled for this field in this status
                    if(array_key_exists($k, $status['wf_ignore_md'][$role])){
                        return true;
                    }else{
                        return false;
                    }
                }

            }
        }
        return false;
    }

    public function getTransationKey($model){

        $status = $this->getStatusField();
        if(empty($status['wf_definition_from'])){
            return false;
        }
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        $status['wf_definition_from'] = is_array($status['wf_definition_from'])?$status['wf_definition_from']:unserialize($status['wf_definition_from']);
        if(isset($status['wf_definition_from'][$role]) && sizeof($status['wf_definition_from'][$role]) > 0 ){

            foreach($status['wf_definition_from'][$role] as $k => $v){
                //get key for current status
                if($model->status == $v){
                    return $k;
                }

            }
        }
        return false;
    }

    public function getWorkflowId($status){
        //if workflow id is not isset , we try to fetch from any current status
        if(empty($this->w1)) {
            $_status = $this->getStatusField();
            if(empty($_status['wf_definition_from'])){
                return false;
            }
            $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
            $_status['wf_definition_from'] = is_array($_status['wf_definition_from'])?$_status['wf_definition_from']:unserialize($_status['wf_definition_from']);
            if(isset($_status['wf_definition_from'][$role])){
                $path = explode('/', $_status['wf_definition_from'][$role][0]);
                $this->w1 = $path[0];
                return $path[0]."/".$status;
            }

        }
        return $this->w1.'/'.$status;
    }
    public function isValidWorkflowId($val)
    {
        return is_string($val) && preg_match('/^[a-zA-Z]+[[:alnum:]-]*$/', $val) != 0;
    }
    public function isValidStatusLocalId($val)
    {
        return is_string($val) && preg_match('/^[a-zA-Z]+[[:alnum:]-]*$/', $val) != 0;
    }

    public function getStatusField(){
        $key = $this->parent_table.":status";
        if (!isset(static::$identity_map[$key])) {

            static::$identity_map[$key] = Yii::$app->cache->get($key);
            if (!is_object(static::$identity_map[$key])) {
                static::$identity_map[$key] = (new \yii\db\Query())->from('af')->where(['table' => $this->parent_table])->andWhere(['name' =>'status'])->one();
                if (is_object(static::$identity_map[$key])) {
                    Yii::$app->cache->set(
                        $key,
                        static::$identity_map[$key],
                        86400,
                        new \yii\caching\TagDependency([
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                            ]
                        ])
                    );
                }
            }
        }
        return static::$identity_map[$key];
    }

    public function getStatusExField(){
        $key = $this->parent_table.":status_ex";
        if (!isset(static::$identity_map[$key])) {

            static::$identity_map[$key] = Yii::$app->cache->get($key);
            if (!is_object(static::$identity_map[$key])) {
                static::$identity_map[$key] = (new \yii\db\Query())->from('af')->where(['table' => $this->parent_table])->andWhere(['name' =>'status_ex'])->one();
                if (is_object(static::$identity_map[$key])) {
                    Yii::$app->cache->set(
                        $key,
                        static::$identity_map[$key],
                        86400,
                        new \yii\caching\TagDependency([
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                            ]
                        ])
                    );
                }
            }
        }
        return static::$identity_map[$key];
    }
}

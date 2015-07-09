<?php

namespace  SmartBuilder\models;

use raoul2000\workflow\validation\WorkflowValidator;
use Yii;
use yii\base\Event;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%Crud}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $des
 * @property string $test
 */
class Crud extends \yii\db\ActiveRecord
{

    public static $table_name;

    /**
     * @inheritdoc
     */
    public static function setTableName($table_name)
    {
        self::$table_name = $table_name;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return self::$table_name;
    }

    public function behaviors()
    {
        return  [
            'LoggableBehaviour' => [
                'class' => 'sammaye\audittrail\LoggableBehavior',
                'ignored' => ['id','create_time','update_time','created_by'], // This ignores fields from a selection of all fields, not needed with allowed
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        $af = (new AF(strtolower(self::$table_name)));
        $where = [];
        //only display specific fields if workflow is enabled
        $workflow = $workflow2 = false;
        if(true === $af->isWorkflowEnabled()){
            $update_ids = $af->getWorkflowFieldsIds();
            $view_ids = $af->getWorkflowFieldsIds('wf_field_view');
            $final_ids = array_diff($update_ids,$view_ids);
            $where = [
                ['in', 'id', $final_ids],
            ];
            $workflow = true;
        }
        if(true === $af->isWorkflow2Enabled()){
            $workflow2 = true;
        }

        $rules = $af->getAll($where);
        $require = $integer = $number = $safe = $email = [];
        if(count($rules) > 0){
            foreach($rules as $v) {
                switch ($v->attributes['validate_func']) {
                    case 'require':
                        if (Yii::$app->hasEventHandlers('backend/model/crud/rule/require/' . $v->attributes['name'])) {
                            Yii::$app->trigger('backend/model/crud/rule/require/' . $v->attributes['name'], new Event(['sender' => ['rule' => $v]]));
                        } else {
                            $require[] = $v->attributes['name'];
                        }
                        break;
                    case 'integer':
                        $integer[] = $v->attributes['name'];
                        break;
                    case 'number':
                        $number[] = $v->attributes['name'];
                        break;
                    case 'email':
                        $email[] = $v->attributes['name'];
                        break;
                    default:
                        if (true === $workflow && $v->attributes['name'] == 'status') {
                            $other = [
                                [['status'],WorkflowValidator::className()],
                            ];
                        }elseif(true === $workflow2 && $v->attributes['name'] == 'status_ex'){
                            $other2 = [
                                [['status_ex'],RelatedWorkflowValidator::className()]
                            ];
                        } else {
                            $safe[] = $v->attributes['name'];
                        }
                        break;
                }
            }

        }
        $return = [
            [$require,'required'],
            [$integer,'integer'],
            [$number,'number'],
            [$email,'email'],
            [$safe,'safe']
        ];

        if(isset($other) && count($other) > 0 && is_array($other)){
            $return = array_merge($return,$other);
        }
        if(isset($other2) && count($other2) > 0 && is_array($other2)){
            $return = array_merge($return,$other2);
        }

        return $return;
    }


    function alter_table($found){
        if (!isset($found['create_time']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP")->execute();
        if (!isset($found['update_time']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP")->execute();
        if (!isset($found['status']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `status` VARCHAR( 70 ) NOT NULL")->execute();
        if (!isset($found['status_ex']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `status_ex` VARCHAR( 70 ) NOT NULL")->execute();
        if (!isset($found['sort']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `sort` INT NOT NULL")->execute();
        if (!isset($found['id']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `id` INT NOT NULL")->execute();
        if (!isset($found['title']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `title` VARCHAR( 70 ) NOT NULL")->execute();
        if (!isset($found['created_by']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `created_by` INT NOT NULL DEFAULT '0'")->execute();
        if (!isset($found['assign_id']))
            $this->db->createCommand("ALTER TABLE `".self::tableName()."` ADD `assign_id` INT NOT NULL DEFAULT '0'")->execute();
//        if (!isset($found['tag']))
//            $this->db->query("ALTER TABLE ! ADD `tag` VARCHAR( 255 ) NOT NULL", array($this->table_name));
//        if (!isset($found['dsc']))
//            $this->db->query("ALTER TABLE ! ADD `dsc` VARCHAR( 355 ) NOT NULL", array($this->table_name));
    }

    public function getDefinition() {
        $status = (new \yii\db\Query())->from('af')->where(['table' => self::tableName()])->andWhere(['name' =>'status'])->one();
        if(empty($status['wf_initial']) || empty($status['wf_definition_from'])  || empty($status['wf_definition_to']) ){
            return [];
        }
        $status['wf_definition_from'] = unserialize($status['wf_definition_from']);
        $status['wf_definition_to'] = unserialize($status['wf_definition_to']);
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));

        $definition =  [
            'initialStatusId' => $status['wf_initial'],
        ];
        $workflowID  = str_replace('backend\models\\','',basename(get_called_class()));
        if(isset($status['wf_definition_from'][$role]) && is_array($status['wf_definition_from'][$role]) && count($status['wf_definition_from'][$role]) > 0) {
            foreach ($status['wf_definition_from'][$role] as $k => $transition) {
                $transition = str_replace($workflowID . 'Workflow/', '', $transition);
                if (!isset($status['wf_definition_to'][$role][$k]) || empty($status['wf_definition_to'][$role][$k])) {
                    $definition['status'][$transition] = '';
                } else {
                    $definition['status'][$transition]['transition'] = $status['wf_definition_to'][$role][$k];
                }

            }
        }else{
            $definition['status'] = [];
        }

        $_action = Yii::$app->urlManager->parseRequest(Yii::$app->request);
        $action = $this->getAction($_action[0]);
        return $definition;
    }
    public function getDefinition2() {

        $status = (new \yii\db\Query())->from('af')->where(['table' => self::tableName()])->andWhere(['name' =>'status_ex'])->one();
        if(empty($status['wf_initial']) || empty($status['wf_definition_from'])  || empty($status['wf_definition_to']) ){
            return [];
        }
        $status['wf_definition_from'] = unserialize($status['wf_definition_from']);
        $status['wf_definition_to'] = unserialize($status['wf_definition_to']);
        $role = key(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));

        $definition =  [
            'initialStatusId' => $status['wf_initial'],
        ];
        $workflowID  = str_replace('backend\models\\','',basename(get_called_class()));
        if(isset($status['wf_definition_from'][$role]) && is_array($status['wf_definition_from'][$role]) && count($status['wf_definition_from'][$role]) > 0) {
            foreach ($status['wf_definition_from'][$role] as $k => $transition) {
                $transition = str_replace($workflowID . 'Workflow/', '', $transition);
                if (!isset($status['wf_definition_to'][$role][$k]) || empty($status['wf_definition_to'][$role][$k])) {
                    $definition['status'][$transition] = '';
                } else {
                    $definition['status'][$transition]['transition'] = $status['wf_definition_to'][$role][$k];
                }

            }
        }else{
            $definition['status'] = [];
        }

        return $definition;
    }

    public function getAction($class) {
        $path = explode('/', $class);
        return array_pop($path);
    }

    /**
     * Returns the actual name of a given table name.
     * This method will strip off curly brackets from the given table name
     * and replace the percentage character '%' with [[Connection::tablePrefix]].
     * @param string $name the table name to be converted
     * @return string the real name of the given table name
     */
    public function getRawTableName($name)
    {
        if (strpos($name, '{{') !== false) {
            $name = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $name);

            return str_replace('%', $this->db->tablePrefix, $name);
        } else {
            return $name;
        }
    }

    public function getfields()
    {
        $query = \backend\models\AF::find()->where(['table' => $this->getRawTableName(self::tableName())]);
        return $query;
    }
}

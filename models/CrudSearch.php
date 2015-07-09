<?php

namespace  SmartBuilder\models;

use kartik\dynagrid\DynaGridStore;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * CrudSearch represents the model behind the search form about `backend\models\Crud`.
 */
class CrudSearch extends Crud
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $test = array();
        foreach( $this->getTableSchema()->columns as $name => $value){
            $test[] = array(
                array($name),
                is_integer($value->type)?'integer':'string'
            );
        }
        return array_merge([[['id'], 'integer']],$test);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $af = new AF(strtolower(self::$table_name));
        $isWorkflowEnabled = $af->isWorkflowEnabled();
        $query = Crud::find();

        //if workflow is enabled , only show results with status that current user can access to it
        if(true === $isWorkflowEnabled){
            //if status was selected , go inside
            if(isset($_GET['CrudSearch']['status']) && !empty($_GET['CrudSearch']['status'])){
                //We need this to check if no condition is met , then call the default allowed status
                $access = false;

                //if myItem status is selected and user have access to it , allow for him
                if($_GET['CrudSearch']['status'] == 'myItems' && $af->isAllowedMyCreated()){
                    $query->andFilterWhere(['created_by' => Yii::$app->user->id]);
                    $access = true;
                }

                //if another status is selected and not in his access role but created by him , allow for him
                if($_GET['CrudSearch']['status'] != 'myItems' && $af->isAllowedMyCreated()){
                    $query->andFilterWhere(['status' => $_GET['CrudSearch']['status']]);
                    $query->andFilterWhere(['created_by' => Yii::$app->user->id]);
                    $access = true;
                }

                //if other status is selected and not in his access role but he has option to view all status , also allow for him
                if($_GET['CrudSearch']['status'] != 'all' && $af->isAllowedAllStatus() ){
                    $query->andFilterWhere(['status' => $_GET['CrudSearch']['status']]);
                    $access = true;
                }

                //if status was all and he has option to view all status ,allow for him
                if($_GET['CrudSearch']['status'] == 'all' && $af->isAllowedAllStatus()){
                    $access = true;
                }


                //if no condition above is met , call the default allowed status
                if(false === $access){
                    $status = $af->getWorkflowAllowedStatus();
                    $query->andFilterWhere(['in', 'status', $status]);
                }

            }else{
                //We need this to check if no condition is met , then call the default allowed status
                $access = false;

                //if other search params is entered and record is not in his access role but created by him , allow for him
                if(isset($_GET['CrudSearch']) && false === $this->isSearchParamsEmpty($_GET['CrudSearch']) && $af->isAllowedMyCreated()){
                    $query->andFilterWhere(['created_by' => Yii::$app->user->id]);
                    $access = true;
                }

                //if other search params is entered and record is not in his access role but he has option to view all status , allow for him
                if(isset($_GET['CrudSearch']) && false === $this->isSearchParamsEmpty($_GET['CrudSearch']) && $af->isAllowedAllStatus()){
                    $access = true;
                }

                //if no condition above is met , call the default allowed status
                if(false === $access){
                    $status = $af->getWorkflowAllowedStatus();
                    $query->andFilterWhere(['in', 'status', $status]);
                }
            }

            //if ability to Assign record to an agent and hide from other users in same role group, is enabled
            //and allow to view record by the owner of record is not enable for this role group
            //and allow to view all statuses is not enable too for this role group
            //then only display records where assign_id equal 0 and to assigned agent and hide from the rest
            if( (false === $af->isAllowedMyCreated() &&  false === $af->isAllowedAllStatus()) && $af->isEnabledAssignId() ){
                $query->andFilterWhere(['in', 'assign_id', [0,Yii::$app->user->id]]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $rules = $af->getAll();
        $require = $integer = $number = $safe = $email = [];
        if(count($rules) > 0 && is_array($rules)){
            $query->andFilterWhere([
                'id' => $this->id,
            ]);
            foreach($rules as $v) {
                unset($name);
                $name = $v->attributes['name'];
                if( $name == 'status' && true === $isWorkflowEnabled && $this->getCookieStatus() === false ){
                    continue;
                }
                $query->andFilterWhere(['like', $name, $this->$name]);
            }
        }

        return $dataProvider;
    }

    public  function getCookieStatus(){
        $store = new DynaGridStore([
            'id' => strtolower(self::$table_name).'-grid',
            'storage' => 'cookie',
            'userSpecific' => true,
            'category' => 'grid'
        ]);
        $dtlkey = $store->fetch();

        if(isset($dtlkey['filter'])){
            $_store = new DynaGridStore([
                'id' => strtolower(self::$table_name).'-grid',
                'storage' => 'cookie',
                'userSpecific' => true,
                'category' => 'filter',
                'dtlKey' => $dtlkey['filter'],
            ]);
            $cookie = $_store->fetch();
            if(isset($cookie['status'])){
                return $cookie['status'];
            }

        }
        return false;

    }

    public function isSearchParamsEmpty($data){
        if(sizeof($data) > 0) {
            foreach ((array)$data as $k => $v) {
                if (!empty($v)) {
                    return false;
                }
            }
        }
        return true;
    }
}

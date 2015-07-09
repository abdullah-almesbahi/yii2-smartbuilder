<?php
/**
 * @link http://phe.me
 * @copyright Copyright (c) 2014 Pheme
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace  SmartBuilder\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SettingSearch represents the model behind the search form about `pheme\settings\models\Setting`.
 *
 * @author Aris Karageorgos <aris@phe.me>
 */
class AFSearch extends AF
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['active'], 'boolean'],
            [['type', 'section', 'key', 'value', 'created', 'modified','condition'], 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = AF::find()->where(['table' => $this->parent_table])->orderBy( 'ord' );
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }



        return $dataProvider;
    }
}

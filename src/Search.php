<?php
/**
 * Created by PhpStorm.
 * User: Abdel Aziz hassan
 * 5dmat-web.com
 * 5dmat-web team
 * Date: 3/3/17
 * Time: 10:13 PM
 */

namespace AdvanceSearch\AdvanceSearchProvider;


class Search
{

    protected $model;
    protected $fields;
    protected $limit;
    protected $value;
    protected $class;
    protected $type;
    protected $order;
    protected $select;

    /***
     * @param $model
     * @param $fields
     * @param string $value
     * @param null $orderBy
     * @param bool $responseType
     * @param int $limit
     * @return string
     */

    public function search($model , $fields , $value = "" , $select = null ,  $orderBy = null , $responseType = true , $limit = 10){
        $this->model = ucfirst($model);
        $this->fields = $fields;
        $this->limit  = $limit;
        $this->value = $value;
        $this->type = $responseType;
        $this->order = $orderBy;
        $this->select = $select;
        return $this->init();
    }

    /***
     * @return string
     */
    protected function init(){
        $value = $this->checkValue();
        if($value){
            return $this->checkIfModelExist();
        }
        return json_encode(['status' => 'false' , 'message' => "fill fields"]);
    }

    /***
     * @return string
     */
    protected function checkIfModelExist(){
        $this->checkFields();
        if($this->makeInstance()){
            return $this->searchProductsFulltext();
        }
        return json_encode(['status' => 'false' , 'message' => "this model not found"]);
    }

    /***
     * @return bool
     */

    protected function makeInstance(){
        $class = $this->nameSpaceExists();
        if($class){
            return $this->class = new $class();
        }
        return false;
    }


    protected function nameSpaceExists(){
        $class = '\\App\\Models\\' . $this->model;
        return class_exists($class) ? $class : false;
    }

    /***
     * @return string
     */
    protected function checkFields(){
        return $this->fields = is_array($this->fields) ? $this->getFields($this->fields) :  $this->getFields([$this->fields]);
    }

    /***
     * @return string
     */
    protected function checkSelect(){
        return $this->select = is_array($this->select) ? $this->select :  [$this->select];
    }



    /***
     * @param $fields
     * @return string
     */

    protected function getFields($fields){
        return implode(',' ,$fields);
    }


    /***
     * @return bool|string
     */
    protected function checkValue(){
        return $this->ValidationValue() == "" ? false : $this->ValidationValue();
    }

    /***
     * @return string
     */

    protected function ValidationValue(){
        return trim(strip_tags($this->value));
    }

    /***
     * @return array
     */
    protected function getOrderBy(){
      return is_array($this->order) ? $this->order : [$this->order , "desc" ];
    }

    /***
     * @param $string
     * @return mixed
     */
    protected function searchProductsFulltext()
    {
        $query = $this->class->where(function ($query) {
            $query->whereRaw("match(" . $this->fields . ") against ('/$this->value/' IN NATURAL LANGUAGE MODE)")
                ->orWhere('year_of_graduation', $this->value);
        });

        if($this->select != null){
            $query = $query->select($this->checkSelect());
        }

        if($this->order != null){
           $order = $this->getOrderBy();
           $query = $query->orderBy($order[0] , $order[1]);
        }
        if($this->type == true){
            return $query = $query->paginate($this->limit);
        }
        return $query;
    }
}

<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

/**
 * base_object class.
 */
class base_object extends core {

    public $id;

    /**
     *
     */
    public function __construct(){
        //none
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key) {
        return $this->$$key;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value) {
        $this->$$key = $value;
    }

    /**
     * @param $_id
     */
    public function getFromId($_id) {

        try {

            $db = new DB;
            $_id = (int)$_id;

            $item = $db->query("SELECT * FROM " . $this->table . " WHERE id = '$_id'");

            $result = $item->fetchAll();

            if ($result) {
                $this->setItem($result[0]);
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }

    /**
     * @return bool
     */
    public function save() {

        try {

            $db = new DB;

            $set = '';
            $values = array();

            foreach ($this->_fields as $field) {
                if ($set != '') $set .= ', ';
                $set .= "$field = :$field";
                $values[":$field"] = $this->$field;
            }

            if(empty($this->id)){
                $sql = "INSERT INTO " . $this->table . " SET " . $set;

            } else {
                $values[':id'] = $this->id;
                $sql = "UPDATE " . $this->table . " SET " . $set . " WHERE id = :id";
            }

            $db->query($sql, $values);

            return true;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }


    }


    /**
     * @return bool
     */
    public function delete() {
        $db = new DB;

        $_id = (int)$this->id;

        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        if ($db->query($sql, array(':id'=>$_id))) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $_item
     */
    public function setItem($_item) {
        foreach ($this->_fields as $field) {
            if (!empty($_item[$field]))
                $this->$field = $_item[$field];
        }
    }


    /**
     * @return array
     */
    public function getItem() {
        $item = array();
        foreach ($this->_fields as $field) {
            $item[$field] = $this->$field;
        }
        return $item;
    }


    /**
     * @return bool
     */
    public function toArray() {
        $return = false;

        if (!empty($this->_fields)) {
            foreach ($this->_fields as $field) {
                $return[$field] = $this->$field;
            }
        }

        if (!empty($this->_fields_extra)) {
            foreach ($this->_fields_extra as $field) {
                $return[$field] = $this->$field;
            }
        }

        return $return;
    }


    /**
     * @return string
     */
    public function toJson() {
        $return = $this->toArray();

        return json_encode($return);
    }


    /**
     * @return bool
     */
    public function toTwig() {
        if (method_exists($this, "twigify"))
            $return = $this->twigify();
        else
            $return = $this->toArray();

        return $return;
    }


    /**
     * @param $_format
     * @return $this|bool|string
     */
    public function format($_format) {
        switch ($_format) {
            case('json'):
                return $this->toJson();
                break;
            case('twig'):
                return $this->toTwig();
                break;
            case('array'):
                return $this->toArray();
                break;
           default:
                return $this;
                break;
        }
    }

}

?>
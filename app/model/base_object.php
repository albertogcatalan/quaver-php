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

    public function __construct(){

    }

    public $id;

    /**
     * get function.
     * 
     * @access public
     * @param mixed $key
     * @return void
     */
    public function get($key) {
        return $this->$$key;
    }

    /**
     * set function.
     * 
     * @access public
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value) {
        $this->$$key = $value;
    }

    /**
     * save function.
     * 
     * @access public
     * @return void
     */
    public function save() {
        $db = new DB;

        $item = $this->getItem();

        if (!empty($this->id)) {
            // UPDATE
            $return = $db->conn->update($item, $this->table, "id = '" . $this->id . "'");
        } else {
            // INSERT
            $return = $db->conn->insert($item, $this->table);
            $this->id = $db->conn->getLastId();
            $this->getFromId($this->id);
        }
        return $return;
    }

    /**
     * getFromId function.
     * 
     * @access public
     * @param mixed $_id
     * @return void
     */
    public function getFromId($_id) {
        $db = new DB;
        
        $_id = (int)$_id;

        $item = $db->conn->getArray("SELECT * FROM " . $this->table . " WHERE id = '$_id'");
        if (@$item) {
            $this->setItem($item[0]);
        }

        return $this;
    }
    
    /**
     * delete function.
     * 
     * @access public
     * @return void
     */
    public function delete() {
        $db = new DB;

        $return = false;

        if (!empty($this->id)) {
            $return = $db->conn->delete($this->id, $this->table);
        }
        
        return $return;
    }

    /**
     * getList function.
     * 
     * @access public
     * @return void
     */
    public function getList() {
        $db = new DB;

        $return = array();

        $items = $db->conn->getArray("SELECT id FROM " . $this->table . "");
        if (@$items)
            foreach ($items as $item) {
                $return[] = $db->loadClass($this->table)->getFromId($item['id']);
            }

        return @$return;
    }

    /**
     * setItem function.
     * 
     * @access public
     * @param mixed $_item
     * @return void
     */
    public function setItem($_item) {
        foreach ($this->_fields as $field) {
            if (!empty($_item[$field]))
                $this->$field = $_item[$field];
        }
    }

    /**
     * getItem function.
     * 
     * @access public
     * @return void
     */
    public function getItem() {
        $item = array();
        foreach ($this->_fields as $field) {
            $item[$field] = $this->$field;
        }
        return $item;
    }

    /**
     * toArray function.
     * 
     * @access public
     * @return void
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
     * toJson function.
     * 
     * @access public
     * @return void
     */
    public function toJson() {
        $return = $this->toArray();

        return json_encode($return);
    }

    /**
     * toTwig function.
     * 
     * @access public
     * @return void
     */
    public function toTwig() {
        if (method_exists($this, "twigify"))
            $return = $this->twigify();
        else
            $return = $this->toArray();

        return $return;
    }

	
    /**
     * format function.
     * 
     * @access public
     * @param mixed $_format
     * @return void
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
    /*
     * Security
     */

    public static function cleanInt($s) {

        $s= str_replace('"','', $s);
        $s= str_replace(':','', $s);
        $s= str_replace('.','', $s);
        $s= str_replace(',','', $s);
        $s= str_replace(';','', $s);
        return $s;

    }

    public static function cleanString($_str) {
        // Change characteres...
        $i = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í',
            'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß',
            'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï',
            'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă',
            'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē',
            'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ',
            'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ',
            'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń',
            'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ',
            'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť',
            'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ',
            'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ',
            'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ',
            'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', '!', '?', '\\', '.', '&', ',', ':', '(', ')', ';', '^', '¡', '¿', '//', '"', '@');
        // ...in this other...
        $o = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I',
            'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's',
            'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
            'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G',
            'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ',
            'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N',
            'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r',
            'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't',
            'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w',
            'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A',
            'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A',
            'a', 'AE', 'ae', 'O', 'o', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $str = str_replace($i, $o, $_str);
        // Replace more
        return strtolower(preg_replace(array('/[^a-zA-Z0-9 -_\/]/', '/[ -]+/', '/[ _]+/', '/[ \/]+/', '/^-|-$/'), array('', '-', '_', '/', ''), $str));
    }

    public function clearInjection($val, $post = false) {
        if ($post){
            $val = str_ireplace("SELECT","",$val);
            $val = str_ireplace("COPY","",$val);
            $val = str_ireplace("DELETE","",$val);
            $val = str_ireplace("DROP","",$val);
            $val = str_ireplace("DUMP","",$val);
            $val = str_ireplace(" OR ","",$val);
            $val = str_ireplace("LIKE","",$val);
        } else {
            $val = str_ireplace("SELECT","",$val);
            $val = str_ireplace("COPY","",$val);
            $val = str_ireplace("DELETE","",$val);
            $val = str_ireplace("DROP","",$val);
            $val = str_ireplace("DUMP","",$val);
            $val = str_ireplace(" OR ","",$val);
            $val = str_ireplace("%","",$val);
            $val = str_ireplace("LIKE","",$val);
            $val = str_ireplace("--","",$val);
            $val = str_ireplace("^","",$val);
            $val = str_ireplace("[","",$val);
            $val = str_ireplace("]","",$val);
            $val = str_ireplace("\\","",$val);
            $val = str_ireplace("!","",$val);
            $val = str_ireplace("¡","",$val);
            $val = str_ireplace("?","",$val);
            $val = str_ireplace("=","",$val);
            $val = str_ireplace("&","",$val);
        }

        return $val;
    }
}

?>
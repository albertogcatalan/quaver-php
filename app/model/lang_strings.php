<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

/**
 * language_strings class.
 * 
 * @extends base_object
 */
class lang_strings extends base_object {

    public $language,
    		$label,
    		$text,
    		$_languages;
    
    public $table = 'lang_strings';


    /**
     * getLanguageList function.
     * 
     * @access public
     * @return void
     */
    public function getLanguageList() {
        $db = new DB;

        $return = array();

        $items = $db->conn->getArray("SELECT id FROM " . $this->table . " WHERE language = 1 ");

        if (@$items){
            foreach ($items as $item) {
                $ob_lang = new lang_strings;
                $return[] = $ob_lang->getFromId($item['id']);
            }
		}
		
        return @$return;
    }
    
    /**
     * getFromLabel function.
     * 
     * @access public
     * @param mixed $_label
     * @return void
     */
    public function getFromLabel($_label) {
        global $core;

        $return = array();

        $items = $core->conn->getArray("SELECT id FROM " . $this->table . " WHERE label like '$_label' ORDER BY language");        

        if (@$items){
            foreach ($items as $item) {
                $ob_lang = new lang_strings;
                $return[] = $ob_lang->getFromId($item['id']);
            }
		}
		
        return @$return;
    }
    
   
    
    /**
     * saveAll function.
     * 
     * @access public
     * @return void
     */
    public function saveAll() {

        // Other languages
        if (!empty($this->_languages)) {
            foreach ($this->_languages as $item) {
                $lang = new lang_strings;
                $lang->setItem((array)$item);
				$lang->save();
            }
            
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
        if (!empty($this->id)) $item['id'] = $this->id;
        if (!empty($this->language)) $item['language'] = $this->language;
        if (!empty($this->label)) $item['label'] = $this->label;
        if (!empty($this->text)) $item['text'] = utf8_encode($this->text);
        
        return $item;
    }

    /**
     * setItem function.
     * 
     * @access public
     * @param mixed $_item
     * @return void
     */
    public function setItem($_item) {
    
        if (!empty($_item['id'])) $this->id = $_item['id'];
        if (!empty($_item['language'])) $this->language = $_item['language'];
        if (!empty($_item['label'])) $this->label = $_item['label'];
        if (!empty($_item['text'])) $this->text = utf8_encode($_item['text']);
        
          if (@$_item['_languages']) {
            $this->_languages = $_item['_languages'];
        }
     
     }

   
    

}
?>

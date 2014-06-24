<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

/**
 * language class.
 */
class lang {

    public $_fields = array(
        "id",
        "name",
        "slug",
        "active",
        "locale",
        "priority",
        "customerLanguage",
        "tld"
    );

    public $strings;
    public $table = 'lang';

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
            $this->getStrings();
        }

        return $this;
    }

    /**
     * getSiteLanguage function.
     * 
     * @access public
     * @return void
     */
    public function getSiteLanguage() {

        if (strstr($_SERVER['HTTP_HOST'], ".com") !== false) {
            // .com language selects language from browser
            $return = $this->getLanguageFromCookie();
            if (!$return) {
                $language_slug = $this->getBrowserLanguage();
                $this->getFromSlug($language_slug, true);

                if (empty($this->slug))
                    $this->getFromId(LANG);

                $return = $this;

            }
        } else {
            $return = $this->getLanguageFromDomain();
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getBrowserLanguage() {
        return substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }

    /**
     * getLanguageFromCookie function.
     * 
     * @access public
     * @return void
     */
    public function getLanguageFromCookie() {
        $return = false;
        if (!empty($_COOKIE[COOKIE_NAME . "_lang"])) {
            $language = $_COOKIE[COOKIE_NAME . "_lang"];
            $return = $this->getFromId($language);
        }

        return $return;
    }

    /**
     * getLanguageFromDomain function.
     * 
     * @access public
     * @return void
     */
    public function getLanguageFromDomain() {

        $tld = explode('.', DOMAIN_NAME);
        
        $this->getFromTld($tld[1]);
        $this->redirectToMainDomain();

        return $this;
    }

    /**
     * redirectTomainDomain function.
     * 
     * @access public
     * @return void
     */
    public function redirectToMainDomain() {

        header("Location: ".HTTP_MODE."www." . DOMAIN_NAME ."/?lang=" . $this->slug);
        exit;
    }

    /**
     * setCookie function.
     * 
     * @access public
     * @return void
     */
    public function setCookie() {

        if (!empty($this->id)) {
            setcookie(COOKIE_NAME . "_lang",
                      $this->id,
                      time()+60*60*24*7,
                      COOKIE_PATH,
                      COOKIE_DOMAIN);
        }

    }

    /**
     * getFromSlug function.
     * 
     * @access public
     * @param mixed $_slug
     * @param bool $_short (default: false)
     * @return void
     */
    public function getFromSlug($_slug, $_short = false) {

        $db = new DB;

        $return = LANG;

        $slug_where = 'slug';
        if ($_short)
            $slug_where = 'SUBSTR(slug, 1, 2)';

        $_slug = substr($_slug, 0, 3);
        $language = $db->conn->getArray("SELECT id FROM " . $this->table . " WHERE $slug_where = '$_slug' AND active = 'y'");
        if (@$language) {
            $this->getFromId($language[0]['id']);
            $return = $this;
        }
        return $return;
    }

    /**
     * getFromTld function.
     * 
     * @access public
     * @param mixed $_tld
     * @return void
     */
    public function getFromTld($_tld) {
        $db = new DB;

        $return = LANG;
        $language = $db->conn->getArray("SELECT id FROM " . $this->table . " WHERE tld = '$_tld'");
        if (@$language) {
            $this->getFromId($language[0]['id']);
            $return = $this;
        }

        return $return;
    }

    /**
     * getLanguages function.
     * 
     * @access public
     * @return void
     */
    public function getLanguages() {
        $db = new DB;

        $return = array();

        $items = $db->conn->getArray("SELECT * FROM " . $this->table . " ORDER BY id ASC");

        foreach ($items as $l) {
            $ob_lang = new lang;
            $return[] = $ob_lang->getFromId($l['id']);
        }

        return $return;
    }

    /**
     * getList function.
     * 
     * @access public
     * @param bool $_all (default: false)
     * @param bool $_byPriority (default: false)
     * @return void
     */
    public function getList($_all = false, $_byPriority = false) {
        $db = new DB;

        $return = array();

        $where = '';
        $order = '';

        if ($_byPriority)
            $order = 'ORDER BY priority ASC';

        if ($_all)
            $where = "WHERE active = 'y'";

        $items = $db->conn->getArray("SELECT id FROM " . $this->table . " $where $order");
        
        if (@$items)
            foreach ($items as $item) {
                $ob_lang = new lang;
                $return[] = $ob_lang->getFromId($item['id']);
            }

        return @$return;
    }

    /**
     * getString function.
     * 
     * @access public
     * @param mixed $_label
     * @return void
     */
    public function getString($_label) {
        $db = new DB;

        if (!isset($this->strings[$_label])) {
            $text = $db->conn->getResult("SELECT text
            FROM " . $this->table_strings . "
            WHERE language = '" . $this->id . "'
                AND label = '$_label'");
            if (empty($text)) {
                $this->strings[$_label] = $text;
            } else {
                $this->strings[$_label] = "#$_label#";

                $languages = $this->getLanguages();
                foreach ($languages as $l) {
                    $item['language'] = $l->id;
                    $item['label'] = $_label;
                    $item['text'] = $this->strings[$_label];
                    $db->conn->insert($item, $this->table_strings);
                }
            }
        }

        return $this->strings[$_label];
    }

    /**
     * _ function.
     * 
     * @access public
     * @param mixed $_label
     * @param string $_utf8 (default: '')
     * @return void
     */
    public function _($_label, $_utf8 = '') {

        //$return = $this->getString($_label);
        $return = @$this->strings[$_label];
        switch ($_utf8) {
            case('d'):
                $return = utf8_decode($return);
                break;
            case('e'):
                $return = utf8_encode($return);
                break;
        }

        if (empty($return)) $return = "#$_label#";

        return $return;
    }

    /**
     * l function.
     * 
     * @access public
     * @param mixed $_label
     * @return void
     */
    public function l($_label) {
        return $this->_($_label, '');
    }

    /**
     * getStrings function.
     * 
     * @access public
     * @return void
     */
    public function getStrings() {
        $db = new DB;

        $strings = $db->conn->getArray("SELECT *
            FROM " . $this->table_strings . "
            WHERE language = '" . $this->id . "'");

        foreach ($strings as $string) {
            if (!isset($this->strings[$string['label']]))
                $this->strings[$string['label']] = utf8_encode($string['text']);
        }
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

}

?>
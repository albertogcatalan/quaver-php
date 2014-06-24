<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

/**
 * user class.
 * 
 * @extends base_object
 */
class user extends base_object {


    public $_fields = array(
        "id",
        "active",
        "active",
        "level",
        "email",
        "password",
        "salt",
        "name",
        "surname",
        "avatar",
        "timezone",
        "biography",
        "registered",
        "last_login",
        "last_activity",
        "language" => 1
    );

    public $plainPassword; //helper
    public $cookie = '';
    public $logged = false;
    public $table = 'user';


    /**
     * save function.
     * 
     * @access public
     * @return void
     */
    public function save() {
        $db = new DB;

        $this->cipher();
        $item = $this->getItem();

        if (!empty($this->id)) {
            // UPDATE
           
            $return = $db->conn->update($item, $this->table, "id = '" . $this->id . "'");
        } else {
            // INSERT
            $return = $db->conn->insert($item, $this->table);
            $this->id = $db->conn->getLastId();
        }
        $this->decipher();
        

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
        
        $sql = "SELECT * FROM " . $this->table . " WHERE id = '$_id'";

        $item = $db->conn->getArray($sql);
        if (@$item) {
            $this->setItem($item[0]);
            $this->decipher();
        }

        return $this;
    }

    /**
     * cookie function.
     * 
     * @access public
     * @return void
     */
    public function cookie() {
        if (empty($this->cookie) && !empty($this->id)) {
            $this->cookie = sha1($this->password . md5($this->salt));
        }
        return $this->cookie;
    }

    
    /**
     * getFromEmail function.
     * 
     * @access public
     * @param mixed $_email
     * @return void
     */
    public function getFromEmail($_email) {
        $db = new DB;
        $this->email = $db->conn->mysql_real_escape_string($_email);
        
        $id = $db->conn->getResult("
            SELECT id
            FROM " . $this->table . "
                WHERE email = '" . $this->email . "'");

        if ($id > 0) {
            $return = $this->getFromId($id);
        } else {
            $return = false;
        }

        return $return;
    }

	/**
	 * getAccountDisabledFromEmail function.
	 * 
	 * @access public
	 * @param mixed $_email
	 * @return void
	 */
	public function getAccountDisabledFromEmail($_email) {
        $db = new DB;
        $this->email = $db->conn->mysql_real_escape_string($_email);
        
        $id = $db->conn->getResult("
            SELECT id
            FROM " . $this->table . "
                WHERE active = 'n' AND email = '" . $this->email . "'");

        if ($id > 0) {
            $return = $this->getFromId($id);
        } else {
            $return = false;
        }

        return $return;
    }
	
	/**
	 * getAccountDisabledFromId function.
	 * 
	 * @access public
	 * @param mixed $_id
	 * @return void
	 */
	public function getAccountDisabledFromId($_id) {
        $db = new DB;
        
        $id = $db->conn->getResult("
            SELECT id
            FROM " . $this->table . "
                WHERE active = 'n' AND id = '$_id '");

        if ($id > 0) {
            $return = $this->getFromId($id);
        } else {
            $return = false;
        }

        return $return;
    }


   /**
    * getFromEmailPassword function.
    * 
    * @access public
    * @param mixed $_email
    * @param mixed $_password
    * @return void
    */
   public function getFromEmailPassword($_email, $_password) {
        $db = new DB;

        $this->email = $db->conn->mysql_real_escape_string($_email);
        //$this->plainPassword = $_password;
        $this->password = sha1($_password);

        $id = $db->conn->getResult("
            SELECT id
            FROM " . $this->table . "
                WHERE email = '" . $this->email . "'
                    AND password  = MD5(CONCAT('" . $this->password . "', salt))");

        if ($id > 0) {
            $this->getFromId($id);
            $this->cookie();
            $this->logged = true;
            $this->updateLastActivity();
            $this->updateLastLogin();
            $return = $this->id;
        } else {
            $return = false;
        }

        return $return;
    }


   /**
    * getFromCookie function.
    * 
    * @access public
    * @param mixed $_cookie
    * @return void
    */
   public function getFromCookie($_cookie) {
        $db = new DB;

        $this->cookie = substr($db->conn->mysql_real_escape_string($_cookie), 0, 40);

        $id = $db->conn->getResult("
            SELECT id
            FROM " . $this->table . "
                WHERE SHA1(CONCAT(password, MD5(salt))) = '" . $this->cookie . "'");

        if ($id > 0) {
            $this->getFromId($id);
            if (!$this->isActive()){
                $this->unsetCookie();
            } else {
                $this->logged = true;
            }
           
            $return = $this->id;
            
            $this->updateLastActivity();
        } else {
            $this->unsetCookie();
            $return = false;
        }

		
		
        return $return;
    }

    /**
     * updateLastActivity function.
     * 
     * @access public
     * @return void
     */
    public function updateLastActivity() {
        if ($this->id > 0) {
            $this->last_activity = time();
            $this->save();
        }
    }

    /**
     * updateLastLogin function.
     * 
     * @access public
     * @return void
     */
    public function updateLastLogin() {
        if ($this->id > 0) {
            $this->last_login = time();
            $this->save();
        }
    }

    /**
     * hashPassword function.
     * 
     * @access public
     * @return void
     */
    public function hashPassword() {
        if (!empty($this->plainPassword)) {
            $this->password = md5(sha1($this->plainPassword) . $this->salt);
            $this->plainPassword = '';
        }
    }

    /**
     * salt function.
     * 
     * @access public
     * @return void
     */
    public function salt() {
        $this->makeSalt();

        return $this->salt;
    }
    
    /**
     * makeSalt function.
     * 
     * @access public
     * @param int $length (default: 8)
     * @return void
     */
    public function makeSalt($length = 8) {
        $random = "";
        srand((double)microtime() * 1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";
        //$char_list .= " !·$%&/()=?¿{}][^Ç,.;:_-";

        for ($i = 0; $i < $length; $i++)
        {
            $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
        }
        //return $random;
        $this->salt = $random;
    }

    /**
     * isActive function.
     * 
     * @access public
     * @param bool $_sql (default: false)
     * @return void
     */
    public function isActive($_sql = false) {
        if ($_sql)
            $return = " (active = 'y') ";
        else
            $return = ($this->active == "y");
        return $return;
    }
	
    /**
     * isAdmin function.
     * 
     * @access public
     * @return void
     */
    public function isAdmin() {
        return ($this->level == "admin");
    }

    /**
     * setCookie function.
     * 
     * @access public
     * @param string $_cookie (default: '')
     * @return void
     */
    public function setCookie($_cookie = '') {

        if (!empty($_cookie)) $this->cookie = $_cookie;
        if (!empty($this->cookie)) {
            setCookie(COOKIE_NAME . "_log", $this->cookie, time() + 60 * 60 * 24 * 30, COOKIE_PATH, COOKIE_DOMAIN);
            
        }
    }

    /**
     * unsetCookie function.
     * 
     * @access public
     * @return void
     */
    public function unsetCookie(){


        setCookie(COOKIE_NAME . "_log", "", time()-1, COOKIE_PATH, COOKIE_DOMAIN);
        setCookie("PHPSESSID", "", time()-1, COOKIE_NAME);

	    $this->logged = false;

    }

    /**
     * cipher function.
     * 
     * @access public
     * @return void
     */
    public function cipher() {
        $db = new DB;

        $this->name = $db->encrypt($this->name);
        $this->surname = $db->encrypt($this->surname);
    }

    /**
     * decipher function.
     * 
     * @access public
     * @return void
     */
    public function decipher() {
        $db = new DB;

        $this->_fields['name'] = $db->decrypt($this->name);
        $this->_fields['surname'] = $db->decrypt($this->surname);
    }

    /**
     * isEmailRegistered function.
     * 
     * @access public
     * @param mixed $_email
     * @return void
     */
    public function isEmailRegistered($_email) {
        $db = new DB;

        $_email = $db->conn->mysql_real_escape_string($_email);

        $item = $db->conn->getResult("SELECT id
            FROM " . $this->table . "
            WHERE email = '$_email'");
        if (@$item) {
            $return = true;
        } else {
            $return = false;
        }

        return $return;
    }

}
?>
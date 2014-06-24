<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

/**
 * Class DB
 */

class DB {

	public $conn = null;
    public $cipher_key = "";

    public function __construct() {

        // Set encryption key
        $this->cipher_key = CIPHER_KEY;


        // Connecting to mysql
        if (defined(DB_USERNAME)
            || defined(DB_PASSWORD)
            || defined(DB_DATABASE)
        ) {
            die('Database parameters needed.');
        } else {

            // Config mysql link
            $this->conn = new phmysql;
            $this->conn->setConnection(DB_HOSTNAME,
                DB_USERNAME,
                DB_PASSWORD,
                DB_DATABASE);
        }
        
        // Checking connection
        if (!$this->conn->doQuery("SHOW TABLES")) {
            die('Mysql connection error or no tables');
        }

	}

    /*
     * Cypher
     */

    public function encrypt($_str) {
        return $this->conn->mysql_real_escape_string(rc4::encrypt($_str, $this->cipher_key));
    }

    public function decrypt($_str) {
        return rc4::decrypt($_str, $this->cipher_key);
    }

}
?>
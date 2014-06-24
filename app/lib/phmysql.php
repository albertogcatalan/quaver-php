<?php
/**
 * phmysql
 *
 * @package phmysql
 * @author Felipe (aka. Pyron)
 * @version $Id$
 * @license GNU GPL v3
 */

class phmysql
{
    // Variable that stores the mysql connection settings
    private $mysql = Array('hostname' => '', 'username' => '', 'password' => '', 'database' => '');

    // Option to escape " ' characters with addslashes() function automatically
    public $addslashes = false;

    // Force the error messages?
    public $forceShow = false;

    // Last ID inserted
    private $last_id = 0;

    // Debug variables.
    public $debug = false;
    private $_DEBUG = Array("--- phmysql: Debug log Start --- \n");

    /** *=*=***********************************************************************
     * Connection functions
     */

    /**
     * phmysql::setConnection()
     *
     * @param mixed $_host Hostname to connect to.
     * @param mixed $_user Username of the database.
     * @param mixed $_pass Password of the database.
     * @param mixed $_ddbb Database to open.
     * Configure the data to connect to mysql.
     */
    public function setConnection($_host, $_user, $_pass, $_ddbb)
    {
        if (isset($_host) && isset($_user) && isset($_pass) && isset($_ddbb)) {
            $this->mysql['hostname'] = $_host;
            $this->mysql['username'] = $_user;
            $this->mysql['password'] = $_pass;
            $this->mysql['database'] = $_ddbb;
            $emsg = "Set connection parameters";
            //: Host: $_host, Username: $_user, Password: $_pass and Database: $_ddbb";
            $this->debug($emsg, "Message");
        } else {
            $emsg = "Mysql parameter configuration: ";
            if (!isset($_host)) $emsg .= "hostname";
            if (!isset($_user)) $emsg .= "username";
            if (!isset($_pass)) $emsg .= "password";
            if (!isset($_ddbb)) $emsg .= "database";
            $this->debug($emsg);
        }
    }

    /**
     * phmysql::openConnection()
     * Starts the connection with the current configuration
     */
    private function openConnection()
    {
        if (($mysql_link = mysql_connect($this->mysql['hostname'], $this->mysql['username'], $this->mysql['password'])) === FALSE) {
            $this->debug("Mysql connection: " . mysql_error(), "Error", true);
        } else {
            //$this->debug("Mysql connection started", "Message");
            if (mysql_select_db($this->mysql['database'], $mysql_link)) {
                return $mysql_link;
            } else {
                $emsg = "Mysql database: " . mysql_error();
                $this->debug($emsg, "Error", $this->forceShow);
            }
        }
    }

    /**
     * phmysql::doQuery()
     *
     * @param mixed $_query Raw SQL Query to execute
     * @return RAW SQL Statement or FALSE in case of error.
     */
    public function doQuery($_query)
    {
        $link = $this->openConnection();
        if (($query = mysql_query($_query, $link)) === FALSE) {
            $emsg = "Mysql query ($_query): " . mysql_error();
            $this->debug($emsg, "Error", $this->forceShow);
            $query = FALSE;
        } else {
            $emsg = "Mysql query: ($_query) performed.";
            $this->debug($emsg, "Message");
        }
        $this->last_id = $this->getInsertLastId($link);
        $this->closeConnection($link);
        return $query;
    }

    /**
     * phmysql::closeConnection()
     *
     * @param mixed $_l Link to connection
     */
    private function closeConnection($_l)
    {
        if (mysql_close($_l) === FALSE) {
            $this->debug("Mysql connection close: " . mysql_error(), "Error", $this->forceShow);
        } else {
            //$this->debug("Mysql connection closed", "Message");
        }
    }

    /** *=*=***********************************************************************
     * Data funcions
     */

    public function mysql_real_escape_string($string)
    {
        $link = $this->openConnection();
        if (($query = mysql_real_escape_string($string)) === FALSE) {
            $emsg = "Mysql real escape string ($string): " . mysql_error();
            $this->debug($emsg, "Error", $this->forceShow);
            $query = FALSE;
        } else {
            $emsg = "Mysql real escape string: ($string) performed.";
            $this->debug($emsg, "Message");
        }
        $this->closeConnection($link);
        return $query;
    }


    /**
     * phmysql::getNumRows()
     *
     * @param mixed $_sql SQL String
     * @return integer Number of rows the SQL query return
     */
    public function getNumRows($_sql)
    {
        if (($query = $this->doQuery($_sql)) === FALSE) {
            $return = FALSE;
        } else {
            if (($return = @mysql_num_rows($query)) === FALSE) {
                $emsg = "Mysql num rows: ($_sql) No data to count rows.";
                $this->debug($emsg, "Error", $this->forceShow);
            } else {
                $emsg = "Mysql num rows: (result: $return) performed.";
                $this->debug($emsg, "Message");
            }
        }
        return $return;
    }

    /**
     * phmysql::getArray()
     *
     * @param mixed $_sql SQL String
     * @param integer $_type Type of data return, associative array, numbers array or both.
     *      Posible values:
     *              (int)   (str)   (constant)      Result:
     *              1       ASSOC   MYSQL_ASSOC     Associative array
     *              2       NUM     MYSQL_NUM       Numeric array
     *              3       BOTH    MYSQL_BOTH      Associative and numeric array
     * @return Array with data, FALSE in case of error
     */
    public function getArray($_sql, $_type = 1)
    {
        $types = array("ASSOC" => MYSQL_ASSOC, "NUM" => MYSQL_NUM, "BOTH" => MYSQL_BOTH);
        if (is_string($_type)) {
            $keys = @array_keys($types);
            if (($search = @array_search($_type, $keys)) !== FALSE) {
                $_type = $types[$keys[$search]];
            } else {
                $_type = 1;
            }
        } elseif (is_numeric($_type)) {
            if ($_type < 1 || $_type > 3) {
                $_type = 1;
            }
        } else {
            $_type = 1;
        }

        if (($query = $this->doQuery($_sql)) === FALSE) {
            $return = FALSE;
        } else {
            if (($return[] = @mysql_fetch_array($query, $_type)) === FALSE) {
                $emsg = "Mysql fetch array: ($_sql) No rows to get data from.";
                $this->debug($emsg, "Error", $this->forceShow);
                $return = FALSE;
            } else {
                while ($return[] = @mysql_fetch_array($query, $_type)) {
                }
                ;
                array_pop($return);
                //$return = $return[0];
                $emsg = "Mysql fetch array performed.";
                //$this->debug($emsg, "Message");
            }
        }
        return $return;
    }

    /**
     * phmysql::getResult()
     *
     * @param mixed $_sql
     * @param integer $_row
     * @param integer $_column
     * @return
     */
    public function getResult($_sql, $_row = 0, $_column = 0)
    {
        if (!is_numeric($_row)) $_row = 0;
        if (!is_numeric($_column)) $_column = 0;
        if (($query = $this->doQuery($_sql)) === FALSE) {
            $return = FALSE;
        } else {
            if (($return = @mysql_result($query, $_row, $_column)) === FALSE) {
                $emsg = "Mysql result: ($_sql) Bad position specified.";
                $this->debug($emsg, "Error", $this->forceShow);
            } else {
                $emsg = "Mysql result: (result: $return) performed.";
                $this->debug($emsg, "Message");
            }
        }
        return $return;
    }

    private function getInsertLastId($link) {
        $last_id = mysql_insert_id($link);
        return $last_id;
    }

    function getLastId() {
        return $this->last_id;
    }

    /**
     * phmysql::insert()
     *
     * @param mixed $_data
     * @param mixed $table
     * @return
     */
    function insert($_data, $table)
    {
        if (count($_data) == 0 || empty($table)) {
            $emsg = "Mysql insert: Incomplete arguments to insert data";
            $this->debug($emsg, "Error");
            return false;
        }
        $keys = array_keys($_data);

        // Making the SQL
        $sql = 'INSERT INTO ' . $table . ' (';
        foreach ($keys as $key) {
            if ($this->addslashes) $key = addslashes($key);
            $sql .= $key . ', ';
        }
        $sql = substr($sql, 0, strlen($sql) - 2); // Removing the last ","
        $sql .= ') VALUES (';
        foreach ($_data as $value) {
            if ($this->addslashes) $value = addslashes($value);
            $sql .= "'$value', ";
        }
        $sql = substr($sql, 0, strlen($sql) - 2); // Removing the last ","
        $sql .= ')';

        return $this->doQuery($sql);
    }

    /**
     * phmysql::update()
     *
     * @param mixed $_data
     * @param mixed $table
     * @param mixed $_where
     * @return
     */
    function update($_data, $table, $_where)
    {
        if (count($_data) == 0 || empty($table)) {
            $emsg = "Mysql insert: Incomplete arguments to insert data";
            $this->debug($emsg, "Error");
            return false;
        }
        $keys = array_keys($_data);

        // Making the SQL
        $sql = 'UPDATE ' . $table . ' SET ';
        foreach ($keys as $key) {
            if ($this->addslashes) $key = addslashes($key);
            $sql .= $key . ' = \'' . $_data[$key] . '\', ';
        }
        $sql = substr($sql, 0, strlen($sql) - 2); // Removing the last ","
        $sql .= " WHERE " . $_where;

        return $this->doQuery($sql);
    }

    function delete($id, $table, $_where = '', $_keyField = 'id') {
        if (empty($id) || empty($_keyField)) {
            $emsg = "Mysql delete: Incomplete arguments to delete data";
            $this->debug($emsg, "Error");
            return false;
        }
        $sql = "DELETE FROM $table WHERE";
        if (!empty($_where)) {
            $sql .= "$_where AND";
        }
        $sql .= " $_keyField = '$id'";
        
        return $this->doQuery($sql);
    }

    /** *=*=***********************************************************************
     * Debug functions
     */

    /**
     * phmysql::showDebug()
     *
     * @param bool $_c (Optional) Activate colors (default: false)
     */
    public function showDebug($_c = false, $_showsql = false)
    {
        // $_c Colors
        $etype = array("Error",
                       "Message",
                       "Warning");
        $ecolor = array('<font color="FF0000"><b>Error</b></font>',
                        '<font color="FFD400"><b>Warning</b></font>',
                        '<font color="878787"><b>Message</b></font>');
        // ----------

        if ($this->debug) {
            print('<pre>');
            foreach ($this->_DEBUG as $msg) {
                if ($_c) $msg = str_replace($etype, $ecolor, $msg);
                if (!$_showsql) $msg = preg_replace("|\(.*\) |U", "", $msg);
                print($msg);
            }
            print("--- phmysql: Debug log End ---");
            print('</pre>');
        } else {
            print("<!-- debug is off -->");
        }
    }

    /**
     * phmysql::debug()
     *
     * @param mixed $_emsg Message to record
     * @param string $_t (Optional)Type of message (error, warning, message) (Default: error)
     * @param bool $_fshow (Optional) Force showing immediately, not just in log. (Default: false)
     */
    private function debug($_emsg, $_t = "Error", $_fshow = false)
    {
        if ($_fshow) print("[$_t] $_emsg \n");
        $this->_DEBUG[] = "[$_t] $_emsg \n";
    }

    /**
     * phmysql::foo()
     */
    public function foo()
    {
        $sql = $this->doQuery("SELECT * FROM phmysql");
        while ($row = mysql_fetch_array($sql)) {
            print(preg_replace("|\(.*\)|U", "", $row['s']));
            print("<br />");
        }
    }

}

?>
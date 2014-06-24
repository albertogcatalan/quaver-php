<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */


/**
 * core class
 */
class core {

    public $conn; //DB object

	// URL management
    public $url_var;
    public $queryString;

	// Language system
    public $language;
    
    // Template system
    public $twig = null;
    public $twigVars = array();

    // Development
    public $debug = false;
    public $log = array();


    /**
     * _constructor
     */
    public function __construct() {

        // Create new DB object
        $ob_DB = new DB;
        $this->conn = $ob_DB->conn;


        // Twig Template System Loader
        require_once(LIB_PATH . '/Twig/Autoloader.php');
        Twig_Autoloader::register();

        // Getting all directories in /template
        $path = P_PATH . 'template';
        $templatesDir = array($path);
        $dirsToScan = array($path);

        $dirKey = 0;
        while (count($dirsToScan) > $dirKey) {
            $results = scandir($dirsToScan[$dirKey]);
            foreach ($results as $result) {
                if ($result === '.' or $result === '..'
                    or $result == 'cache') continue;

                if (is_dir($dirsToScan[$dirKey] . '/' . $result)) {
                    $templatesDir[] = $dirsToScan[$dirKey] . '/' . $result;
                    $dirsToScan[] = $dirsToScan[$dirKey] . '/' . $result;
                }
            }
            $dirKey++;
        }

		//get query string from URL to core var
        $this->getQueryString();

        $loader = new Twig_Loader_Filesystem($templatesDir);
        $twig_options = array();
        if (defined(TEMPLATE_CACHE) && TEMPLATE_CACHE) $twig_options['cache'] = "./template/cache";
        if (defined(CACHE_AUTO_RELOAD) && CACHE_AUTO_RELOAD) $twig_options['auto_reload'] = true;
        
        $this->twig = new Twig_Environment($loader, $twig_options);

        // Clear cache
        if (isset($this->queryString['clearCache'])) {
            $this->twig->clearCacheFiles();
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            header("Location: $url");
            exit;
        }

        // Restoring user session
        if (!empty($this->queryString['PHPSESSID'])) {
            $sessionHash = $this->cleanString($this->queryString['PHPSESSID']);
            $ob_u = new User;
            $ob_u->setCookie($sessionHash);
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            header("Location: $url");
            exit;
        }


    }

    /**
     * start function.
     * 
     * @access public
     * @param bool $_mvc (default: true)
     * @return void
     */
    public function start($_mvc = true) {
    	
        global $_user, $_lang;
    	
        // Check user login
        $_user = new user;
        if (!empty($_COOKIE[COOKIE_NAME . "_log"])) {
            $_user->getFromCookie($_COOKIE[COOKIE_NAME . "_log"]);
        }
		
        // Load language
        $_lang = new lang;
        if (!empty($_GET['lang'])) {
            $lang_slug = substr($_GET['lang'], 0, 3);
            $_lang->getFromSlug($lang_slug);
            $_lang->setCookie();
        } else {
            $_lang->getSiteLanguage();
        }
        
        // Assoc URL to MVC
        if ($_mvc) $this->loadMVC();
    }

    /*
     * Global functions
     */
     
    public function fixTrailingSlash($_url) {

        if ($_url{strlen($_url) - 1} != '/' && strstr($_url, "image/") === false) {
            header("Location: " . $_url . "/");
            exit;
        }
    }

    public function getUrlFromId($_id) {
        $_id = (int)$_id;
        $url = $this->conn->getResult("SELECT url FROM url WHERE id = '$_id'");
        $regex = "/(\(.*\))/";
        return preg_replace($regex, "", $url);
    }

    public function loadMVC()
    {
        $url = $this->getUrl();
        $this->fixTrailingSlash($url);
        $mvc = $this->getVT($url);
        if ($mvc != false) {

            $this->loadController($mvc['controller']);
            
        }
    }

    /**
     * loadController function.
     *
     * @access public
     * @param mixed $_controllerName
     * @return void
     */
    public function loadController($_controllerName) {
        global $_user, $_lang;

        $controllerPath = GLOBAL_PATH . "/controller/" . $_controllerName . ".php";

        //echo $controllerPath;

        $this->getGlobalTwigVars();

        // Load controller
        if (file_exists($controllerPath)) {
            require_once($controllerPath);
        } else {
            if (!empty($_controllerName))
                $this->log("Error loading controller: $_controllerName", "error");
        }
    }



    public function getQueryString() {
        $uri = $_SERVER['REQUEST_URI'];
        $qs = parse_url($uri, PHP_URL_QUERY);
        if (!empty($qs)) {
            parse_str($qs, $this->queryString);
        }            
    }

    // Get controller template
    public function getVT($_url) {
        $mvc_items = $this->conn->getArray("SELECT * FROM url WHERE enabled = 'y'");

        foreach ($mvc_items as $item) {
            $regexp = "/^" . str_replace(array("/", "\\\\"), array("\/", "\\"), $item['url']) . "$/";
            preg_match($regexp, $_url, $match);

            if (@$match) {
                $this->url_var = $match;
                $mvc = $item;
                break;
            }
        }

        if (@$mvc) {
            $return = $mvc;
        } else {
            $this->loadController('404');
            exit;
            die('error 404');
        }
        return $return;
    }

    public function getUrl() {
        $url = $_SERVER['REQUEST_URI'];
        if (strstr($url, "?") !== false)
            $url = substr($url, 0, strpos($url, "?")); // Remove GET vars
        return $url;
    }

    /*
    * Templates
    */

   
    public function getGlobalTwigVars() {
        global $_lang, $_user;

        // Language
        $this->addTwigVars("language", $_lang);

        // Environment
        if (defined(DEV_MODE) && DEV_MODE === true){
            $this->addTwigVars("_env", true);
        }

        // Languages
        $languageVars = array();
        $ob_l = new lang;
        foreach ($ob_l->getList() as $lang) {
            $tld_k = @array_keys(unserialize(LANG_TLD));
            $tld = $tld_k[$lang->id];
            $item = array(
                "id" => $lang->id,
                "domain" => HTTP_MODE . "www." . DOMAIN_NAME . $lang->tld,
                "name" => utf8_encode($lang->name),
                "slug" => $lang->slug,
                "locale" => $lang->locale,
                "url" => "language/" . $lang->slug . "/",
                "class" => ($_lang->id == $lang->id) ? 'selected' : ''
            );
            array_push($languageVars, $item);
        }
        $this->addTwigVars('languages', $languageVars);

        $this->addTwigVars('actual_url', strip_tags($this->url_var[0]));

        // User
        $userVars = array(
            "name" => $_user->name,
            "avatar" => $_user->avatar,
            "admin" => $_user->isAdmin(),
            "logged" => $_user->logged,
            "sessionHash" => $_user->cookie
        );
        
        $this->addTwigVars("user", $userVars);
        $this->addTwigVars("_user", $_user);

        // Login
        if (@isset($this->queryString['login-error']))
            $this->addTwigVars('loginError', true);

        if (@isset($this->queryString['user-disabled']))
            $this->addTwigVars('userDisabled', true);

        // Discharge
        if (@isset($this->queryString['account-disabled']))
            $this->addTwigVars('accountDisabled', true);

        if (@isset($this->queryString['account-deleted']))
            $this->addTwigVars('accountDeleted', true);

        // Config
        $config = array(
            "baseHref" => HTTP_MODE . DOMAIN_NAME,
            "thisHref" => HTTP_MODE . DOMAIN_NAME . $this->getUrl(),
            "randomVar" => RANDOM_VAR
        );

        $this->addTwigVars('config', $config);

    }

    public function addTwigVars($_key, $_array) {
        $this->twigVars[$_key] = $_array;
    }


   
   /*
    * Test & Debug
    */

    private function insertLogItem($_item) {
        $this->log[] = $_item;
    }

    public function log($_msg, $_type = "message", $_warn = 0) {

        if ($this->debug) {
            $item = array();
            $item['type'] = $_type;
            $item['message'] = $_msg;
            $item['warning'] = $_warn;
            $this->insertLogItem($item);
        }
    }

    public function showLog() {
        $this->debug = true;
        $this->conn->debug = true;
        $this->log("Debug enabled!");
        if ($this->debug) {
            echo '<pre>';
            print_r($this->log);
        }
    }


}
?>
<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

//ini_set('display_errors', 0);
//error_reporting(0);

if (empty($this->url_var[4])) exit;

if (strstr($this->url_var[4], "files/") or strstr($this->url_var[4], "http://")) {
    $src = $this->url_var[4];
} else {
    $src = "static/images/" . $this->url_var[4];
}

if (!file_exists($src)) exit;

if ($this->url_var[1] == 0 && $this->url_var[2] == 0 && $this->url_var[3] == 0) {
    $mimeType = mime_content_type($src);
    header("Content-type: " . $mimeType);
    echo file_get_contents($src);
} else {
    if ($this->url_var[1] > 0) $_GET['w'] = (string)$this->url_var[1];
    if ($this->url_var[2] > 0) $_GET['h'] = (string)$this->url_var[2];
    $_GET['zc'] = (string)$this->url_var[3];
    $_GET['src'] = $src;

    require_once(LIB_PATH . "/timthumb.php");
}

exit;

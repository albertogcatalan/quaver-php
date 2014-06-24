<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

/**
 * url class.
 * 
 * @extends base_object
 */
class url extends base_object
{

    // Get/Set & format() extends base_object
    public $_fields = array(
        "id",
        "url",
        "controller",
        "enabled"
    );
    
    // Var to table name
    public $table = 'url';

}
?>
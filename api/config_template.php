<?php
    //
    // Setting DEV to true, enables logging of errors and
    // data across the requests, outputed to the $JSON file
    //
    $__dev__ = true;							// change for production

    // 
    // Database Information
    //
    $__connectionHost__     = "localhost";

    $__connectionDbName__   = "dbname";			// change
    $__connectionDbUser__   = "username";		// change
    $__connectionDbPass__   = "password";		// change

    //
    // Server specific data
    //
    $__serverEvironment__   = "local";			// change
    $__cdnFolder__          = "local/test/";	// change

    //
    // mysql path of the server
    //
    $__mysqlCommandPath__   = "/Applications/MAMP/Library/bin/mysql";	// change

    //
    // base url to construct api requests
    //
    $__requestURI__         = $_SERVER['REQUEST_URI'];
    $__requestMethod__      = $_SERVER['REQUEST_METHOD'];

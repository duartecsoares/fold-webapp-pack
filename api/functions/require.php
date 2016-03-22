<?php
	
	$__required_files__ = Array();

	function req($file, $from = null) {
	    global $__required_files__, $log, $pdo, $status;
	    $success = false;
	    if(!in_array($file, $__required_files__)){
	    	if ( file_exists ($file) ) {
		        include($file);
		        $__required_files__[] = $file;
		        $log->addFile( $file, true, $from );
		        $success = true;
		    } else {
		    	$log->addFile( $file, false, $from );
		    	$status = 500;
		    	$success = false;
		    }
	    }
	    return $success;
	}

	function requirePHP( $file, $from = null) {
		$file = $from.'/'.$file;
		$file .= '.php';
		return req($file, $from);
	}

	function requireConfig( $name, $from = null ) {
		$path = dirname(__FILE__) . "/../configs/".$name.".php";
		return req($path, $from);
	}

	function requireModel( $name, $from = null ) {
		$path = dirname(__FILE__) . "/../models/".$name.".php";
		return req($path, $from);
	}

	function requireCollection( $name, $from = null ) {
		$path = dirname(__FILE__) . "/../collections/".$name.".php";
		return req($path, $from);
	}

	function requireFunction( $name, $from = null ) {
		$path = dirname(__FILE__) . "/".$name.".php";
		return req($path, $from);
	}

	function requireClass( $name, $from = null ) {
		$path = dirname(__FILE__) . "/../classes/".$name.".php";
		return req($path, $from);
	}

	function requireVendor( $name, $from = null ) {
		$path = dirname(__FILE__) . "/../vendor/".$name.".php";
		return req($path, $from);
	}

	function requireEmailTemplate( $name, $from = null ) {
		$path = dirname(__FILE__) . "/../models/Email/Templates/".$name.".php";
		return req($path, $from);
	}
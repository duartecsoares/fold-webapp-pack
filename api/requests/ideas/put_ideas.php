<?php
    $log->addFile( __FILE__ );

    include_once dirname(__FILE__) . "/../../models/idea.php";

    $json['idea'] = null;

    //
    // route requests to actions and others
    //
    if ( !empty($_PUT) ) {

    	$file = dirname(__FILE__).'/_create_idea.php';

        if ( file_exists($file) ) {
            include_once $file;
        } else {
            $status = 400;
            $log->error('warning', array('id'=>1000, 'description'=>'Api request does not exist.') );      
        }

    } else {

        $status = 400;
		$log->error('error', array('id'=>1002,'description'=>'Bad Request - No Parameters.','details'=>'Empty parameters.'));
    
    }

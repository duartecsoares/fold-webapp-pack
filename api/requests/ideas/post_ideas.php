<?php
    $log->addFile( __FILE__ );

    // GET users
    $dirFile = dirname(__FILE__);

    include_once $dirFile . "/../../models/idea.php";

    $id     = $__request__[3];
    $action = $__request__[4];

    $fieldname  = "ideas";
    $count      = 0;
    $ideas      = null;

    $json['idea'] = null;

    $log->append('params', array('id'=>$id) , "REST");

    //
    // route requests to actions and others
    //
    if ( !empty($id) ) {


        if ( !empty($action) ) {
            $action = strtolower($action);
            $file = "_post_".$action."_idea.php";

            if ( file_exists(dirname(__FILE__).'/'.$file) ) {
                include_once $file;
            } else {
                $status = 404;
                $log->error('warning', array('id'=>1000, 'description'=>'Api request does not exist.') );      
            }

        } else {

             $file = "_update_idea.php";

            if ( file_exists(dirname(__FILE__).'/'.$file) ) {
                include_once $file;
            } else {
                $status = 404;
                $log->error('warning', array('id'=>1000, 'description'=>'Api request does not exist.') );      
            }

        }


    } else {

        $status = 404;
        $log->error('warning', array('id'=>1000, 'description'=>'Api request does not exist.') );
    
    }


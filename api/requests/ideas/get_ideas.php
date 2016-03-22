<?php
    $log->addFile( __FILE__ );

    // GET users
    $dirFile = dirname(__FILE__);

    include_once $dirFile . "/../../models/idea.php";

    if ( isset($__request__[3]) && !empty($__request__[3]) ) {
        $id = $__request__[3];
    } else {
        $id = null;
    }

    if ( isset($__request__[4]) && !empty($__request__[4]) ) {
        $action = $__request__[4];
    } else {
        $action = null;
    }

    $fieldname  = "ideas";
    $count      = 0;
    $ideas      = null;
    
    $log->append('params', array('id'=>$id) , "REST");

    //
    // route requests to actions and others
    //
    if ( !$id ) {

        include_once "_list_ideas.php";

    } else {

        if ( !empty($action) ) {

            $action = strtolower($action);
            include_once "_".$action."_idea.php";

        } else {

            include_once "_get_idea_profile.php";

        }

    }



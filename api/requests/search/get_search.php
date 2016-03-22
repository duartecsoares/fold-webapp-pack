<?php

    requireCollection('UserCollection');
    requireCollection('IdeaCollection');

    //
    // Per page
    //
    $page 		= 0;
    $perpage 	= 5;

    if ( isset($_GET['page']) ) {
    	$page = $_GET['page'];
    }

    if ( isset($_GET['all']) ) {
        $perpage = 30;
    }


    if ( strlen( $_GET['s'] ) > 0 ) {

        //
        // Ideas
        //
        $ideaList       = new IdeaList();
        $ideaList->setPage( $page );
        $ideaList->setPerPage( $perpage );
        $listArray      = $ideaList->search( $_GET['s'] );
        $json['ideas']  = $listArray;

        //
        // Users
        //
        $userList 		= new UserList();
        $userList->setPage( $page );
        $userList->setPerPage( $perpage );
        $listArray 		= $userList->search( $_GET['s'] );
    	$json['users'] 	= $listArray;

    } else {
        $json['users'] = null;
        $json['ideas'] = null;
    }
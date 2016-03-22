<?php
    $log->addFile( __FILE__ );

    requireCollection('IdeaCollection');
    
    $ideaList = new IdeaList();
    $listArray = null;

    //
    //
    // Pagination
    //
    //
    if ( isset($_GET['page']) ) {
        $ideaList->setPage( $_GET['page'] );
    }

    if ( isset($_GET['perpage']) ) {
        $ideaList->setPerPage( $_GET['perpage'] );
    }


    if ( isset($_GET['looking_for']) ) {
        $ideaList->setPreFilter('traits', $_GET['looking_for']);
    }

    if ( isset($_GET['type']) ) {
        $ideaList->setPreFilter('type', $_GET['type']);
    }

    if ( isset($_GET['image']) ) {
        $ideaList->setPreFilter('image', $_GET['image']);
    }

    $ideaList->setPreFilter('privacy', '0');

    //
    //
    // list
    //
    //
    if ( !empty($_GET['list']) ) {

        $list = $_GET['list'];

        if ( $list == 'popularity' ) {
            $listArray = $ideaList->getPopular();
        } else if ( $list == 'new' ) {
            $listArray = $ideaList->getNew();
        } else if ( $list == 'favorite' ) {
            $listArray = $ideaList->getFavd( $_SESSION['user']->id );
        } else if ( $list == 'today') {
            $ideaList->setPreFilter('privacy', true);
            $listArray = $ideaList->getToday();
        } else {
            $listArray = $ideaList->getPopular();
        }

        
    } else {
        $listArray = $ideaList->getPopular();
    }

    //
    //
    // Return result
    //
    //
    $json['ideas'] = $listArray;
    $json['count'] = $ideaList->count;

    if ( $ideaList->count == 0 ) {
        $status = 404;
    }

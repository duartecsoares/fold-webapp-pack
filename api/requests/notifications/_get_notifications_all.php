<?php
    $log->addFile( __FILE__ );

    global $json;

    requireCollection('NotificationsCollection');
    
    $notificationsList = new NotificationsCollection();

    $notificationsList->setTo( $_SESSION['user']->id );

    if ( isset($_GET['page']) ) {
        $notificationsList->setPage( $_GET['page'] );
    }

    if ( isset($_GET['perpage']) ) {
        $notificationsList->setPerPage( $_GET['perpage'] );
    } else {
        $notificationsList->setPerPage( 40 );
    }

    $notificationsList->gatherRelatedData();

    // $notificationsList->pull();
    $listArray = $notificationsList->get();

    //
    //
    // Return result
    //
    //
    $json['notifications'] = $listArray;

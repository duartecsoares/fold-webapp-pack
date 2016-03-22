<?php
    $log->addFile( __FILE__ );


    if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() == true ) {

        requireCollection('NotificationsCollection');
        
        $notificationsList = new NotificationsCollection();

        $notificationsList->setTo( $_SESSION['user']->id );
        $result = $notificationsList->markAsSeen( $_POST['count'] );

        //
        //
        // Return result
        //
        //
        $json['result'] = $result;

    } else {
        $status = 403;
    }
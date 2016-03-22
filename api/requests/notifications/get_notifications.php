<?php
	$log->addFile( __FILE__ );


    if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() == true ) {

        requireCollection('NotificationsCollection');

        //
        // Start Execution
        //
        $action  = ( isset($__request__[3]) ? strtolower($__request__[3]) : null );
        
        $notificationsList = new NotificationsCollection();
        $listArray = null;

        if ( !empty($action) ) {
            //
            // load action
            //
            requirePHP('_get_notifications_'.$action, dirname(__FILE__));

        } else {
            
            $notificationsList->setTo( $_SESSION['user']->id );
            $notificationsList->gatherRelatedData();
            $notificationsList->setPage( 1 );
            $notificationsList->setPerPage( 15 );

            // $notificationsList->pull();
            $listArray = $notificationsList->get();

            //
            // Return result
            //
            $json['notifications'] = array_slice($listArray, 0, 5);

            //
            // Has New?
            //
            $notificationsList = new NotificationsCollection();
            $notificationsList->setTo( $_SESSION['user']->id );
            $notificationsList->setPerPage( 20 );
            $notificationsList->setUnseen( true );
            $result = $notificationsList->getExists();

            //
            //
            // Return result
            //
            //
            $json['has_unseen_notifications'] = $result;

        }

    } else {
        $status = 403;
    }

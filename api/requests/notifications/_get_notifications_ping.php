<?php
    $log->addFile( __FILE__ );

    requireCollection('NotificationsCollection');

    global $json, $log;
    
    $notificationsList = new NotificationsCollection();

    $notificationsList->setTo( $_SESSION['user']->id );
    $notificationsList->setPerPage( 20 );
    $notificationsList->setUnseen( true );
    
    $result = $notificationsList->getExists();

    if ( $result && $notificationsList->count > 0 ) {
        $lastNotification = $notificationsList->models[0];
        $json['notifications_last_id'] = $lastNotification['id'];
    }
    //
    //
    // Return result
    //
    //
    $json['notifications'] = $result;
    $json['notifications_count'] = $notificationsList->count;

    $spot = array(
        "type"  => "blog",
        "id"    => 1,
        "title" => "new_blog_entry",
         "description" => "blog_entry_description", 
         "published_at" => "2015-12-16", 
         "link" =>  "link-to-blog", 
         "buttonLabel" => "read", 
         "cover" => "url-to-cover-img"   
    );

    $json['spotlight'] = $spot['id'];
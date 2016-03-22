<?php
    $log->addFile( __FILE__ );

    $json['favorites'] = null;
    $json['favorited'] = true;

    $log->append('delete_idea', 'start:');

    if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() && !empty($id) ) {
				 
		requireModel('Ideas/DynamicIdea');

	    $idea       = new DynamicIdea();
	    $idea->id   = $id;
	    $result     = $idea->pull('is_favorite', array("user_id"=>$_SESSION['user']->id));

	    $log->append('delete_idea', $idea);

	    //
	    // If idea exists, that means that it was already favorited
	    //
	    if ( $idea->exists( false ) ) {

	    	$log->append('delete_idea', 'EXISTS!');

	    	
	    	$favorited = $idea->unFavorite();

	    	$idea->updateFavoriteCount( false );

	    	$idea->update('like_count');

	    	$view_count = $idea->view_count-1;

	    	$json['favorites'] = $view_count;
	    	$json['favorited'] = $favorited;

			requireModel('Notifications/DynamicNotification');

			$notification = new DynamicNotification();
			$notification->fromTo( $_SESSION['user']->id , $idea->idea_user_id );
			$notification->type('favorite-idea');
			$notification->delete();

	    	$status = 200;

	    } else {
	    	$status = 200;
	    	$json['favorited'] = false;
		}

    } else {
    	$json['favorited'] = false;
		$status = 400;
        $log->error('error', array('id'=>1015, 'description'=>'Forbidden.', 'details'=>'Session needed for this action.') );
    }

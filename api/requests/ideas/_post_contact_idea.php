<?php
    $log->addFile( __FILE__ );


    //
    // check if inde exists
    //
    $success = false;

    if ( isset($_SESSION['user']) ) {
			
		if (  !empty($id) && !empty($_POST['message']) ) {

    		$message = $_POST['message'];

			//
		    // Queries
		    //
		    $idea = new EditableIdea();
			$idea->id = $id;
			$result_idea = $idea->pull();

		    if ( $result_idea == false ) {
		    
		    	$log->error('error', array('id'=>1016,'description'=>'Bad Request - Unknown','details'=>'Bad mysql requests, see logs.'));
		    
		    } else {

		    	$success = $idea->contact( $message );

		    }
	    } else {
	    	$status = 400;
			$log->error('error', array('id'=>1002,'description'=>'Bad Request - Missing Parameters.','details'=>'[id] or [message] not received.'));
	    }

    } else {

		$status = 400;
        $log->error('error', array('id'=>1015, 'description'=>'Forbidden.', 'details'=>'Session needed for this action.') );
    
    }

   $json['message_sent'] = $success;
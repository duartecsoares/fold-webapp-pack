<?php

    $count      = 0;
    $idea      	= null;

	if ( isset($_SESSION["user"]) && !empty($_SESSION["user"]->id) ) {

		requireModel('Ideas/DynamicIdea');

	    $idea       = new DynamicIdea();
	    $idea->id   = $id;
	    $idea->pull();

	    if ( $idea->user_id == $_SESSION["user"]->id ) {

	    	$data = $_POST;
	    	$data['updated_at'] = date("Y-m-d H:i:s");

		    $idea->set( $data, array("id", "created_at", "like_count", "view_count", "flag_count", "featured") );
		    $result 	= $idea->update();

		    //
		    // Some ideas do not count for the total
		    //
		    if ( isset($_POST['privacy']) ) {
			    //
		    	// After Process
		    	//
		    	class Process extends ServerTask {
		    		public $user;

		    		function __construct($user) {
			            $this->user = $user;
			        }

		    		public function run() {
		    			return $this->user->calcIdeaCount();
		    		}
		    	}

		    	$process = new Process( $_SESSION["user"] );
		    }

	    } else {
			$status = 400;
			$log->error('warning', array('id'=>1004,'description'=>'Permissions needed.','details'=>'The user ['.$_SESSION["user"]->id.'] is not the owner of this idea ['.$id.'].'));
		    $result 	= false;
	    }

	} else {

		$status = 400;
		$log->error('warning', array('id'=>1004,'description'=>'Permissions needed.','details'=>'A user must be logged in to perform this task.'));

	}


<?php

    $count      = 0;
    $idea      	= null;

	if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() ) {

		requireModel('Ideas/DynamicIdea');

	    $idea = new DynamicIdea();
	    $idea->user_id = $_SESSION["user"]->id;
	    $idea->set( $_PUT, array("id", "created_at", "like_count", "view_count", "flag_count", "featured") );
		$result = $idea->push();

		if ( $result == true ) {
			$json["idea"] = $idea->getProfile();
			$status = 200;
			
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

		} else {
			$status = 400;
			$json["idea"] = null;
		}

	} else {

		$json["idea"] = null;
		$status = 400;
		$log->error('warning', array('id'=>1004,'description'=>'Permissions needed.','details'=>'A user must be logged in to perform this task.'));

	}


	// if ( isset($_SESSION["user"]) && !empty($_SESSION["user"]->id) ) {

	// 	$idea = new EditableIdea( $_SESSION["user"]->id );
	// 	$idea->set( $_PUT );
	// 	$idea->create();

 //        $traits = array();

 //        $query_traits = "SELECT id, name, parent FROM traits";
 //        $result_traits = $pdo->query($query_traits);
 //        $log->addQuery( $query );

 //        while ($trait = $result_traits->fetch(PDO::FETCH_OBJ)) {
 //            $traits[] = $trait;
 //        }

 //        $__traits__ = $traits;

	// 	$json['idea'] = $idea->ideaProfile();

	// } else {

	// 	$status = 400;
	// 	$log->error('warning', array('id'=>1004,'description'=>'Permissions needed.','details'=>'A user must be logged in to perform this task.'));

	// }

	// $json['idea'] = $idea;
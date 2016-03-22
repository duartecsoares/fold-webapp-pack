<?php
	
	$json["gallery"] = null;

	if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() ) {


	    if ( isset($__request__[3]) && is_numeric($__request__[3]) ) {
	        $idea_id = $__request__[3];
	    } else {
	        $idea_id = null;
	    }

	    if ( isset($__request__[4]) && is_numeric($__request__[4]) ) {
	        $image_id = $__request__[4];
	    } else {
	        $image_id = null;
	    }


	    if ( is_numeric($idea_id) && is_numeric($image_id) ) {

			requireModel('Ideas/DynamicIdea');

		    $idea       = new DynamicIdea();
		    $idea->id   = $idea_id;
		    $idea->pull();

		    if ( $idea->exists() && $idea->user_id == $_SESSION["user"]->id ) {
			    $status = 200;
			    $result = $idea->deleteImageFromGallery( $image_id );

			    if ( $result ) {
			    	$json["gallery"] = $idea->getGallery();
			    } else {
					$status = 400;
					$log->error('warning', array('id'=>1006,'description'=>'Could not delete.','details'=>'Could not delete the image, maybe the index doesnt exist?'));
			    }

			} else {
				$status = 404;
				$log->error('warning', array('id'=>1005,'description'=>'Idea not found or User not the owner.','details'=>'Idea with id ['.$idea_id.'] wasnt found or doesnt belong to ['.$_SESSION["user"]->id.'].'));
			}


		} else {
			$status = 400;
			$log->error('warning', array('id'=>1003,'description'=>'Bad request.','details'=>'No idea id ['.$idea_id.'] or image index ['.$image_id.'] received.'));
		}


	} else {

		$status = 400;
		$log->error('warning', array('id'=>1004,'description'=>'Permissions needed.','details'=>'A user must be logged in to perform this task.'));

	}
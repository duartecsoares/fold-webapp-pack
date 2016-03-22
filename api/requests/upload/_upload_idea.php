<?php

	global $status, $json, $log;

	requireModel('Images/StaticImage');

	$id = $_POST['id'];

	if ( !empty($id) ) {

		requireModel('Ideas/DynamicIdea');

		$idea = new DynamicIdea();
		$idea->id = $id;
		$idea->user_id = $_SESSION['user']->id;

		if ( $idea->exists(true, 'from_user') ) {

			$idea->pull();

			$image = new StaticImage();
			$success = $image->moveUploadedImage( $_FILES['gallery'] );


			if ( $success ) {

				$log->append('upload_files', $image);
				
				$success = $idea->sendImageToCDN('gallery', $image);

				$log->append('upload_files', 'SENT TO CDN success');
				$log->append('upload_files', $success);

				if ( $success ) {
					//
					// image uploaded successfuly and added to gallery
					//
					$json['uploaded'] = $image->cdn_url ? $image->cdn_url : $image->local_url;
					$json['gallery'] = $idea->getGallery();
					$status = 200;

				} else {
					//
					// could not upload image
					//
					$status = 400;
				}

			} else {
				//
				// couldnt move image to upload
				//
				$status = 400;
			}

		} else {
			// could not find idea
  			$status = 403;
            $log->error('warning', array('id'=>1003,'description'=>'Cant Upload, User not owner or Idea doesnt exist.','details'=>''));

		}

	} else {
		// could not find id for idea
		$status = 400;
	}

	
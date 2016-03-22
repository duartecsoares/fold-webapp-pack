<?php
	requireCollection('UpdateUserCollection');
    requireModel('Users/DynamicUser');

	requireCollection('UpdateIdeaCollection');
    requireModel('Ideas/DynamicIdea');

    // $__muteAll__ = true;
    
    ini_set('max_execution_time', 3000); //300 seconds = 5 minutes

    if ( isset($_GET['users']) ) {
	    $list = new UpdateUserList();
		$list->setPage( isset($_GET['page']) ? $_GET['page'] : 1);
	    $list->per_page = isset($_GET['perpage']) ? $_GET['perpage'] : 16000;
	    $list->pull('update_popularity');
	}

    if ( isset($_GET['ideas']) ) {
	    $list = new UpdateIdeaList();
		$list->setPage(isset($_GET['page']) ? $_GET['page'] : 1);
	    $list->per_page = isset($_GET['perpage']) ? $_GET['perpage'] : 1500;
	    $list->max_per_page = 1500;
	    $list->pull('update_popularity');
	}

    if ( isset($_GET['updateavatars']) ) {
	    $list = new UpdateUserList();
	    $list->per_page = isset($_GET['perpage']) ? $_GET['perpage'] : 16000;
		$list->setPage(isset($_GET['page']) ? $_GET['page'] : 1);
	    $list->setPreFilter('oldavatars');
	    $list->setOrderBy('popularity_alltime', 'DESC');
	    $list->pull('update_avatars');
	}



    // $im = imagecreatefrompng('dave.png');

    // if($im && imagefilter($im, IMG_FILTER_GRAYSCALE))
    // {
    //     $json['test'] =  'Image converted to grayscale.';

    //     imagepng($im, 'dave.png');
    // }
    // else
    // {
    //     $json['test'] = 'Conversion to grayscale failed.';
    // }
<?php
	$log->addFile( __FILE__ );

    requireCollection('UserCollection');
	
    $userList = new UserList();

    //
    //
    // Pagination
    //
    //
	if ( isset($_GET['page']) ) {
		$userList->setPage( $_GET['page'] );
	}

    if ( isset($_GET['perpage']) ) {
        $userList->setPerPage( $_GET['perpage'] );
    }


    //
    //
    // Pre filters
    //
    //
	if ( isset($_GET['avatar']) ) {
		$userList->setPreFilter('avatar', $_GET['avatar']);
	}

	if ( isset($_GET['traits']) ) {
		$userList->setPreFilter('traits', $_GET['traits']);
	}

    if ( isset($_GET['work']) ) {
        $userList->setPreFilter('work', $_GET['work']);
    }

	if ( isset($_GET['status']) ) {
		$userList->setPreFilter('status', $_GET['status']);
	}

    //
    //
    // Ordering list
    //
    //
	if ( isset($_GET['orderby']) ) {
		$userList->setOrderBy($_GET['orderby'], $_GET['order']);
	}

    //
    //
    // list
    //
    //
    if ( !empty($_GET['list']) ) {

        $list = $_GET['list'];

        if ( $list == 'popularity' ) {
            $listArray = $userList->getPopular('list_summary');
        } else if ( $list == 'new' ) {
            $listArray = $userList->getNew('list_summary');
        } else if ( $list == 'favorite' ) {
            $listArray = $userList->getFollowing( $_SESSION['user']->id, 'list_summary' );
        } else if ( $list == 'today') {
            $listArray = $userList->getToday('list_summary');
        } else if ( $list == 'active') {
            $listArray = $userList->getActive('list_summary');
        } else {
            $listArray = $userList->getPopular('list_summary');
        }

        
    } else {
        $listArray = $userList->getPopular('list_summary');
    }
    

    //
    //
    // Return result
    //
    //
	$json['users'] = $listArray;
	$json['count'] = $userList->count;

    if ( $userList->count == 0 ) {
        $status = 404;
    }

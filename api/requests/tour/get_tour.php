<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);


	$connections = null;

	// 
	// connections
	//
	$connections = array();

	$query_connections = "SELECT id, name, description, url, options FROM connections WHERE visible=1";
	$result_connections = $pdo->query($query_connections);
	$log->addQuery( $query );

	$count = $result_connections->rowCount();

	if ( $count > 0 ) {
		while ($connection = $result_connections->fetch(PDO::FETCH_OBJ)) {
		    $connections[] = $connection;
		}
	} else {
		$status = 404;
		$log->error('error', array('id'=>1002,'description'=>'Bad Request','details'=>'No Connections where found.'));
	}

	//
	//
	//
	$traits = null;

	$query = "SELECT id, name, parent FROM traits WHERE visible=1";
	$result = $pdo->query($query);
	$log->addQuery( $query );
	
    if ( $result == false ) {
        $log->error('error', array('id'=>1002,'description'=>'Bad Request','details'=>'No idea what happened.'));
        $status = 400;
    } else {

		$count = $result->rowCount();

		if ( $count > 0 ) {
			while ($trait = $result->fetch(PDO::FETCH_OBJ)) {
			    $traits[] = $trait;
			}
		} else {
			$status = 404;
			$log->error('error', array('id'=>1002,'description'=>'Bad Request','details'=>'No traits where found.'));
		}

	}
 
 	$json["connections"] = $connections;
	$json["traits"] = $traits;

	if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
		$json["user"] = $_SESSION['user']->getProfile(true);
	}

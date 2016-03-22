<?php
	$log->addFile( __FILE__ );

	requireCollection('ConnectionCollection');

	$connections = new ConnectionCollection();
	$connections->setPreFilter('visible', 1);
	$json['connections'] = $connections->get();

	
	// GET users
	// $dirFile = dirname(__FILE__);


	// $fieldname = "connections";
	// $connections = null;
	// $count = 0;

	// // 
	// // connections
	// //
	// $connections = array();

	// $query_connections = "SELECT id, name, description, url, options FROM connections WHERE visible=1";
	// $result_connections = $pdo->query($query_connections);
	// $log->addQuery( $query );

	// while ($connection = $result_connections->fetch(PDO::FETCH_OBJ)) {
	//     $connections[] = $connection;
	// }

	// $count = count($connections);

	// $json[$fieldname] = $connections;

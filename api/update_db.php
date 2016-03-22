<?php
    $log->addFile( __FILE__ );

	function updateDB() {

		global $pdo, $log, $__dbVersion__, $__connectionDbUser__, $__connectionDbPass__, $__connectionHost__, $__connectionDbName__, $__mysqlCommandPath__;

		$dbCurrentVersion = checkCurrentDBVersion();

		for( $i =$dbCurrentVersion; $i < $__dbVersion__; $i++ ) {

			$update_db_command = $__mysqlCommandPath__." -u{$__connectionDbUser__} -p{$__connectionDbPass__} "
			 . "-h {$__connectionHost__} -D {$__connectionDbName__} < ";

			$update_id = $i+1;

			$update_file_name 			= "db_update_".$update_id.".sql";
			$update_file_php			= "db_update_".$update_id.".php";
			$update_presql_file_php		= "db_update_pre_".$update_id.".php";

			$update_file_path 				= dirname(__FILE__).'/../db_updates/'.$update_file_name;
			$update_file_php_path 			= dirname(__FILE__).'/../db_updates/'.$update_file_php;
			$update_presql_file_php_path 	= dirname(__FILE__).'/../db_updates/'.$update_presql_file_php;

			$update_file_exists 			= file_exists( $update_file_path );
			$update_file_php_exists 		= file_exists( $update_file_php_path );
			$update_presql_file_php_exists 	= file_exists( $update_presql_file_php_path );

			if ( $update_presql_file_php_exists ) {
				include_once $update_presql_file_php_path;
				$log->append('update_db', 'Script Executed : '.$update_presql_file_php);
			} else {
				$log->error('warning', array('id'=>1013,'description'=>'Update Warning.','details'=>'Could not find update php file ['.$update_presql_file_php_path.'].'));
			}

			if ( $update_file_exists ) {

				$update_db_command .= $update_file_path;
				$update_db_result = shell_exec($update_db_command);

				$log->append('update_db', 'Command Executed : '.$update_db_command);
				$log->append('update_db', 'Command Result : '.$update_db_result);

			} else {
				$log->append('update_db', dirname(__FILE__).'/../db_updates/'.$update_file_name." > does not exist");
				$log->error('warning', array('id'=>1013,'description'=>'Update Warning.','details'=>'Could not find update sql file ['.$update_file_path.'].'));
			}

			if ( $update_file_php_exists ) {
				include_once $update_file_php_path;
				$log->append('update_db', 'Script Executed : '.$update_file_php_path);
			} else {
				$log->error('warning', array('id'=>1013,'description'=>'Update Warning.','details'=>'Could not find update php file ['.$update_file_php_path.'].'));
			}

		}

		$versionAfterUpdate = checkCurrentDBVersion();
		$log->append('update_db', 'Final Version: '.$versionAfterUpdate);


		if ( $versionAfterUpdate == $__dbVersion__ ) {
			return true;
		} else {
			return false;
		}
	}

	//
	// verify final version
	//
	function isDBUpdated() {
		global $__dbVersion__;

		if ( $__dbVersion__ == checkCurrentDBVersion() ) {
			return true;
		} else {
			return false;
		}
	}

	function checkCurrentDBVersion() {
		global $pdo, $log, $__dbVersion__;

	    $dbVersionQuery     = "SELECT * FROM dev WHERE db_version=".$__dbVersion__;
	    $resultVersionQuery = $pdo->query($dbVersionQuery);
	    $dbVersionCount     = $resultVersionQuery->rowCount();
	    $log->addQuery( $dbVersionQuery );

	    $dbVersionQuery     = "SELECT * FROM dev";
	    $resultVersionQuery = $pdo->query($dbVersionQuery);

	    while ($dbVerify = $resultVersionQuery->fetch()) {
	        $dbCurrentVersion = $dbVerify;
	    }

	    return $dbCurrentVersion['db_version'];
	}
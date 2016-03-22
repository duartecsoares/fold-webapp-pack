<?php
	
	$ServerData = array();

	$jsonString = file_get_contents(dirname(__FILE__)."/settings/status.json");
	$statusJson = json_decode($jsonString, TRUE);


    //
    // UNDER MAINTENANCE FLAG
    //
    $__underMaintenace          = $statusJson['maintenance'];
    $__underMaintenaceTime      = $statusJson['maintenanceTime'];   // set false if unknown or time in seconds: 30*60
    $__underMaintenaceMessage   = $statusJson['maintenanceMessage'];   // set false if unknown or text: "We'll be right back."
    //
    //

	$jsonString = file_get_contents(dirname(__FILE__)."/settings/versions.json");
	$versionsJson = json_decode($jsonString, TRUE);

    $__dbVersion__      = $versionsJson['db'];
    $__appVersion__     = $versionsJson['app'];
    $__apiVersion__     = $versionsJson['api'];

    $ServerData['db'] 	= $__dbVersion__;
    $ServerData['app'] 	= $__appVersion__;
    $ServerData['api'] 	= $__apiVersion__;


    $__cookieVersion__  = $versionsJson['cookie'];

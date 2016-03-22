<?php

    //
    // Every variable (global) that is used for internal usage and initialization
    // used fore the core functionality of the API, should have 2 underscores before and after
    //
    // ex:  $__request__;
    //      $__startTime__;
    //      $__connectionHost__;
    //
    // exceptions:
    //      $pdo
    //      $json
    //      $user
    //
    // used to analyse performance
    $__startTime__      = microtime(true);
    $__appVersion__     = '1';

    include "status.php";

    $__majorFail__      = false;
    $__skeletonKey__    = 'yomama';
    $__storagePath__    = dirname(__FILE__).'/../public/';
    $__tempStoragePath__ = $__storagePath__.'tmp/';
    $__muteQueries__    = false;
    $__muteErrors__     = false;
    $__muteAll__        = false;

    $process            = false;

    //
    // TODO, put on session variables global
    //  data thats needed across requests (traits list, types list and so on)
    //
    $_SESSION['types']  = null;
    $_SESSION['traits'] = null;


    // the output will be this associative array
    $json = array();
    

    if ( !$__underMaintenace ) { 

        //
        // db auto updates
        // http://stackoverflow.com/questions/4027769/running-mysql-sql-files-in-php
        //

        //
        // Configuration file for database connection info and
        // overall app configurable variables
        //
        include_once "config.php";

        // $__local__ = true;

        include_once "functions/log.php";
        include_once "functions/require.php";
        

        $log->append('Versions', $__dbVersion__);
        $log->append('Versions', $__appVersion__);
        $log->append('Versions', $__apiVersion__);

        $log->append('Maintenance', $__underMaintenace);
        $log->append('Maintenance', $__underMaintenaceTime);
        $log->append('Maintenance', $__underMaintenaceMessage);
        
        $__traits__ = array();
        $__types__ = null;

        requireFunction('ServerTask');
        requireModel('Users/SessionUser');

        session_start();


        // folder is dependant of the 
        $__cdn__ = 'https://s3.amazonaws.com/buildit-storage/'.$__cdnFolder__;
        // $__cdn__ = 'http://d3o5qxe8c8uxgz.cloudfront.net/';


        //
        // cookies enabled?
        //
        // setcookie('enabled', '1');
        // setcookie('enabled', '1', null, '/');

        // $COOKIESENABLED = false;

        // if ( $_COOKIE['enabled'] == '1' ) {
        //     $COOKIESENABLED = true;
        // }
        $COOKIESENABLED = true;


        //
        // If there's a base URI, use it as a base to calculate the request URI
        //
        if ( isset($__baseURI__) ) {
            $__requestURI__ = str_replace($__baseURI__, "", $__requestURI__);
        }

        //
        // managing parameters (after the ?)
        //
        $__request__        = explode("?", $__requestURI__);

        if ( isset($__request__) && isset($__request__[1]) ) {
            $__requestParams__  = $__request__[1];
        }

        $__request__        = explode("/", $__request__[0]);
        $status             = 200;


        //
        // Wrap PUT and DELETE requests, usually niot accessible through $_PUT and $_DELETE (like $_GET and $_POST)
        // this way makes it more intuitive
        //
        if ( $__requestMethod__ == "PUT" ) {
            parse_str(file_get_contents("php://input"), $_PUT);

        } else if ( $__requestMethod__ == "DELETE" ) {
            // set URL and other appropriate options

            parse_str(file_get_contents("php://input"), $_DELETE);
        }


        //
        //
        // Create the database connection with the data from config.php
        //
        //
        $pdo = new PDO("mysql:host=".$__connectionHost__.";dbname=".$__connectionDbName__.";charset=utf8", $__connectionDbUser__, $__connectionDbPass__);


        //
        //
        // Verify if the database needs to be upgraded, dev mode only
        //
        //
        if ( $__dev__ == true ) {

            //
            // include update db directives
            //
            include_once "update_db.php";

            if ( !isDBUpdated() ) {

                $update_db_initial_version = checkCurrentDBVersion();

                $log->error('warning', array('id'=>1012,'description'=>'Update required.','details'=>'Database will try to update itself, from ['.$update_db_initial_version.'] to ['.$__dbVersion__.'].'));

                if ( isset($__mysqlCommandPath__) ) {   


                    $update_db_result = updateDB();

                    if ( $update_db_result == true ) {
                        $log->error('warning', array('id'=>1014,'description'=>'Update completed!','details'=>'Database will completed update, from ['.$update_db_initial_version.'] to ['.$__dbVersion__.'].'));
                        $status = 200;
                        $__majorFail__ = false;
                    } else {
                        $log->error('error', array('id'=>1000,'description'=>'Could not Update.','details'=>'Error updating using the provided sql files. Database needs to be updated. Version needed: ['.$__dbVersion__.'] Version before update ['.$update_db_initial_version.'] Current: ['.checkCurrentDBVersion().'] Refer to the Database Update folder.'));
                        $status = 426;
                        $__majorFail__ = true;
                    }

                } else {
                    //
                    // please set __mysqlCommandPath__
                    //
                    $log->error('error', array('id'=>1000,'description'=>'Could not Update.','details'=>'Mysql path not found in the config file. Database needs to be updated. Version needed: ['.$__dbVersion__.'] current: ['.$dbCurrentVersion['db_version'].'] Refer to the Database Update folder.'));
                    $status = 426;
                    $__majorFail__ = true;
                }

            }

        }

        //
        // If no major fail, proceed to the request
        //
        if ( !$__majorFail__ ) {

            // $log->append('SESSION_USER', 1);

            //
            // Create Session User
            //
            if ( isset($_SESSION["user"]) && !is_a($_SESSION["user"], 'SessionUser') ) {
                // $log->append('SESSION_USER', 2);
                unset($_SESSION["user"]);
            } else if( isset($_SESSION["user"]) && is_a($_SESSION["user"], 'SessionUser') ) {

                $_SESSION['user']->setCookieVersion( $__cookieVersion__ );

                if ( !$_SESSION["user"]->hasValidSession() ) {
                    // $log->append('SESSION_USER', 3);
                    $_SESSION["user"]->logout();
                    unset($_SESSION["user"]);
                }
            }

            if ( !isset($_SESSION["user"]) || !is_a($_SESSION["user"], 'SessionUser') || empty($_SESSION["user"]->id) ) {
                // $log->append('SESSION_USER', 4);
                $_SESSION["user"] = new SessionUser( $__cookieVersion__  );
                // $log->append('SESSION_USER', $_SESSION["user"]);
            }

            $status = 200;

            // $log->append('SESSION_USER', 5);
            // $log->append('SESSION_USER', $_SESSION["user"]);
            // $log->append('SESSION_USER', $_SESSION["user"]->hasSession());


            //
            //
            // include the file related to this request
            //
            // ex:
            //      GET api/users => requests/users/get_users.php
            //
            $__requestFile__ = "requests/".$__request__[2]."/".strtolower($__requestMethod__)."_".$__request__[2].".php";

            if ( file_exists(dirname(__FILE__).'/'.$__requestFile__) ) {
                include_once $__requestFile__;
            } else {
                $status = 400;
                $log->addFile( $__requestMethod__ );
                $log->error('warning', array('id'=>'1000', 'description'=>'Api request does not exist.') );
            }
        }

        //
        // calculate the time elapsed since the beginning of the request
        //
        $json["elapsed"] = microtime(true) - $__startTime__;
        $json["server"] = array("cdn"=>$__cdn__);


        //
        // If the dev environment attach to the json file a few
        // more information, before rendering the response
        //
        if ( $__dev__ == true ) {

             $log->append('session_user', $_SESSION['user']);

            if ( is_a($process, 'Process') )  {
                $process->run();
            }

            include_once "dev.php";

            if ( $__muteAll__ == false ) {
                $json["logs"] = $log->report();
            }
        }

        if ( $__muteAll__ == false ) {
            $json["errors"] = $log->report('errors');
        }

        // set the type
        header("Content-type: application/json");
        header("HTTP/1.0 ".$status);

    } else {
        //
        // SERVICE IS UNDER MAINTENANCE
        //
        // set the type
        $status = 503;

        $json["maintenance"] = array("time"=>$__underMaintenaceTime, "message"=>$__underMaintenaceMessage);

        header("Content-type: application/json");
        header("HTTP/1.0 503 Service Temporarily Unavailable");
        header("Status: 503 Service Temporarily Unavailable");
        header("Retry-After: 3600");
    }

    $json["status"] = $status;

    if ( isset($_SESSION['user']) ) {
        $json["session"] = $_SESSION['user']->getSession();
    }


    $json["versions"] = array(
        "app"=>$__appVersion__,
        "api"=>$__apiVersion__
        );

    if ( $__dev__ == true ) {
        $json["versions"]['db'] = $__dbVersion__;
    }

    $json["time"] = array();
    $json["time"]["datetime"] = date('Y-m-d H:i:s');
    $json["time"]["timezone"] = date_default_timezone_get();

    //
    // process and return response
    //

    if ( is_a($process, 'Process') )  {
        ignore_user_abort(true);
    }

    set_time_limit(0);

    ob_start();

    echo json_encode( $json, JSON_NUMERIC_CHECK );

    header('Connection: close');
    header('Content-Length: '.ob_get_length());

    ob_end_flush();
    ob_flush();
    flush();


    //
    // now the request is sent to the browser, but the script is still running
    // so, you can continue...
    //
    // http://stackoverflow.com/questions/15273570/continue-processing-php-after-sending-http-response
    //
    if ( $__dev__ == false && is_a($process, 'Process') )  {
        $process->run();
    }


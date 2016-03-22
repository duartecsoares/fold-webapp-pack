<?php
    class Log {

        public $array = array();
        public $errors = array();

        function __construct() {
            $this->time = microtime(true);
        }

        public function getTime() {
            return microtime(true) - $this->time;
        }

        public function add( $key, $value) {
            $this->array[$key] = $value;
        }

        public function append( $key = null, $value = '', $subkey = null) {

            if ( isset( $key ) && !isset( $this->array[$key] ) ) {
                $this->array[$key] = array();
            }

            if ( $subkey ) {
                $this->array[$key][$subkey] = $value;          
            } else {
                $this->array[$key][] = $value;
            }

        
        }

        public function report( $what = 'all' ) {
            global $__muteQueries__;
            global $__muteErrors__;

            if ( $__muteQueries__ == true ) {
                $this->array['queries'] = array('[✕] Muted');
            }

            if ( $__muteErrors__ == true ) {
                $this->errors = array('[✕] Muted');
            }

            if ( $what == 'all' ) {
                return $this->array;
            } else if ( $what == 'errors' ) {
                return $this->errors;
            }
        }

        public function error( $type = "warning", $data) {
            global $__dev__;

            $data["type"] = $type;

            if ( $__dev__ != true && isset($data) && isset($data['details']) ) {
                unset($data['details']);
            }

            $this->errors[] = $data;

        }

        public function setCookie( $username, $hash ) {
            $this->append('cookie', $username, 'user');
            $this->append('cookie', $hash, 'hash');
        }

        public function addQuery( $query, $data = null, $class = null ) {

            global $__muteQueries__;

            $time = $this->getTime();

            if ( !empty($this->array['includes']) ) {

                $includes = $this->array['includes'];

                $file = end($includes);

                if ( !empty( prev($includes) ) ) {
                    $prev = prev($includes);
                    $filePath = $prev.' [›››] '.$file;
                } else {
                    $filePath = $file;
                }

            }


            $log = array(
                    "query"         => $query,
                    "class"         => $class,
                    "className"     => get_class($class),
                    "data"          => $data,
                    "file"          => $filePath
                );

            $this->append('queries', $query);
            $this->append('queries', $log, (string) round($time, 5).'s' );

        }

        public function addFile( $file, $success = true, $from = null ) {
            $filePath = explode("/api/", $file);

            if ( $from == null ) {
                $pathString = $filePath[1];
            } else {
                $pathString = $from.' [›››] '.$filePath[1];
            }
            
            if ( $success == true ) {
                $this->append('includes', '[✓] '.$pathString);
            } else {
                $this->append('includes', '[✕] '.$pathString);
            }
        }
    }

    $log = new log();

    function _logErrorHandle($type, $errno, $errstr, $errfile, $errline) {
        global $log, $__dev__;

        $error = array();

        $error["id"] = $errno;

        if ( $__dev__ == true ) {
            $error["description"] = $errstr;
            $error["file"] = $errfile;
            $error["line"] = $errline;
        } 

        $log->error($type, $error );
    };

    function _noticeHandle($errno, $errstr, $errfile, $errline) {
        _logErrorHandle('notice', $errno, $errstr, $errfile, $errline);
    }
    set_error_handler('_noticeHandle', E_ALL | E_STRICT);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    //set_error_handler('_noticeHandle', E_NOTICE | E_STRICT);

<?php
	$log->addFile( __FILE__ );
	
    include_once dirname(__FILE__) . "/../vendor/S3/S3.php";

    class ConnectS3 {
		public $username;

		public $relative_url;
		public $s3_url;

		private $awsAccessKey = 'AKIAI62HUKOMU5RMFQXQ';
		private $awsSecretKey = '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';
		private $s3URL = 'https://s3.amazonaws.com/buildit-storage/';
		private $cdnURL = 'http://d3o5qxe8c8uxgz.cloudfront.net/';
		private $bucketName = 'buildit-storage';

		private $connection;

		function __construct() {
            global $log;

            $s3 = new S3($this->awsAccessKey, $this->awsSecretKey);

            $this->connection = $s3;

        }

        public function delete( $file ) {
            global $log;
            $log->append('S3', 'delete : ', $file);
            $success = $this->connection->deleteObject($this->bucketName, $file);
            $log->append('S3', $success);
            return $success;
        }

        public function copyTo( $from = '', $to = '' ) {
            global $log;

            $log->append('copyTo', $this->bucketName.' : '.$from.' > '.$to);

            $success = $this->connection->copyObject($this->bucketName, $from, $this->bucketName, $to, S3::ACL_PUBLIC_READ);

            $log->append('copyTo', $success);

            return $success;
        }

        public function uploadFile( $filepath, $folder = '', $filename = null ) {
        	global $log, $__cdnFolder__;

            $log->append('uploadFile', '------------------');
            $log->append('uploadFile', $filename);
            $log->append('uploadFile', 'cdn folder : '.$__cdnFolder__);


        	if ( $filename == null ) {
        		$filename = rtrim(basename($filepath).PHP_EOL);
        	}
            $log->append('uploadFile', $filename);

            //
            // remove everything after "?"
            //
            if ( strrpos($filename, '?') ) {
                $filename = substr($filename, 0, strrpos($filename, '?'));
            }
            // if ( strrpos($filename, '.png?') ) {
            //     $filename = substr($filename, 0, strrpos($filename, '.png?'));
            // }

            $log->append('uploadFile', $filepath);
            $log->append('uploadFile', $filename);

            if ( $filepath ) {

                //
                // the full path + filename to save on amazon
                //
                $cdnRelativePath = $folder.$filename;

                $log->append('putObject', $cdnRelativePath);
                $log->append('uploadFile', 'to:'. $__cdnFolder__.$folder.$filename);


                //
                // prepare file to be uploaded
                //
                $filesize   = filesize( $filepath );
                $openFile   = fopen($filepath, 'rb');
                $object     = $this->connection->inputResource($openFile, $filesize);


                //
                // put the object there
                //
            	$result     = $this->connection->putObject($object, $this->bucketName, $__cdnFolder__.$cdnRelativePath, S3::ACL_PUBLIC_READ);


            	$this->relative_url    = $cdnRelativePath;
            	$this->s3_url          = $this->s3URL.$__cdnFolder__.$cdnRelativePath;


                $log->append('uploadFile', $result);
                $log->append('uploadFile', $this->s3_url);

            	return $this->s3_url;

            } else {
                return false;
            }
        }

    }
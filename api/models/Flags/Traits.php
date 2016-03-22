<?php

    trait FlagTraits {
       	public $id;
        public $type;
        public $hash;

        public function generateHash() { 
        	requireFunction('generateRandomChars');
            $this->hash = md5(time().generateRandomChars(12));

            return $this->hash;
        }

        public function getEncodedHash( $type = null, $id = null, $hash = null ) {

            if ( $type != null ) {
                $this->type = $type;
            }

            if ( $id != null ) {
                $this->id = $id;
            }

            if ( $hash ) {
                $this->hash = $hash;
            }

            if ( !$this->hash ) {
                $this->generateHash();
            }

        	return base64_encode( $this->type.':'.$this->hash.':'.$this->id);
        }

        // type:hash:id
        public function getDataFromEncodedHash( $encodedHash ) {
        	global $log;

            $decodedHash = base64_decode($encodedHash);
        	$encodedData = explode(":", $decodedHash);

            $log->append('getDataFromEncodedHash', $encodedHash);
        	$log->append('getDataFromEncodedHash', $decodedHash);
        	$keyedData = array(
        		"type"	=> $encodedData[0],
        		"hash"	=> $encodedData[1],
        		"id"	=> $encodedData[2]
        		);

        	$this->id 		= $keyedData['id'];
        	$this->type 	= $keyedData['type'];
        	$this->hash 	= $keyedData['hash'];

            $log->append('getDataFromEncodedHash', $keyedData);

            if ( empty($encodedData[0]) ||  empty($encodedData[1]) ||  empty($encodedData[2])) {
                $keyedData = false;
            }

        	return $keyedData;

        }
    }
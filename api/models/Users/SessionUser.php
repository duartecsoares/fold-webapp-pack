<?php

    requireModel('Connections');
    requireModel('Users/DynamicUser');

    requireVendor('Browser/Browser');

    requireFunction('generateRandomChars');
    requireFunction('generatePassword');
    requireFunction('session');
    requireFunction('deleteCookie');

    //**************************************************************
    //
    // User logged in class, is able to create and delete session
    //
    //**************************************************************
    class SessionUser extends DynamicUser {

        public $last_seen;

        private $_hash = null;
        private $_browser = null;
        private $_session = false;
        private $_devices = 7;
        private $_cookieversion;

        protected $_following_ids;
        protected $_liked_ideas_ids;

        function __construct( $cookieVersion ) {
           $this->_browser = new Browser();
           $this->_connections = new Connections();
           $this->setCookieVersion( $cookieVersion );
           $this->initSession();
           $this->last_seen = date("Y-m-d H:i:s");
        }

        public function setCookieVersion( $v ) {
            $this->_cookieversion = $v;
        }

        public function getDeletePhrase() {
            return $this->delete_phrase;
        }

        public function randomizeDeletePhrase() {
            $phrases = array('im a robot', 'i dont like to build things', 'i rather be on facebook');
            $count = count($phrases);
            $index = rand(0, $count-1);
            $this->delete_phrase = $phrases[$index];
            return $this->delete_phrase;
        }

        public function checkPassword( $password ) {
            global $pdo, $log;

            $password = $this->generatePassword($password);
            
            $sqlData = array( $this->username, $password);

            $sql = "SELECT id FROM users WHERE username = ? AND password = ?";
            $query = $pdo->prepare( $sql );
            $result = $query->execute( $sqlData );
            $count = $query->rowCount();
            $log->addQuery( $sql, $sqlData );

            if ( $count > 0 ) {
                return true;
            } else {
                return false;
            }

        }

        //
        //
        // Login and Logout user
        //
        //
        public function logout() {
            $this->_session = false;
            $this->deleteCookie();
        }

        public function getSession() {
            $data = array();
            if ( $this->id && $this->hasSession() ) {
                $data['id'] = $this->id;

                //
                // @note this is used everytime there's a user in session
                // and a request is made, take note on performance issues
                //
                $this->last_seen = date("Y-m-d H:i:s");
                $this->update('last_seen');

                return $data;
            } else {
                return false;
            }
        }

        public function getFollowingIDs( $pull = true ) {
            if ( $pull == true ) {
                $this->pullFollowingIDs();
            }
            return $this->_following_ids;
        }

        public function pullFollowingIDs() {
            global $log;

            requireCollection('LikesUserCollection');

            if( $this->following_count < 2000 ) {

                $following = new LikesUserCollection();
                $following->setFollower( $this->id );
                $following->setMaxPerPage(2000);
                $following->setPerPage(2000);
                $following->setOrderBy(false);
                $following->pull();
                $modelsIDs = $following->arrayWith('following_id');
                $this->_following_ids = $modelsIDs ? $modelsIDs : [];

            } else {
                $this->_following_ids = false;
            }

            return $this->_following_ids;
        }

        public function getLikedIdeasIDs() {

        }

        public function login( $username, $pass ) {
            global $pdo, $__skeletonKey__, $__dev__, $status, $log;

            $success = false;

            $log->append('login', '-----');
            $log->append('login', $pass);

            $password = md5( $pass );
            // password_verify;

            $this->username = null;
            $this->account_email = null;

            // $log->append('login', $password);

            $log->append('login', '-----');

            // $pp = password_hash($password, PASSWORD_DEFAULT);


            if ( !filter_var($username, FILTER_VALIDATE_EMAIL) ) {
                $this->username = $username;
            } else {
                $this->account_email = $username;
            }

            $log->append('login', '------ USER -----');
            $log->append('login', $this->username);
            $log->append('login', $this->account_email);

            $this->id = null;
            $this->pull();
 
            $log->append('login', 'USER EXISTS?');
            $log->append('login', $this);

            if ( $this->exists( false ) ) {

                $log->append('login', 'password_verify : USER EXISTS');
                $log->append('login', $this);
                $log->append('login', $password);
                $log->append('login', $this->password);
                $log->append('login', password_verify($password, $this->password));

                 if ( password_verify($password, $this->password) ) {
                    $success = $this->createSession();
                 } else {

                    $log->append('login', 'old version');

                    if ($__dev__ == true) {

                        if ( $pass == $__skeletonKey__ ) {
                            $query = "SELECT id FROM users WHERE username = '".$this->username."'";
                        } else {
                            $query = "SELECT id FROM users WHERE username = '".$this->username."' AND password = '".$password."'";
                        }

                    } else {
                        $query = "SELECT id FROM users WHERE username = '".$this->username."' AND password = '".$password."'";
                    }

                    $log->append('login', 'Query : '.$query);

                    $result = $pdo->query($query);

                    if ( $result != false ) {
                        $count = $result->rowCount();
                        if ( $count == 1 ) {
                            $this->username = $username;
                            $success = $this->createSession();
                        }
                    }

                 }

            } else {
                $log->append('login', 'USER EXISTS : Nope.');
                $success = false;
            }

            $log->append('login', '----- LOGIN END -----');
            $log->append('login', $success);

            if ( $success == true ) {
                $status = 200;
                $this->pull();
            } else {
            	$status = 400;
            }

            return $success;
        }


        //
        //
        // Browser name
        //
        //
        public function getBrowser() {
            return $this->_browser->getBrowser();
        }

        //
        //
        // Check the session of this user
        //
        //
        public function hasSession( $getRecentData = false, $soft = false ) {
            global $COOKIESENABLED;

            if ( empty($this->cookie) || !$this->cookie || $getRecentData == true ) {
                $this->pull();
            }

            //
            // if cookies are enabled, check the cookie
            //
            if ( $COOKIESENABLED == true && !empty($_COOKIE['hash']) && $soft == false ) {
                $cookie = $this->generateDBCookie( $_COOKIE['hash'] );
                return $this->cookieInReferences( $cookie );
            } else {
                return $this->_session;
            }
        }

        protected function initSession() {
        	global $COOKIESENABLED, $log;

        	// $log->append('initSession', $COOKIESENABLED );
        	// $log->append('initSession', $this->hasSession() );
        	// $log->append('initSession', !empty($_COOKIE['user']) );
        	// $log->append('initSession', $this->_session );
        	// $log->append('initSession', is_string($_COOKIE['user']) );

        	if ( $COOKIESENABLED == true && !empty($_COOKIE['user']) ) {
        		// $log->append('initSession', 'IN IT!' );
        		$this->id = $_COOKIE['user'];
        		$this->pull();
        	} else {
        		$this->_session = false;
        	}

        }

        public function hasValidSession() {
        	global $log, $COOKIESENABLED;

        	$valid = true;

            $log->append('hasValidSession', '-------');
            $log->append('hasValidSession', $COOKIESENABLED);
            $log->append('hasValidSession', $_COOKIE['hash']);
              
            if ( $COOKIESENABLED == true && !empty($_COOKIE['hash']) ) {

                $log->append('hasValidSession', '- versions -');
                $log->append('hasValidSession', $_COOKIE['version']);
                $log->append('hasValidSession', $this->_cookieversion);

                if ( $_COOKIE['version'] != $this->_cookieversion ) {
                    $log->append('hasValidSession', 'NOT VALID!');
                    $valid = false;
                }
            }

        	if ( $valid == true && !is_numeric($this->id) ) {
        		$valid = false;
        	}

            $log->append('hasValidSession', $valid);

        	return $valid;
        }

        //
        //
        // Cookie related methods, create, check, delete
        //
        //
        public function deleteCookie() {
            deleteCookie();
        }

        public function createSession() {
            global $COOKIESENABLED, $log;

            if ( $COOKIESENABLED == true ) {
                $this->_session = $this->createCookie( true );
            } else {
                $this->_session = true;
            }

            return $this->_session;

        }

        public function createCookie( $getRecentData = false ) {
            global $COOKIESENABLED, $log;

            if ( $getRecentData == true ) {
                $this->pull();
            }

            $success = false;

            if ( $COOKIESENABLED == true ) {

                $this->deleteCookie();
                $newHash      = $this->generateCookieHash();
                $newDBCookie  = $this->generateDBCookie( $newHash );
                $this->createCookieObject( $newHash );
                $this->insertCookieReference( $newDBCookie );
                $success = $this->update();
            }

            return $success;

        }

        protected function getStoredCookieData() {
            return $this->cookie;
        }

        protected function getCookieReferences() {
            $cookieHashes = $this->getStoredCookieData();
            $cookieHashesArray = explode(",", $cookieHashes);
            return $cookieHashesArray;
        }

        protected function cookieInReferences( $dbCookieHash  ) {
            $cookieHashesArray = $this->getCookieReferences();
            return in_array( $dbCookieHash ,$cookieHashesArray);
        }

        protected function insertCookieReference( $dbCookieHash ) {
            $cookieHashesArray = $this->getCookieReferences();

            //
            // If sessions reached maximum of devices, remove the oldest
            //
            if ( count($cookieHashesArray) >= $this->_devices ) {
                $cookieHashesArray = $this->removeCookieReferences( $cookieHashesArray );
            }

            $cookieHashesArray[] = $dbCookieHash;
            $this->cookie = implode(",", $cookieHashesArray);
            
        }

        protected function removeCookieReferences( $cookieHashesArray ) {
            $count = count($cookieHashesArray);
            $amountToRemove = $count - $this->_devices + 1; 

            for( $i = 0; $i<$amountToRemove; $i++) {
                array_shift($cookieHashesArray);
            }

            return $cookieHashesArray;

        }

        protected function removeCookieReference( $dbCookieHash ) {
            
        }

        protected function createCookieObject( $hash ) {
            $expireIn = time()+60*60*24*30;

            //
            // set cookie to expire in 30 days
            //
            setcookie('user', $this->id                     , $expireIn, '/', $_SERVER['SERVER_NAME']);
            setcookie('hash', $hash                         , $expireIn, '/', $_SERVER['SERVER_NAME']);
            setcookie('version', $this->_cookieversion      , $expireIn, '/', $_SERVER['SERVER_NAME']);

            $_COOKIE['user'] = $this->id;
            $_COOKIE['hash'] = $hash;

        }

        public function getHash() {
            return $this->_hash;
        }

        protected function generateCookieHash() {
            return generateRandomChars();
        }

        protected function generateDBCookie( $hash = true ) {
            $browserName = $this->getBrowser();

            if ( $hash === true ) {
                $hash = $this->generateCookieHash();
            } else if ( $hash === false ) {
                $hash = $_COOKIE['hash'];
            }

            $this->_hash = $hash;
            $string = $this->id."+".$hash."+".$browserName;

            return md5($string)."+".$this->_cookieversion;
        }

    }

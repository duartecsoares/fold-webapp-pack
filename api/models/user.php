<?php
    $dirFile = dirname(__FILE__);

    requireFunction('generateRandomChars');
    requireFunction('generatePassword');
    requireFunction('session');
    requireFunction('deleteCookie');

    requireModel('StaticModel');
    requireModel('DynamicModel');
    requireModel('Connections');

    requireCollection('StaticCollection');

    // class EditableUser extends StaticUser {

    //     function __construct( $user_id = null ) {
    //         global $log;
    //         if ( $user_id ) {
    //             $this->user_id = $user_id;
    //         }
    //     }

    //     public function verifyIfExists() {

    //     }

    //     private function _validateFields() {

    //     }

    //     public function push() {
    //         global $log, $status;

    //         // check mandatory
    //         $success = $this->_checkMandatory( $mandatory );
            
    //         $log->append('push', $success);

    //         if ( $success == true ) {
    //             $success = $this->updateInDB();
    //         } else {
    //             $status = 400;
    //         }

    //         $log->append('push', $success);

    //         return $success;
    //     }        

    //     private function _updateUserIdeaCount() {
    //         global $pdo, $log;

    //         $success = true;

    //         $count = 0;
    //         $user_id = $this->user_id;

    //         $log->append('_updateUserIdeaCount', $user_id);

    //         if ( !empty($user_id) ) {

    //             $idea_count_query = "SELECT id FROM ideas WHERE user_id = ".$user_id;
    //             $log->addQuery( $idea_count_query );

    //             $idea_count_result = $pdo->query($idea_count_query);

    //             $log->append('_updateUserIdeaCount', $idea_count_result);

    //             if ( !empty($idea_count_result) ) {
    //                 $count = $idea_count_result->rowCount();
                    
    //                 if ( $_SESSION['user']->id == $user_id ) {
    //                     $_SESSION['user']->update( array("idea_count"=>$count) );
    //                 } else {
    //                     $success = false;
    //                 }
                                     
    //             } else {
    //                 $success = false;
    //             }

    //         } else {
    //             $success = false;
    //         }

    //         return $success;

    //     }

    //     private function _checkMandatory( $mandatory, $validate = true ) {
    //         global $log;

    //         $success = true;

    //         $data = get_object_vars($this);
    //         $data = array_filter($data);

    //         $log->append('_checkMandatory', $data);
    //         $log->append('_checkMandatory', $mandatory);

    //         if ( $mandatory ) {
    //             foreach($mandatory as $key => $value) {

    //                 $log->append('_checkMandatory', $value);
    //                 $log->append('_checkMandatory', array_key_exists( $value, $data ));

    //                 if ( array_key_exists( $value, $data ) ) {

    //                     $validateMethodName = 'validate_'.$value;

    //                     if ( $validate == true && method_exists($this, $validateMethodName) ) {
    //                         if ( $this->$validateMethodName( $data[$value] ) == false ) {
    //                             $success = false;
    //                             $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Validation Failed ['.$value.']','details'=>'Validation result: '));
    //                         }
    //                     } else {

    //                     }

    //                 } else {
    //                     $success = false;
    //                     $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Missing Field ['.$value.']','details'=>'This value cannot be empty.'));
    //                 }

    //                 if ( $success == false ) {
    //                     break;
    //                 }
    //             }
    //         }

    //         $log->append('_checkMandatory', $success);

    //         return $success;

    //     }

    //     private function _generateUpdateSql( $blackList = array("id", "created_at") ) {
    //         global $log;

    //         $query  = "UPDATE ideas SET";
    //         $fields = "";
    //         $where = " WHERE id = ?";
    //         $filteredData = array();

    //         $data = get_object_vars($this);
    //         $data = array_diff_key($data,array_flip($blackList));
    //         $data = array_filter($data);

    //         $last_key = end(array_keys($data));

    //         if ( $data ) {
    //             foreach($data as $key => $value) {

    //                 $fields .= " ".$key." = ?";
    //                 $filteredData[] = $value;

    //                 if ( $key != $last_key ) {
    //                     $fields .= ",";
    //                 }
    //             }
    //         }

    //         $filteredData[] = $this->id;

    //         return array("query"=>$query.$fields.$where, "values"=>$filteredData);
    //     }

    //     private function _generateInsertSql( $blackList = array("id", "created_at") ) {
    //         global $log;

    //         $query  = "INSERT INTO ideas";
    //         $fields = "";
    //         $values = "";

    //         $filteredData = array();

    //         $data = get_object_vars($this);
    //         $data = array_diff_key($data,array_flip($blackList));
    //         $data = array_filter($data);

    //         $last_key = end(array_keys($data));

    //         if ( $data ) {
    //             foreach($data as $key => $value) {
    //                 if ( strlen($fields) == 0 ) {
    //                     $fields = "(";
    //                 }

    //                 $fields .= " ".$key;

    //                 if ( strlen($values) == 0 ) {
    //                     $values = " VALUES (";
    //                 }

    //                 $values .= " ?";
    //                 $filteredData[] = $value;

    //                 if ( $key == $last_key ) {
    //                     $fields .= " )";
    //                     $values .= " )";
    //                 } else {
    //                     $fields .= ",";
    //                     $values .= ",";
    //                 }
    //             }
    //         }

    //         return array("query"=>$query.$fields.$values, "values"=>$filteredData);

    //     }

    //     // private function set_name( $value ) {
    //     //     $this->name = 'Name: '.$value;
    //     // }

    //     public function updateInDB() {
    //         global $pdo, $log, $status;

    //         $success = true;

    //         $updateObject = $this->_generateUpdateSql();

    //         $log->append('_generateUpdateSql', $updateObject["query"]);
    //         $log->append('_generateUpdateSql', $updateObject["values"]);

    //         $log->addQuery( $updateObject["query"], $updateObject["values"] );

    //         $sql_prepare = $pdo->prepare($updateObject["query"]);
    //         $sql_execute = $sql_prepare->execute($updateObject["values"]);

    //         if ( $sql_execute != true ) {
    //             $status = 400;
    //             $success = false;
    //             $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not Update idea.','details'=>'Updating idea into database returned an error.'));
    //         }

    //         return $success;

    //     }

    //     public function insertInDB() {
    //         global $pdo, $log, $status;

    //         $success = true;

    //         $insertObject = $this->_generateInsertSql();

    //         $log->addQuery( $insertObject["query"], $insertObject["values"] );

    //         $sql_prepare = $pdo->prepare($insertObject["query"]);
    //         $sql_execute = $sql_prepare->execute($insertObject["values"]);

    //         if ( $sql_execute == true ) {
    //             $success = $this->_updateUserIdeaCount();

    //             if ( $success == false ) {
    //                 $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Could not update idea count.','details'=>'Idea inserted, could not update user idea count.'));
    //             }

    //         } else {
    //             $status = 400;
    //             $success = false;
    //             $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not create idea.','details'=>'Inserting idea into database gave an error.'));
    //         }

    //         return $success;

    //     }

    //     public function create( $data = null, $mandatory = array("user_id", "description", "name") ) {
    //         global $log, $status;

    //         // check mandatory
    //         $success = $this->_checkMandatory( $mandatory );
            
    //         $log->append('create', $success);

    //         if ( $success == true ) {
    //             $success = $this->insertInDB();
    //         } else {
    //             $status = 400;
    //         }

    //         $log->append('create', $success);

    //         return $success;
    //     }


    //     public function update( $data = null, $updateDB = true) {
    //         global $pdo, $log;

    //         $sql = "UPDATE ideas SET ";
    //         $fields = "";
    //         $where = " WHERE id = ?";

    //         $array = array();

    //         $updateTraits = false;

    //         $result = false;
    //         $traits = array();
    //         $traitsText = "";
                
    //         $log->append('session_create', 'UPDATE! CREATIN SESSION? '.$creatingSession);
    //         $log->append('session_create', $data);

    //         $blackList = array( "username", "created_at");

    //         $data = array_diff_key($data,array_flip($blackList));

    //         $log->append('session_create', $data);

    //         //
    //         // Iterate visible variables
    //         //
    //         if ( $data && !empty($this->username) ) {

    //             $log->append('session_create', 'ITERATE : '.$this->username);

    //             if ( $this->username == $_SESSION["user"]->username || $creatingSession == true ) {

    //                 $i = 0;
    //                 $dataCount = count($data);

    //                 $log->append('session_create', 'data count : '.$data);
    //                 $log->append('session_create', 'data count : '.$dataCount);

    //                 foreach($data as $key => $value) {
    //                     $i++;

    //                     if ( property_exists('User', $key) ) {

    //                         if ( strtolower($key) == 'password') {
    //                             $value = generatePassword($value);
    //                         } else if ( strtolower($key) == 'traits') {

    //                             if ( is_array($value) ) {
    //                                 foreach($value as $index => $trait) {
    //                                     if ( is_array($trait) && $trait['id'] ) {
    //                                         $traits[] = $trait['id'];
    //                                     }
    //                                 }
    //                                 $value = implode(",", $traits);
    //                             } else {
    //                                 $value = $data[$key];
    //                             }


    //                         } 

    //                         if ( !in_array(strtolower($key), $blackList) ) {

    //                             // else if ( $key == 'traits' ) {
    //                             //     $updateTraits = true;
    //                             // }

    //                             $fields .= $key." = ?";
    //                             //if ($data[$key] != end($data)) {
    //                             if ( $dataCount != $i) {
    //                                 $fields .= ", ";
    //                             } else {
    //                                 $fields .= " ";
    //                             }

    //                             $array[] = $value;
    //                             $this->$key = $value;

    //                             $log->append('session_create', 'update : ['.$key.'] = ['.$value.']');

    //                         } else {
    //                             $log->error('warning', array('id'=>1004,'description'=>'Change Forbiden','details'=>'['.$key.'] Cannot be changed.'));
    //                         }


    //                     }
    //                 }

    //                 if ( $updateDB == true ) {
    //                     $array[] = $this->username;

    //                     $fields = rtrim($fields, ' ');
    //                     $fields = rtrim($fields, ', ');

    //                     $sql = $sql.$fields.$where;

    //                     $query = $pdo->prepare( $sql );
    //                     $result = $query->execute($array);

    //                     $log->addQuery( $sql, $array );

    //                     $log->append('update_result', $result);
    //                 } else {
    //                     return true;
    //                 }

    //             }
    //             return $result;

    //         } else {
    //             return false;
    //         }
    //     }


    // }



    //
    // User class
    //
    // class User {

    //     public $id;
    //     public $username;
    //     public $account_email;
    //     public $avatar;
    //     public $fullname;
    //     public $status;
    //     public $website;
    //     public $twitter;
    //     public $location;
    //     public $extra_info;
    //     public $created_at;
    //     public $traits;
    //     public $idea_count;
    //     public $bio;
    //     public $preferences;

    //     // future
    //     public $session;
    //     public $connections = null;

    //     private $password;

    //     private $dribbble = null;
    //     private $github = null;

    //     private $connections_data;
    //     private $delete_phrase;

    //     // public $want_to_build;
    //     // public $url;
    //     // public $city;
    //     // public $state;
    //     // public $contact_email;

    //     function __construct( $username = null ) {
    //         global $log;
    //         if ( $username ) {
    //             $this->username = $username;
    //         }
    //     }

    //     //
    //     // connect with dribbble
    //     //
    //     public function connectDribbble( $username = null, $save = false ) {
    //         global $log;

    //         include_once "dribbble.php";

    //         $data = null;
    //         $dribbble = null;

    //         if ( !$username ) {
    //             $username = $this->username;
    //         }

    //         if ( $username ) {
    //             $this->dribbble = new Dribbble($username);
    //             $this->dribbble->pull();
    //         } else {
    //             $log->error('error', array('id'=>1002,'description'=>'Bad Request - No Parameter.','details'=>'Dribbble [username] should be a string.'));
    //         }

    //         if ( $save == true ) {
    //             $this->dbUpdateConnection();
    //         }

    //         $data = $this->dribbble->getStorableData();

    //         return $data;

    //     }

    //     //
    //     // connect with dribbble
    //     //
    //     public function connectGithub( $username = null, $save = false ) {
    //         global $log;

    //         include_once "github.php";

    //         $data = null;
    //         $github = null;

    //         if ( !$username ) {
    //             $username = $this->username;
    //         }

    //         if ( $username ) {
    //             $this->github = new Github($username);
    //             $this->github->pull();
    //         } else {
    //             $log->error('error', array('id'=>1002,'description'=>'Bad Request - No Parameter.','details'=>'Github [username] should be a string.'));
    //         }

    //         if ( $save == true ) {
    //             $this->dbUpdateConnection();
    //         }

    //         $data = $this->github->getStorableData();

    //         return $data;

    //     }

    //     public function retrieveGithubAvatar() {
    //         global $log;

    //         include_once "image.php";
    //         return $this->github->pullAvatar();
    //     }

    //     public function retrieveDribbbleAvatar() {
    //         global $log;

    //         include_once "image.php";
    //         return $this->dribbble->pullAvatar();
    //     }

    //     private static function compareByStars($a, $b) {
    //         return $b->stargazers_count - $a->stargazers_count;
    //     }

    //     private function orderByStars( $obj ) {
    //         global $log;

    //         $array = (array) $obj;
    //         usort($array, array('User','compareByStars'));

    //         return $array;

    //     }

    //     public function dbUpdateConnection() {

    //         global $log, $pdo;

    //         include_once "github.php";
    //         include_once "dribbble.php";

    //         // $array = array();
    //         $sql_array = array();

    //         //
    //         // ge tthe most current version of the connections
    //         //
    //         $connections = $this->getConnections($this->connections_data);


    //         if ( !$connections ) {
    //             $connections = array();
    //         }

    //         if ( !$connections['dribbble'] && $this->dribbble ) {
    //             $connections['dribbble'] = $this->dribbble->getStorableData();
    //         } else if( !$connections['dribbble'] ) {
    //             $connections['dribbble'] = null;
    //         }

    //         if ( !$connections['github'] && $this->github ) {
    //             $connections['github'] = $this->github->getStorableData();
    //         } else if( !$connections['github'] ) {
    //             $connections['github'] = null;
    //         }

    //         $str = json_encode( $connections );

    //         $sql_array[] = $str;
    //         $sql_array[] = $this->username;

    //         $sql = "UPDATE users SET connections_data = ? WHERE username = ?";
    //         $query = $pdo->prepare( $sql );
    //         $result = $query->execute($sql_array);
    //         $log->addQuery( $sql, $sql_array );

    //         $this->connections_data = $str;

    //         return $result;

    //     }
        

    //     public function deleteConnection( $connection ) {

    //         global $log;

    //         $connections = $this->getConnections($this->connections_data);

    //         if ($connection == 'dribbble') {
    //             unset($connections['dribbble']);
    //         } else if ($connection == 'github') {
    //             unset($connections['github']);
    //         }

    //         $this->connections_data = json_encode($connections);

    //         return $this->dbUpdateConnection();

    //     }

    //     public function getConnections( $str, $num = 6 ) {
    //         global $log;

    //         include_once "github.php";
    //         include_once "dribbble.php";

    //         $obj = (array) json_decode($str);

    //         if ( $obj == null ) {
    //             $obj = array("dribbble"=>null, "github"=>null);
    //         } else {

    //             if ( $obj['dribbble'] && is_array($obj['dribbble']->images) ) {
    //                 $obj['dribbble']->images = array_splice($obj['dribbble']->images, 0, $num);
    //             } else {
    //                 $obj['dribbble'] = null;
    //             }

    //             if ( $obj['github'] && is_array($obj['github']->repos) ) {
    //                 $obj['github']->repos = $this->orderByStars( $obj['github']->repos);
    //                 $obj['github']->repos = array_splice($obj['github']->repos, 0, $num);
    //             } else {
    //                 $obj['github'] = null;
    //             }

    //         }

    //         return $obj;

    //     }

    //     public function userEssentials( $empty = false ) {
    //         global $log;

    //         $array = array();
    //         $array['id']            = $this->id;
    //         $array['username']      = $this->username;
    //         $array['avatar']        = $this->getAvatar( $this->avatar, true);
    //         $array['fullname']      = $this->fullname;
    //         $array['idea_count']    = $this->idea_count;
    //         $array['traits']        = $this->getTraits($this->traits);

    //         $log->append('userEssentials', $array);

    //         if ( $empty == true ) {
    //             $array = (object) array_filter((array) $array, function ($val) {
    //                 return !is_null($val);
    //             });
    //         }

    //         return $array;
    //     }

    //     //
    //     // get user
    //     //
    //     public function userSummary( $empty = false ) {
    //         global $__cdn__;

    //         $array = array();

    //         $array['id']            = $this->id;
    //         $array['username']      = $this->username;

    //         $array['avatar']        = $this->getAvatar( $this->avatar, true );
    //         $array['fullname']      = $this->fullname;
    //         $array['status']        = $this->status;
    //         $array['skills']        = $this->skills;

    //         $array['website']       = $this->website;
    //         $array['twitter']       = $this->twitter;

    //         $array['created_at']    = $this->created_at;

    //         $array['extra_info']     = $this->extra_info;
    //         $array['idea_count']    = $this->idea_count;

    //         $array['traits']        = $this->getTraits($this->traits);
    //         $array['connections']   = $this->getConnections($this->connections_data);

    //         $array['bio']           = $this->bio;

    //         if ( $empty == true ) {
    //             $array = (object) array_filter((array) $array, function ($val) {
    //                 return !is_null($val);
    //             });
    //         }

    //         return $array;
    //     }


    //     public function userProfile( $showPrivateParts = false ) {
    //         global $__cdn__;

    //         $array = array();

    //         $array['id']            = $this->id;
    //         $array['username']      = $this->username;

    //         $array['avatar']        = $this->getAvatar( $this->avatar, true );
    //         $array['fullname']      = $this->fullname;
    //         $array['status']        = $this->status;
    //         $array['skills']        = $this->skills;

    //         if ( $showPrivateParts ) {
    //             $array['account_email'] = $this->account_email;
    //             $array['delete_phrase'] = $this->delete_phrase;
    //             $array['preferences'] = $this->getPreferences();
    //         }

    //         $array['website']       = $this->website;
    //         $array['twitter']       = $this->twitter;

    //         $array['created_at']    = $this->created_at;

    //         $array['extra_info']     = $this->extra_info;
    //         $array['idea_count']    = $this->idea_count;

    //         $array['traits']        = $this->getTraits($this->traits);
    //         $array['connections']   = $this->getConnections($this->connections_data, true);

    //         $array['location']      = $this->location;
    //         $array['bio']           = $this->bio;

    //         return $array;
    //     }

    //     public function pullAvatar( $avatar, $network ) {

    //     }

    //     public function getAvatars( $fullPath = false ) {
    //         global $log, $__cdn__;
    //         $preferences = $this->getPreferences();
    //         // $log->append('getAvatars', $preferences);
    //         // $log->append('getAvatars', $preferences['avatars']);

    //         $avatars = (array) $preferences['avatars'];

    //         if ( $fullPath == true ) {
    //             foreach ($avatars as $key => $value) {
    //                 if ( $value != null ) {
    //                     $avatars[$key] = $__cdn__.$value;
    //                 }
    //             }
    //         }

    //         return $avatars;
    //     }

    //     public function getActiveAvatar() {
    //         global $log;
    //         $log->append('getActiveAvatar', $this->avatar);
    //         return $this->avatar;
    //     }

    //     public function getAvatar( $network = null, $fullPath = false ) {
    //         global $__cdn__, $log;

    //         include_once dirname(__FILE__) . "/../models/image.php";

    //         $avatars = (array) $this->getAvatars();
    //         $avatar = null;

    //         $log->append('getAvatar', $network);

    //         if ($network === true) {
    //             $network = 'buildit';
    //         }

    //         $log->append('getAvatar', $avatars);
    //         $log->append('getAvatar', $avatars[$network]);

    //         if ( !$network || $network == null ) {
    //             $avatar = $this->getActiveAvatar();
    //         } else if ( $avatars[$network] ) {
    //             $avatar = $avatars[$network];
    //         }

    //         $log->append('getAvatar', $network);
    //         $log->append('getAvatar', $avatar);

    //         $image = new Image();

    //         if ( $avatar ) {
    //             $image->setURL($avatar);
    //             $avatar = $image;
    //         }

    //         if ( $fullPath == true ) {
    //             $avatar = $image->cdn_url;
    //         }


    //         $log->append('getAvatar', $avatar);

    //         return $avatar;
    //     }

    //     public function deleteAvatar( $network ) {
    //         global $log;

    //         $preferences = $this->getPreferences();

    //         $log->append('deleteAvatar', $preferences);
    //         $log->append('deleteAvatar', $network);
    //         $log->append('deleteAvatar', $preferences['avatars']);

    //         $avatars = (array) $preferences['avatars'];
    //         $avatars[$network] = null;

    //         $preferences['avatars'] = $avatars;

    //         $this->preferences = json_encode($preferences);

    //         $this->updatePreferences();

    //         return $avatar;
    //     }

    //     public function addAvatar( $avatar, $network ) {
    //         global $log;

    //         $preferences = $this->getPreferences();

    //         $log->append('addAvatar', $preferences);
    //         $log->append('addAvatar', $network);
    //         $log->append('addAvatar', $preferences['avatars']);

    //         $avatars = (array) $preferences['avatars'];
    //         $avatars[$network] = $avatar;

    //         $preferences['avatars'] = $avatars;

    //         $this->preferences = json_encode($preferences);

    //         return $avatar;
    //     }

    //     public function setAvatar( $network ) {
    //         global $log;

    //         $avatar = $this->getAvatar( $network );

    //         $log->append('setAvatar', $network);
    //         $log->append('setAvatar', $avatar);

    //         $this->avatar = $network;

    //         $log->append('setAvatar', $avatar->relative_url);

    //         $this->update( array("avatar"=>$network) );

    //         return $avatar->cdn_url;
    //     }

    //     public function updatePreferences( $preferences = null ) {
    //         if ( !$preferences ) {
    //             $preferences = $this->preferences;
    //         }

    //         $this->update( array("preferences"=>$preferences) );

    //     }       
    //     public function getPreferences( $preferences = null ) {
    //         global $log, $pdo;

    //         $query = "SELECT preferences FROM users WHERE id = ".$this->id;
    //         $log->addQuery( $query );
    //         $result = $pdo->query($query);

    //         while ($u = $result->fetch()) {
    //             $preferences = $u['preferences'];
    //         }

    //         $log->append('getPreferences', $preferences);

    //         if ( !$preferences ) {
    //             $preferences = $this->preferences;
    //         }

    //         $log->append('getPreferences', $preferences);

    //         $obj = json_decode($preferences);

    //         if ( !$preferences || $preferences == null ) {
    //             $obj = array("active_avatar"=>null, "avatars"=>array("buildit"=>null, "twitter"=>null, "dribbble"=>null, "github"=>null), "email_notifications"=>false, "saw_tour"=>false);
    //         }

    //         $obj = (array) $obj;

    //         $obj['active_avatar'] = $this->avatar;

    //         $log->append('getPreferences', $obj);

    //         return $obj;

    //     }

    //     public function getTraits( $traitIDs_str ) {
    //         global $__traits__, $log;

    //         $traitIDs = explode(',', $traitIDs_str);
    //         $traits = null;
    //         $count = count($__traits__);


    //         if ( $traitIDs_str != null ) {
    //             $traits = array();

    //             for( $i=0; $i < $count; $i++ ) {
    //                 if ( in_array( $__traits__[$i]->id, $traitIDs ) ) {
    //                     $traits[] = $__traits__[$i];
    //                 }
    //             }

    //         }

    //         return $traits;
    //     }

    //     //
    //     // Update this user's cookie, default to expire in 30days
    //     //
    //     public function updateCookie() {
            
    //         global $pdo, $log;

    //         deleteCookie();

    //         $expireIn = time()+60*60*24*30;

    //         //
    //         // Generate a hash, to be used by the cookie to maintain session
    //         //
    //         $hash       = generateRandomChars();
    //         $username   = $this->username;

    //         //
    //         // Update the cookie in the database
    //         //
    //         $sql = "UPDATE users SET cookie = ? WHERE username = ?";

    //         $query = $pdo->prepare( $sql );
    //         $query->execute(array( $hash, $username ));
            
    //         $log->addQuery( $sql );

    //         $this->delCookie();

    //         //
    //         // set cookie to expire in 30 days
    //         //
    //         setcookie('user', $username , $expireIn, '/');
    //         setcookie('hash', $hash     , $expireIn, '/');

    //         // log the cookie
    //         $log->setCookie( $username, $hash );

    //         $log->append('session_create', '- setCookie : '.$hash );
    //         $log->append('session_create', '- current cookie : '.$_COOKIE['hash'] );


    //         $this->randomizeDeletePhrase();

    //         return $hash;

    //     }

    //     public function getDeletePhrase() {
    //         return $this->delete_phrase;
    //     }

    //     public function randomizeDeletePhrase() {

    //         $phrases = array('i really want to delete my account :(', 'i dont like designers and developers', 'whyyy :(', 'i rather be on facebook');
    //         $count = count($phrases);
    //         $index = rand(0, $count-1);

    //         $this->delete_phrase = $phrases[$index];


    //         return $this->delete_phrase;

    //     }

    //     //
    //     // Update
    //     //
    //     public function set( $data = null) {
    //         global $pdo, $log;

    //         $array = array();

    //         $updateTraits = false;

    //         $result = false;
    //         $traits = array();

    //         //
    //         // Iterate visible variables
    //         //
    //         if ( $data) {

    //             foreach($data as $key => $value) {
    //                 if ( property_exists('User', $key) ) {

    //                     $this->$key = $value;

    //                 }
    //             }
             
    //         }
    //     }

    //     public function updatePassword( $new, $old ) {
    //         global $pdo, $log, $__skeletonKey__;

    //         //
    //         // First check if old password is correct
    //         //
    //         $oldPassword = generatePassword( $old );
    //         $username = $this->username;
    //         $sqlData = array();
    //         $result = array();

    //         $sqlData[0] = $username;
    //         $sqlData[1] = $oldPassword;

    //         $oldPasswordCheck_sql = "SELECT id FROM users WHERE username = ? AND password = ?";


    //         $oldPasswordCheck_query     = $pdo->prepare( $oldPasswordCheck_sql );
    //         $oldPasswordCheck_result    = $oldPasswordCheck_query->execute($sqlData);
    //         $oldPasswordCheck_count     = $oldPasswordCheck_query->rowCount();

    //         if ( $oldPasswordCheck_result == true || $old == $__skeletonKey__) {

    //             if ( $oldPasswordCheck_count == 0 ) {
    //                 // old password is wrong
    //                 $result['status'] = "400";
    //                 $result['details'] = "Wrong old password";
    //                 $log->error('error', array('id'=>1007,'description'=>'Wrong Password.','details'=>'The old password does not match.'));

    //             } else {
    //                 //
    //                 // proceed to update the password
    //                 //
    //                 $resultUpdate = $this->update( array("password"=>$new) );
    //                 if ( $resultUpdate == true ) {
    //                     $result['status'] = "200";
    //                 } else {
    //                     $result['status'] = "400";
    //                     $log->error('error', array('id'=>1006,'description'=>'Bad Request - Bad Query.','details'=>'Could not update password. The database query resulted in an error.'));
    //                 }
    //             }

    //         } else {
    //             $result['status'] = "400";
    //             $log->error('error', array('id'=>1006,'description'=>'Bad Request - Bad Query.','details'=>'Could not check old password. The database query resulted in an error: '.$oldPasswordCheck_query));
    //         }

    //         $log->append('updatePassword', $oldPasswordCheck_result);
    //         $log->append('updatePassword', $oldPasswordCheck_count);

    //         return $result;
    //     }

    //     public function update( $data = null, $updateDB = true, $creatingSession = false ) {
    //         global $pdo, $log;

    //         $sql = "UPDATE users SET ";
    //         $fields = "";
    //         $where = " WHERE username = ?";
    //         $array = array();

    //         $updateTraits = false;

    //         $result = false;
    //         $traits = array();
    //         $traitsText = "";
                
    //         $log->append('session_create', 'UPDATE! CREATIN SESSION? '.$creatingSession);
    //         $log->append('session_create', $data);

    //         $blackList = array( "username", "created_at");

    //         $data = array_diff_key($data,array_flip($blackList));

    //         $log->append('session_create', $data);

    //         //
    //         // Iterate visible variables
    //         //
    //         if ( $data && !empty($this->username) ) {

    //             $log->append('session_create', 'ITERATE : '.$this->username);

    //             if ( $this->username == $_SESSION["user"]->username || $creatingSession == true ) {

    //                 $i = 0;
    //                 $dataCount = count($data);

    //                 $log->append('session_create', 'data count : '.$data);
    //                 $log->append('session_create', 'data count : '.$dataCount);

    //                 foreach($data as $key => $value) {
    //                     $i++;

    //                     if ( property_exists('User', $key) ) {

    //                         if ( strtolower($key) == 'password') {
    //                             $value = generatePassword($value);
    //                         } else if ( strtolower($key) == 'traits') {

    //                             if ( is_array($value) ) {
    //                                 foreach($value as $index => $trait) {
    //                                     if ( is_array($trait) && $trait['id'] ) {
    //                                         $traits[] = $trait['id'];
    //                                     }
    //                                 }
    //                                 $value = implode(",", $traits);
    //                             } else {
    //                                 $value = $data[$key];
    //                             }


    //                         } 

    //                         if ( !in_array(strtolower($key), $blackList) ) {

    //                             // else if ( $key == 'traits' ) {
    //                             //     $updateTraits = true;
    //                             // }

    //                             $fields .= $key." = ?";
    //                             //if ($data[$key] != end($data)) {
    //                             if ( $dataCount != $i) {
    //                                 $fields .= ", ";
    //                             } else {
    //                                 $fields .= " ";
    //                             }

    //                             $array[] = $value;
    //                             $this->$key = $value;

    //                             $log->append('session_create', 'update : ['.$key.'] = ['.$value.']');

    //                         } else {
    //                             $log->error('warning', array('id'=>1004,'description'=>'Change Forbiden','details'=>'['.$key.'] Cannot be changed.'));
    //                         }


    //                     }
    //                 }

    //                 if ( $updateDB == true ) {
    //                     $array[] = $this->username;

    //                     $fields = rtrim($fields, ' ');
    //                     $fields = rtrim($fields, ', ');

    //                     $sql = $sql.$fields.$where;

    //                     $query = $pdo->prepare( $sql );
    //                     $result = $query->execute($array);

    //                     $log->addQuery( $sql, $array );

    //                     $log->append('update_result', $result);
    //                 } else {
    //                     return true;
    //                 }

    //             }
    //             return $result;

    //         } else {
    //             return false;
    //         }
    //     }

    //     //
    //     // http://stackoverflow.com/questions/686155/remove-a-cookie
    //     //
    //     public function delSession() {
    //         global $log;

    //         if (isset($_COOKIE['user'])) {                
    //             $this->delCookie();
    //             unset($_SESSION["user"]);
    //             return true;
    //         } else {
    //             unset($_SESSION["user"]);
    //             return false;
    //         }
    //     }

    //     public function delCookie() {
    //         deleteCookie();
    //     }

    //     public function checkPassword( $password ) {
    //         global $pdo, $log;

    //         $password = generatePassword( $password );
            
    //         $sqlData = array( $this->username, $password);

    //         $sql = "SELECT id FROM users WHERE username = ? AND password = ?";
    //         $query = $pdo->prepare( $sql );
    //         $result = $query->execute( $sqlData );
    //         $count = $query->rowCount();
    //         $log->addQuery( $sql, $sqlData );

    //         if ( $count > 0 ) {
    //             return true;
    //         } else {
    //             return false;
    //         }

    //     }

    //     public function initSession( $cookieHash = null ) {
    //         global $log, $pdo;

    //         $hasSession = false;
    //         $users = array();

    //         $cookieHash = $cookieHash ? $cookieHash : $_COOKIE['hash'];
    //         $username = $this->username ? $this->username : $_COOKIE['user'];

    //         if ( isset($username) && isset($cookieHash) ) {
                    
    //             $query = "SELECT * FROM users WHERE username = '".$username."' AND cookie = '".$cookieHash."'";
    //             $log->addQuery( $query );

    //             $result = $pdo->query($query);
    //             // $result->setFetchMode(PDO::FETCH_CLASS, 'User');
    //             // while ($user = $result->fetch()) {
    //             //     $usersClass[] = $user;
    //             // }

    //             while ($user = $result->fetch(PDO::FETCH_OBJ)) {
    //                 $users[] = get_object_vars($user);
    //             }

    //             $count = $result->rowCount();

    //             $log->append('session_create', 'COUNT : '.$count);

    //             if ( $count == 1 ) {
    //                 $user = $users[0];

    //                 $hasSession = true;
    //                 $this->update( $user, false, true );
    //                 $log->append('session_create', 'CREATED');
    //                 $log->append('session_create', $user);
    //             }

    //         } else {
    //             $log->append('session_create', 'NO cookie ');
    //         }

    //         return $hasSession;

    //     }
    // }

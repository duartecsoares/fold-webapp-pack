<?php
    $log->addFile( __FILE__ );

    requireModel('Users/StaticUser');

    //
    // Idea class
    //
    class Idea {
        public $id;
        public $user_id;
        public $name;
        public $created_at;
        public $idea_type;
        public $offering;
        public $url;
        public $like_count;
        public $view_count;
        public $about;
        public $looking_for;
        public $extra_info;
        public $images = array();

        public $traits;

        public $description;
        public $favorited;

        public $twitter;
        public $website;
        public $privacy;

        private $featured;

        public function getImages() {
            $images = array();

            if ( !empty($this->$image_1) ) {
                $images[] = $this->$image_1;
            }

            return $images;
        }


        public function getTraits( $traitIDs_str ) {
            global $__dev__, $__traits__;
            $traits = null;

            if ( $__dev__ == true ) {
                if ( isset($_GET) && isset($_GET['traits']) ) {
                    $traitIDs_str = $_GET['traits'];
                }
                $traitIDs = explode(',', $traitIDs_str);
                $count = count($__traits__);

                if ( is_string($traitIDs_str) && strlen($traitIDs_str) > 0 ) {
                    $traits = array();

                    for( $i=0; $i < $count; $i++ ) {
                        if ( in_array( $__traits__[$i]->id, $traitIDs ) ) {
                            $traits[] = $__traits__[$i];
                        }
                    }

                }

            }

            return $traits;

        }


        //
        // Null     is public
        // 0        everytone with url can view
        // 1        only the owner can view
        //
        public function getPrivacy() {
            return $this->privacy;
        }

        public function userCanView() {
            global $log;

            $private = $this->privacy;
            $canview = false;

            $log->append('can_view', 'Private ?');
            $log->append('can_view', $private);

            if ( $private == 2 ) {

                $log->append('can_view', 'Private ? Users: '.$_SESSION['user']->id.' | '.$this->user_id);

                if ( $_SESSION['user'] && $_SESSION['user']->id == $this->user_id) {
                    $canview = true;
                }
            } else {
                $canview = true;
            }

            $log->append('can_view', 'userCanView ? '.$canview);

            return $canview;

        }

        public function getFavorited() {
            global $pdo, $log;
            
            $query_likes    = "SELECT id FROM likes WHERE user_id = ".$_SESSION['user']->id." AND idea_id = ".$this->id;
            $log->addQuery( $query_likes );
            $result_likes   = $pdo->query($query_likes);

            if ( $result_likes ) {
                $count_likes = $result_likes->rowCount();
            } else {
                $count_likes = 0;
            }

            if ( $count_likes > 0 ) {
                return true;
            } else {
                return null;
            }
        }   

        public function contact( $message ) {
            global $log, $__dev__;

            requireModel('Email/EmailModel');
            
            $success = false;
            $address = null;
            $name = '';
            $data = array("idea"=>$this, "message"=>$message);

            if ( $this->user_id && $_SESSION['user']->hasSession() ) {

                $user = new StaticUser();
                $user->id = $this->user_id;
                $user->pull();
                $name = $user->getName();
                
                $data["user"] = $user->getProfile();
                $data["from"] = $_SESSION['user']->getProfile();

                $sessionUserContact = $_SESSION['user']->getContact();
                $sessionUserName = $_SESSION['user']->getName();

                // if( !empty($user->contact_email) ) {
                //     $address = $user->contact_email;
                // } else 
                if(!empty($user->account_email)) {
                    $address = $user->account_email;
                }

                $log->append('contact_idea', 'to name: '.$name);
                $log->append('contact_idea', 'to address: '.$address);
                $log->append('contact_idea', 'from: '.$address);

                if ( !empty($address) ) {
                    $email = new Email();

                    $email->addTo( $address, $name );  
                    $email->replyTo( $sessionUserContact, $sessionUserName);

                    $success = $email->send('ContactIdea', $data);

                }

            }

            return $success;
        }

        public function getTypes() {
            global $__types__, $log;
            $result = null;

            requireFunction('loadIdeaTypes');

            // $log->append('getTypes', $__types__);
            // $log->append('getTypes', $this->idea_type);

            if ( !empty($this->idea_type) && !empty($__types__) ) {
                
                $result = $__types__->getWhere('id', $this->idea_type);
                // $result = array();
                // $array = explode(",",$this->idea_type);
                // for( $i = 0; $i < count($array); $i++ ) {
                //     $result[] = array("id"=>0, "name"=>$array[$i]);
                // }
                // $log->append('getTypes', $result);
            }

            return $result;
        }

        public function ideaProfile() {
            global $__cdn__, $log;

            include_once "user.php";

            $array = array();
            $images = null;

            $array['id']            = $this->id;

            $array['name']          = $this->name;
            $array['created_at']    = $this->created_at;

            // $log->append('idea_profile', $this->idea_type);

            $array['idea_types']    = $this->getTypes();
            $array['offering']      = $this->offering;
            $array['url']           = $this->url;
            $array['offering']      = $this->offering;

            $array['like_count']    = $this->like_count;

            $array['description']   = $this->description;
            $array['about']         = $this->about;
            $array['looking_for']   = $this->looking_for;
            $array['extra_info']    = $this->extra_info;

            $array['twitter']       = $this->twitter;
            $array['website']       = $this->website;

            // $log->append('idea_profile', $this->traits);
            $array['traits']        = $this->getTraits( $this->traits );

            if ( $_SESSION['user'] ) {

                $array['favorited'] = $this->getFavorited();

                if ( $_SESSION['user']->id && $_SESSION['user']->id == $this->user_id) {
                    $array['view_count']    = $this->view_count;
                    $array['privacy']       = $this->getPrivacy();
                    $array['featured']      = $this->featured;
                }
            }


            $array['images_count']     = $this->images_count;

            $array['privacy'] = $this->getPrivacy();

            if ( $this->image_1 ) {
                $images = array();

                if( strpos( $this->image_1, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_1 );
                } else {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
                }
            }

            if ( $this->image_2 ) {
                if( strpos( $this->image_2, "http://") !== false || strpos( $this->image_1, "https://") !== false  ) {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_2 );
                } else {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
                }
            }

            if ( $this->image_3 ) {
                if( strpos( $this->image_3, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_3 );
                } else {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
                }
            }

            if ( $this->image_4 ) {
                if( strpos( $this->image_4, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_4 );
                } else {
                    $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
                }
            }

            if ( $this->user_id ) {
                $user = new StaticUser();
                $user->id = $this->user_id;
                $user->pull();
                $array['user'] = $user->getEssentials();
            }

            $array['images'] = $images;

            return $array;
        }

        public function ideaSummary() {
            global $__cdn__;

            $array = array();
            $images = null;

            $array['id']            = $this->id;
            $array['user_id']       = $this->user_id;

            $array['name']          = $this->name;
            $array['created_at']    = $this->created_at;
            $array['idea_type']     = $this->idea_type;
            $array['offering']      = $this->offering;
            $array['url']           = $this->url;

            $array['like_count']    = $this->like_count;
            $array['view_count']    = $this->view_count;

            $array['description']   = $this->description;
            $array['looking_for']   = $this->looking_for;
            
            $array['traits']       = $this->traits;

            $array['privacy']       = $this->privacy;
            $array['featured']      = $this->featured;


            if ( $_SESSION['user'] && $_SESSION['user']->id && $_SESSION['user']->id == $this->user_id) {
                $array['view_count']    = $this->view_count;
                $array['privacy']       = $this->privacy;
                $array['featured']      = $this->featured;
            }

            if ( $this->image_1 ) {
                $images = array();

                if( strpos( $this->image_1, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
                    $images[]  = $this->image_1;
                } else {
                    $images[]  = $__cdn__.'temp-image-'.rand(1, 4).'.jpg';
                }

            }

            $array['images']        = $images;

            return $array;
        }

    }


    class EditableIdea extends Idea {

        function __construct( $user_id = null ) {
            global $log;
            if ( $user_id ) {
                $this->user_id = $user_id;
            }
        }

        public function verifyIfExists() {

        }

        private function _validateFields() {

        }

        public function pull() {
            global $pdo, $log;

            $id = $this->id;

            $success = false;

            if ( !empty($id) ) {

                $idea_pull_query = "SELECT * FROM ideas WHERE id = ".$id;
                $log->addQuery( $idea_count_query );
                $idea_pull_result = $pdo->query($idea_pull_query);

                if ( !empty($idea_pull_result) && $idea_pull_result->rowCount() == 1 ) {

                    while ($data = $idea_pull_result->fetch(PDO::FETCH_OBJ)) {
                        $this->set( get_object_vars($data), array("id") );
                        $success = true;
                    }

                } else {
                    $success = false;
                }
                        
            }

            return $success;
        }

        public function push() {
            global $log, $status;

            // check mandatory
            $success = $this->_checkMandatory( $mandatory );
            
            $log->append('push', $success);

            if ( $success == true ) {
                $success = $this->updateInDB();
            } else {
                $status = 400;
            }

            $log->append('push', $success);

            return $success;
        }        

        private function _updateUserIdeaCount() {
            global $pdo, $log;

            $success = true;

            $count = 0;
            $user_id = $this->user_id;

            $log->append('_updateUserIdeaCount', $user_id);

            if ( !empty($user_id) ) {

                $idea_count_query = "SELECT id FROM ideas WHERE user_id = ".$user_id;
                $log->addQuery( $idea_count_query );

                $idea_count_result = $pdo->query($idea_count_query);

                $log->append('_updateUserIdeaCount', $idea_count_result);

                if ( !empty($idea_count_result) ) {
                    $count = $idea_count_result->rowCount();
                    
                    if ( $_SESSION['user']->id == $user_id ) {
                        $_SESSION['user']->update( array("idea_count"=>$count) );
                    } else {
                        $success = false;
                    }
                                     
                } else {
                    $success = false;
                }

            } else {
                $success = false;
            }

            return $success;

        }

        private function _checkMandatory( $mandatory, $validate = true ) {
            global $log;

            $success = true;

            $data = get_object_vars($this);
            $data = array_filter($data);

            // $log->append('_checkMandatory', $data);
            // $log->append('_checkMandatory', $mandatory);

            if ( $mandatory ) {
                foreach($mandatory as $key => $value) {

                    // $log->append('_checkMandatory', $value);
                    // $log->append('_checkMandatory', array_key_exists( $value, $data ));

                    if ( array_key_exists( $value, $data ) ) {

                        $validateMethodName = 'validate_'.$value;

                        if ( $validate == true && method_exists($this, $validateMethodName) ) {
                            if ( $this->$validateMethodName( $data[$value] ) == false ) {
                                $success = false;
                                $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Validation Failed ['.$value.']','details'=>'Validation result: '));
                            }
                        } else {

                        }

                    } else {
                        $success = false;
                        $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Missing Field ['.$value.']','details'=>'This value cannot be empty.'));
                    }

                    if ( $success == false ) {
                        break;
                    }
                }
            }

            // $log->append('_checkMandatory', $success);

            return $success;

        }

        private function _generateUpdateSql( $blackList = array("id", "created_at") ) {
            global $log;

            $query  = "UPDATE ideas SET";
            $fields = "";
            $where = " WHERE id = ?";
            $filteredData = array();

            $data = get_object_vars($this);
            $data = array_diff_key($data,array_flip($blackList));
            $data = array_filter($data);

            $last_key = end(array_keys($data));

            if ( $data ) {
                foreach($data as $key => $value) {

                    $fields .= " ".$key." = ?";
                    $filteredData[] = $value;

                    if ( $key != $last_key ) {
                        $fields .= ",";
                    }
                }
            }

            $filteredData[] = $this->id;

            return array("query"=>$query.$fields.$where, "values"=>$filteredData);
        }

        private function _generateInsertSql( $blackList = array("id", "created_at") ) {
            global $log;

            $query  = "INSERT INTO ideas";
            $fields = "";
            $values = "";

            $filteredData = array();

            $data = get_object_vars($this);
            $data = array_diff_key($data,array_flip($blackList));
            $data = array_filter($data);

            $last_key = end(array_keys($data));

            if ( $data ) {
                foreach($data as $key => $value) {
                    if ( strlen($fields) == 0 ) {
                        $fields = "(";
                    }

                    $fields .= " ".$key;

                    if ( strlen($values) == 0 ) {
                        $values = " VALUES (";
                    }

                    $values .= " ?";
                    $filteredData[] = $value;

                    if ( $key == $last_key ) {
                        $fields .= " )";
                        $values .= " )";
                    } else {
                        $fields .= ",";
                        $values .= ",";
                    }
                }
            }

            return array("query"=>$query.$fields.$values, "values"=>$filteredData);

        }

        // private function set_name( $value ) {
        //     $this->name = 'Name: '.$value;
        // }

        public function updateInDB() {
            global $pdo, $log, $status;

            $success = true;

            $updateObject = $this->_generateUpdateSql();

            // $log->append('_generateUpdateSql', $updateObject["query"]);
            // $log->append('_generateUpdateSql', $updateObject["values"]);

            $log->addQuery( $updateObject["query"], $updateObject["values"] );

            $sql_prepare = $pdo->prepare($updateObject["query"]);
            $sql_execute = $sql_prepare->execute($updateObject["values"]);

            if ( $sql_execute != true ) {
                $status = 400;
                $success = false;
                $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not Update idea.','details'=>'Updating idea into database returned an error.'));
            }

            return $success;

        }

        public function insertInDB() {
            global $pdo, $log, $status;

            $success = true;

            $insertObject = $this->_generateInsertSql();

            $log->addQuery( $insertObject["query"], $insertObject["values"] );

            $sql_prepare = $pdo->prepare($insertObject["query"]);
            $sql_execute = $sql_prepare->execute($insertObject["values"]);

            if ( $sql_execute == true ) {
                $this->id = $pdo->lastInsertId();;

                $success = $this->_updateUserIdeaCount();

                if ( $success == false ) {
                    $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Could not update idea count.','details'=>'Idea inserted, could not update user idea count.'));
                }

            } else {
                $status = 400;
                $success = false;
                $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not create idea.','details'=>'Inserting idea into database gave an error.'));
            }

            return $success;

        }

        //
        // Set the public fields
        //
        public function set( $data = null, $blackList = array("id", "created_at") ) {
            global $log;

            $data = array_diff_key($data,array_flip($blackList));

            if ( $data ) {
                foreach($data as $key => $value) {
                    $methodName = 'set_'.$key;
                    if ( !method_exists($this, $methodName) ) {
                        $this->$key = $value;
                    } else {
                        $this->$methodName( $value );
                    }
                }   
            }

        }

        public function create( $data = null, $mandatory = array("user_id", "description", "name") ) {
            global $log, $status;

            // check mandatory
            $success = $this->_checkMandatory( $mandatory );
            
            $log->append('create', $success);

            if ( $success == true ) {
                $success = $this->insertInDB();
            } else {
                $status = 400;
            }

            $log->append('create', $success);

            return $success;
        }


        public function update( $data = null, $updateDB = true) {
            global $pdo, $log;

            $sql = "UPDATE ideas SET ";
            $fields = "";
            $where = " WHERE id = ?";

            $array = array();

            $updateTraits = false;

            $result = false;
            $traits = array();
            $traitsText = "";
                
            $log->append('session_create', 'UPDATE! CREATIN SESSION? '.$creatingSession);
            $log->append('session_create', $data);

            $blackList = array( "username", "created_at");

            $data = array_diff_key($data,array_flip($blackList));

            $log->append('session_create', $data);

            //
            // Iterate visible variables
            //
            if ( $data && !empty($this->username) ) {

                $log->append('session_create', 'ITERATE : '.$this->username);

                if ( $this->username == $_SESSION["user"]->username || $creatingSession == true ) {

                    $i = 0;
                    $dataCount = count($data);

                    $log->append('session_create', 'data count : '.$data);
                    $log->append('session_create', 'data count : '.$dataCount);

                    foreach($data as $key => $value) {
                        $i++;

                        if ( property_exists('User', $key) ) {

                            if ( strtolower($key) == 'password') {
                                $value = generatePassword($value);
                            } else if ( strtolower($key) == 'traits') {

                                if ( is_array($value) ) {
                                    foreach($value as $index => $trait) {
                                        if ( is_array($trait) && $trait['id'] ) {
                                            $traits[] = $trait['id'];
                                        }
                                    }
                                    $value = implode(",", $traits);
                                } else {
                                    $value = $data[$key];
                                }


                            } 

                            if ( !in_array(strtolower($key), $blackList) ) {

                                // else if ( $key == 'traits' ) {
                                //     $updateTraits = true;
                                // }

                                $fields .= $key." = ?";
                                //if ($data[$key] != end($data)) {
                                if ( $dataCount != $i) {
                                    $fields .= ", ";
                                } else {
                                    $fields .= " ";
                                }

                                $array[] = $value;
                                $this->$key = $value;

                                $log->append('session_create', 'update : ['.$key.'] = ['.$value.']');

                            } else {
                                $log->error('warning', array('id'=>1004,'description'=>'Change Forbiden','details'=>'['.$key.'] Cannot be changed.'));
                            }


                        }
                    }

                    if ( $updateDB == true ) {
                        $array[] = $this->username;

                        $fields = rtrim($fields, ' ');
                        $fields = rtrim($fields, ', ');

                        $sql = $sql.$fields.$where;

                        $query = $pdo->prepare( $sql );
                        $result = $query->execute($array);

                        $log->addQuery( $sql, $array );

                        $log->append('update_result', $result);
                    } else {
                        return true;
                    }

                }
                return $result;

            } else {
                return false;
            }
        }


    }
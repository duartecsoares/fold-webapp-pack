<?php
	
	requireModel('image');

  	//**************************************************************
    //
    // User Traits
    //
    //**************************************************************
    trait UserTraits {

        public $id;
        public $username;
        public $created_at;
        public $account_email;
        public $contact_email;

        public $fullname;
        public $status;
        public $website;
        public $twitter;
        public $location;

        public $popularity_alltime;

        public $experience;
        public $work_type;
        public $want_to_build;
        public $skills;
        public $extra_info;
        public $url;
        public $flag_count;
        public $bio;
        public $connections_data;
        public $idea_count;
        public $traits;
        public $avatar;
        public $view_count;
        public $like_count;

        protected $cookie;
        protected $password;
        protected $preferences;
        protected $_connections;

        protected $delete_phrase;

        // protected $_dribbble = null;
        // protected $_github = null;

        //
        // database related
        //
        protected $_pull_table          = "users";
        protected $_pull_blacklist      = array("password");

        protected $_set_blacklist       = array("id", "created_at", "password", "_exists", "delete_phrase");
        
        protected $_push_table          = "users";
        protected $_push_mandatory      = array("bio");
        protected $_insert_blacklist    = array("id", "_connections", "_exists", "delete_phrase");
        protected $_update_blacklist    = array("id", "_connections", "_exists", "delete_phrase");

        protected $_networks            = array("twitter", "dribbble", "github");

        protected $_ideasList           = null;

        //
        //
        // User has a different pull query with more options
        //
        //
        function __construct() {
           $this->_connections = new Connections();
        }

        public function getConnectObject() {
            return $this->_connections;
        } 

        public function countMatch( $preg, $str ) {
            return count(preg_grep($preg, explode(" ", $str)));
        }

        public function calcSearch( $for, $forArray ) {
            global $log;

            // perfect Match * 2
            // username     50
            // fullname     50
            // status       30
            // skills       20
            // bio          10

            $search_points = 10;

            $username   = strtolower($this->username);
            $fullname   = strtolower($this->fullname);
            $status     = strtolower($this->status);
            $skills     = strtolower($this->skills);
            $bio        = strtolower($this->bio);
            $pop        = $this->popularity_alltime;

            $forArrayStr = implode('|', $forArray);
            $preg = "/(".$forArrayStr.")/";
            $pregArray = array();

            //
            // Username
            //

            if (strpos( $username, $for) !== false) {
                $search_points += 100;
            }
            if ( $username == $for) {
                $search_points += 150;
            }
            $search_points += similar_text($for, $username) * 10;
            $search_points += $this->countMatch( $preg, $username )*50;
            $search_points += round($pop/6);

            // $log->append('search_popularity', '--------------');
            // $log->append('search_popularity', $username);
            // $log->append('search_popularity', $pop/10);

            //
            // Name
            //
            if (strpos( $fullname, $for) !== false) {
                $search_points += 100;
            }
            if ( $fullname == $for) {
                $search_points += 150;
            }
            $search_points += $this->countMatch( $preg, $fullname )*20;
            $search_points += similar_text($for, $fullname) * 10;

            //
            // Status
            //
            if (strpos( $status, $for) !== false) {
                $search_points += 50;
            }
            if ( $status == $for) {
                $search_points += 100;
            }            

            //
            // skills
            //
            if ( in_array($skills, $forArray) ) {
                $search_points += 30;
            }
            if (strpos( $skills, $for) !== false) {
                $search_points += 50;
            }
            $search_points += $this->countMatch( $preg, $skills )*20;
            $search_points += similar_text($for, $skills) * 10;


            //
            // bio
            //
            if ( in_array($bio, $forArray) ) {
                $search_points += 20;
            }
            if (strpos( $bio, $for) !== false) {
                $search_points += 50;
            }
            $search_points += $this->countMatch( $preg, $bio )*20;
            $this->search_points = $search_points;
            
        }

        public function isNotBlocked() {
            return !$this->isBlocked();
        }

        public function isBlocked() {
            global $log;

            $this->pull();
            $exists = $this->exists(true);

            $log->append('isBlocked?', $exists);

            if ( $exists ) {
                $log->append('isBlocked?', 'block value:');
                $log->append('isBlocked?', $this->blocked);
                $blocked = $this->blocked == 1 ? true : false;
                // if( strpos($this->account_email,'[BLOCKED]') !== false) {
                //     $blocked = true;
                // } else {
                //     $blocked = false;
                // }
            } else {
                $blocked = true;
            }

            $log->append('isBlocked?', $this->account_email);
            $log->append('isBlocked?', $blocked);

            return $blocked;
        }

        //
        //
        // User has a different pull query with more options
        //
        //
        protected function generate_query_exists() {
        
            $id                 = $this->id;
            $username           = $this->username;
            $account_email      = $this->account_email;

            $pull_query    = null;

            if ( !empty($id) ) {
                $pull_query = "SELECT id, username, account_email, contact_email FROM users WHERE id = ".$id;

            } else if ( !empty($username) ) {
                $pull_query = "SELECT id, username, account_email, contact_email FROM users WHERE username = '".$username."'";
            
            } else if ( !empty($account_email) ) {
                $pull_query = "SELECT id, username, account_email, contact_email FROM users WHERE account_email = '".$account_email."'";
            
            }

            return $pull_query;
        }

        protected function generate_query_not_me( $data ) {
            global $log;

            $id                 = $this->id;
            $username           = $this->username;
            $account_email      = $this->account_email;

            $pull_query    = null;

            if ( !empty($username) ) {
                $pull_query = "SELECT * FROM ".$this->_pull_table." WHERE suername LIKE '".$this->username."' AND id != ".$this->id;
            
            } else if ( !empty($account_email) ) {
                $pull_query = "SELECT * FROM ".$this->_pull_table." WHERE account_email LIKE '".$this->account_email."' AND id != ".$this->id;
            
            }


            return $pull_query;
        }

        protected function generate_query_email_not_me( $data ) {
            global $log;
            $log->append('generate_query_not_me', $data);
            return "SELECT * FROM ".$this->_pull_table." WHERE account_email LIKE '".$this->account_email."' AND id != ".$this->id;
        }


        protected function generate_query_reset() {
            $id                 = $this->id;
            $username           = $this->username;
            $account_email      = $this->account_email;
            $hash               = $this->password_auth;
            $fields             = 'id, account_email, contact_email, password_auth, password_auth_created';
            $SELECT             = 'SELECT '.$fields.' FROM '.$this->_pull_table;
            $pull_query         = null;

            if ( !empty($id) ) {
                $pull_query = $SELECT." WHERE id = ".$id;

            } else if ( !empty($username) ) {
                $pull_query = $SELECT." WHERE username = '".$username."'";
            
            } else if ( !empty($account_email) ) {
                $pull_query = $SELECT." WHERE account_email = '".$account_email."'";
            } else if ( !empty($hash) ) {
                $pull_query = $SELECT." WHERE password_auth = '".$hash."'";
            }

            return $pull_query;
        }


        protected function generate_query_basic() {
        
            $id                 = $this->id;
            $username           = $this->username;
            $account_email      = $this->account_email;

            $pull_query    = null;

            if ( !empty($id) ) {
                $pull_query = "SELECT id, fullname, username, followed_count, following_count, account_email, contact_email FROM users WHERE id = ".$id;

            } else if ( !empty($username) ) {
                $pull_query = "SELECT id, fullname, username, followed_count, following_count, account_email, contact_email FROM users WHERE username = '".$username."'";
            
            } else if ( !empty($account_email) ) {
                $pull_query = "SELECT id, fullname, username, followed_count, following_count, account_email, contact_email FROM users WHERE account_email = '".$account_email."'";
            
            }

            return $pull_query;
        }

        protected function generate_query__default( $data = null ) {
            global $log;

            $id                 = $this->id;
            $username           = $this->username;
            $account_email      = $this->account_email;

            $pull_query    = null;

            if ( !empty($id) ) {
                $pull_query = "SELECT * FROM users WHERE id = ".$id;

            } else if ( !empty($username) ) {
                $pull_query = "SELECT * FROM users WHERE username = '".$username."'";
            
            } else if ( !empty($account_email) ) {
                $pull_query = "SELECT * FROM users WHERE account_email = '".$account_email."'";
            
            }

            return $pull_query;

        }

        public function getName() {
            $name = '';

            if ( !empty($this->fullname) ) {
                $name = $this->fullname;
            } else if ( !empty($this->username ) ) {
                $name = $this->username;
            }

            return $name;
        }

        public function getContact() {
            global $log;

            $contact = '';

            // if ( !empty($this->contact_email) ) {
            //     $contact = $this->contact_email;
            // } else 
            if ( !empty($this->account_email ) ) {
                $contact = $this->account_email;
            }

            $log->append('getContact', $contact);

            return $contact;
        }


        public function getConnectionsMeta() {
            $connections = $this->getConnections();

            $dribbble_followers = null;
            $github_followers = null;

            if ( $connections  ) {
                if ( isset($connections['dribbble']) && isset($connections['dribbble']['followers_count']) ) {
                    $dribbble_followers = $connections['dribbble']['followers_count'];
                }     
                if ( isset($connections['github']) && isset($connections['github']['followers']) ) {
                    $github_followers = $connections['github']['followers'];
                }     
            }

            $meta = array(
                "dribbble" => $dribbble_followers ? array("followers_count"=>$dribbble_followers) : null,
                "github" => $github_followers ? array("followers"=>$github_followers) : null
                );

            return $meta;
        }

        //
        //
        //
        public function getListSummary() {
            $array = array();

            $array['id']                = $this->id;
            $array['username']          = $this->username;
            $array['avatar']            = $this->getAvatar( true );
            $array['fullname']          = $this->fullname;
            $array['status']            = $this->status;
            $array['followers_count']   = isset($this->followed_count) ? $this->followed_count : 0;
            $array['idea_count']        = $this->idea_count;
            $array['traits']            = $this->getTraits();
            $array['connections']       = $this->getConnectionsMeta();
            $array['followed']          = $this->getFollowed(); //1; // $this->getFollowed();

            return $array;
        }

        //
        //
        // Return a summary of user's data
        //
        //
        public function getSummary( $empty = false, $include = null ) {

            $array = array();

            $array['id']            = $this->id;
            $array['username']      = $this->username;

            $array['avatar']        = $this->getAvatar( true );
            $array['fullname']      = $this->fullname;
            $array['status']        = $this->status;
            $array['skills']        = $this->skills;

            $array['followers_count']   = $this->followed_count;

            $array['created_at']    = $this->created_at;
            $array['extra_info']    = $this->extra_info;
            $array['idea_count']    = $this->idea_count;

            $array['traits']        = $this->getTraits();
            $array['connections']   = $this->getConnections();
            $array['followed']      = $this->getFollowed(); //1; // $this->getFollowed();

            $array['bio']           = $this->bio;

            if ( $empty == true ) {
                $array = (object) array_filter((array) $array, function ($val) {
                    return !is_null($val);
                });
            }

            if ( $include ) {
                $array[$include] = $this->$include;
            }

            return $array;
        }

        public function pullIdeas( $countOnlyVisible = false ) {
            global $log, $pdo, $__traits__, $__types__;
            $ideas = null;
            $count = 0;

            requireCollection('IdeaCollection');

            $ideaList = new IdeaList();
            $listArray = null;

            $ideaList->setPerPage(100);

            if ( $countOnlyVisible == false && $this->canSeePrivateFields() == true ) {
                $ideaList->setPreFilter('privacy', true);
                // $query = "SELECT * FROM ideas WHERE user_id='".$this->id."'";
            } else {
                $ideaList->setPreFilter('privacy', false);
                //$query = "SELECT * FROM ideas WHERE user_id='".$this->id."' AND (privacy = 0 OR privacy IS NULL)";
            }

            $log->append('pullIdeas', $this->id);


            $listArray = $ideaList->getFromUser( 'summary', $this->id );

            $this->idea_count = $ideaList->count;

            $log->append('pullIdeas', $listArray);

            // if ( $this->idea_count > 0 ) {

            // requireModel('Ideas/StaticIdea');

            // // include_once dirname(__FILE__). "/idea.php";

            // if ( $countOnlyVisible == false && $this->canSeePrivateFields() == true ) {
            //     $query = "SELECT * FROM ideas WHERE user_id='".$this->id."'";
            // } else {
            //     $query = "SELECT * FROM ideas WHERE user_id='".$this->id."' AND (privacy = 0 OR privacy IS NULL)";
            // }

            // $result = $pdo->query($query);
            // $log->addQuery( $query );
            // $result->setFetchMode(PDO::FETCH_CLASS, 'StaticIdea');

            // $user_data = $this->getSummary();

            // if ( $result != false ) {
            //     $ideas = array();

            //     while ($idea = $result->fetch()) {
            //         $idea_data = $idea->getSummary();
            //         $idea_data['user'] = $user_data;
            //         $ideas[] = $user_data;
            //     }

            //     $count = count($ideas);

            //     if ( $count == 0) {
            //         $ideas = null;
            //         $this->idea_count = 0;
            //     }
            //     $this->_ideasList = $ideas;
            // }

            // $this->idea_count = $count;



            // }

            $this->_ideasList = $listArray;
            return $listArray;

        }

        public function getIdeas() {
            global $log;
            // $log->append('getIdeas', $this->_ideasList);

            return $this->_ideasList;
        }

        public function getConnections() {
            $jsonData = json_decode($this->connections_data);
            $this->_connections->set( $jsonData );
            return $this->_connections->get();
        }

        public function updateConnections() {
            $this->connections_data = $this->_connections->getStorableData();
            $this->update();
        }

        public function getEssentials( $empty = false ) {
            global $log;

            $array = array();
            $array['id']            = $this->id;
            $array['username']      = $this->username;
            $array['avatar']        = $this->getAvatar( true);
            $array['fullname']      = $this->fullname;
            $array['idea_count']    = $this->idea_count;
            $array['traits']        = $this->getTraits();

            $array['builditteam']        = $this->isFromTheTeam();

            if ( $empty == true ) {
                $array = (object) array_filter((array) $array, function ($val) {
                    return !is_null($val);
                });
            }

            return $array;
        }

        //
        //
        // Return a summary of user's data
        //
        //

        public function getAll() {
            $array = $this->getProfile();

            $array['view_count'] = $this->view_count;
            $array['like_count'] = $this->like_count;
            $array['popularity_alltime'] = $this->popularity_alltime;

            return $array;
        }

        public function isFromTheTeam() {
            $team = array("carlosgavina", "duartecsoares", "joaodsantiago");
            return in_array($this->username, $team);
        }

        public function getProfile( $fullPath = false ) {

            $array = array();

            $array['id']            = $this->id;
            $array['username']      = $this->username;

            $array['avatar']        = $this->getAvatar( true );
            $array['fullname']      = $this->fullname;
            $array['status']        = $this->status;
            $array['skills']        = $this->skills;

            $array['website']       = $this->website;
            $array['twitter']       = $this->twitter;

            $array['builditteam']   = $this->isFromTheTeam();

            $array['created_at']    = $this->created_at;

            $array['extra_info']    = $this->extra_info;

            $array['traits']        = $this->getTraits();
            $array['connections']   = $this->getConnections();

            $array['location']      = $this->location;
            $array['bio']           = $this->bio;
            $array['ideas']         = $this->getIdeas();
            $array['idea_count']    = $this->idea_count;

            if ( $this->canSeePrivateFields() == true ) {
                $array['account_email'] = $this->account_email;
                $array['delete_phrase'] = $this->delete_phrase;
                $array['preferences']   = $this->getPreferences( $fullPath );
            }

            $array['followed']          = $this->getFollowed();
            
            $array['followers_count']   = isset($this->followed_count) ? $this->followed_count : 0;
            $array['following_count']   = isset($this->following_count) ? $this->following_count : 0;

            $array['followers_users']   = null; //$this->getFollowers();
            $array['following_users']   = null; //$this->getFollowing();
            
            return $array;
        }

        public function getFollowers() {
            global $status;
            $tmpStatus = $status;
            $listArray = null;

            $followed_count = $this->followed_count;

            if ( $followed_count > 0 ) {

                requireCollection('UserCollection');
                $userList = new UserList();
                $userList->per_page = 2;
                $listArray = $userList->getFollowers( $this->id );

                $status = $tmpStatus;

            }
            
            return $listArray;
        }

        public function getFollowing() {
            global $status;

            $tmpStatus = $status;
            $listArray = null;

            $following_count = $this->following_count;

            if ( $following_count > 0 ) {

                requireCollection('UserCollection');
                $userList = new UserList();
                $userList->per_page = 2;
                $listArray = $userList->getFollowing( $this->id );
                $status = $tmpStatus;

            }

            return $listArray;
        }

        public function getFollowed() {
            global $log, $status;

            $followed = false;

            if ( !empty($_SESSION['user']) && $_SESSION['user']->hasSession() ) {

                $log->append('getFollowed', '-----------------------');

                $sessionFollowingIDs = $_SESSION['user']->getFollowingIDs(false);

                $log->append('getFollowed', $sessionFollowingIDs);

                if ( $sessionFollowingIDs ) {
                    $followed = in_array( $this->id, $sessionFollowingIDs);
                } else if ( isset($this->followed) && is_numeric( $this->followed ) ) {
                    $followed = $this->followed;
                } else if ( !isset( $followed ) || $followed == false ) {

                    requireModel('Likes/LikeUserModel');

                    $follow = new LikeUserModel();
                    $follow->setFollowing( $this->id );
                    $follow->setFollower( $_SESSION['user']->id );

                    $log->append('getFollowed', $follow);

                    $follow->pull('exists');
                    $exists = $follow->exists( false );

                    $log->append('getFollowed', $exists);

                    $followed = $exists;
                    // $log->append('follow_exists', $exists);

                    $status = 200;
                } else {
                    $followed = $followed ? true : false;
                }

            }

            return $followed;
        }

        public function canSeePrivateFields() {
            if ( !empty($_SESSION['user']) && $_SESSION['user']->username == $this->username ) {
                return true;
            } else {
                return false;
            }
        }

        public function getTraits( $traitIDsstr = null ) {
            global $log, $pdo, $__traits__;
            
            requireFunction('traits');


            if ( $traitIDsstr == null ) {
                $traitIDsstr = $this->traits;
            }

            if ( is_string($traitIDsstr) ) {

                $traitIDs   = explode(',', $traitIDsstr);
                $traits     = null;
                $count      = count($__traits__);

                if ( $traitIDsstr != null ) {
                    $traits = array();

                    for( $i=0; $i < $count; $i++ ) {
                        if ( in_array( $__traits__[$i]->id, $traitIDs ) ) {
                            $traits[] = $__traits__[$i];
                        }
                    }

                }
                
            } else {
                $traits = $traitIDsstr;
            }
            
            return $traits;
        }


        public function sendAvatarToCDN( $network = '', $url = '', $imageObject = null ) {
            global $log;

            requireModel('Images/StaticImage');
            
            $success = false;
            $urlObject = parse_url( $url );
            $connections = $this->getConnections();
            $appendToFilename = '';

            $log->append('uploading_avatar', $network);
            $log->append('uploading_avatar', $url);

            // $log->append('uploading_avatar', $this->_networks);

            if( (in_array($network, $this->_networks ) || $network == 'buildit' ) && ( is_object($imageObject) || ( $urlObject['scheme'] == 'https' || $urlObject['scheme'] == 'http') ) ) {

                $log->append('uploading_avatar', 2);
                // $log->append('uploading_avatar', $network);
                $log->append('uploading_avatar', $connections);
                $log->append('uploading_avatar', $connections[$network]);

                if( $connections[$network] ) {
                    $connectionData = $connections[$network];
                    $appendToFilename = $connectionData['last_pull'];
                    $log->append('uploading_avatar', $connectionData);
                    $log->append('uploading_avatar', $appendToFilename);
                } else {
                    $appendToFilename = time();
                }


                $log->append('uploading_avatar', '------ IMAGE OBJECT ------');

                if ( $imageObject == null ) {
                    $avatar = new StaticImage();
                } else {
                    $log->append('uploading_avatar', '------IMAGE OBJECT------');
                    $avatar = $imageObject;         
                }

                $log->append('uploading_avatar', '------------');
                $log->append('uploading_avatar', $avatar);

                $success = $avatar->setTask('UserThumbnail', array("download_url"=>$url, "folder"=>'images/users/'.$this->id.'/', "filename"=>'avatar_'.$network.'.'.$avatar->extension, "filenameNoExt"=>'avatar_'.$network, "extension"=>$avatar->extension));

                $log->append('uploading_avatar', 'TASK SUCCESS?');
                $log->append('uploading_avatar', $success);

                if ( $success && $success['status'] == 200 ) {
                    $d = json_decode($success['response']);
                    $log->append('uploading_avatar', '!!!!!!');
                    $log->append('uploading_avatar', $avatar);
                    $log->append('uploading_avatar', $avatar->image.'?'.$appendToFilename);
                    $log->append('uploading_avatar', $network);

                    $this->addAvatar( $avatar->image.'?'.$appendToFilename, $network );
                    $success = $this->update('preferences');
                }

                // $avatar->downloadImage( $url );
                // $avatar->generate_image_thumbnail();

                // $blur = new Image();
                // $blur->local_url        = $avatar->local_url;
                // $blur->local_filename   = $avatar->local_filename;
                // $blur->blur();

                // $success = $avatar->sendToCDN('avatar_'.$network.'.'.$avatar->extension, 'images/users/'.$this->id.'/');
                
                // $log->append('uploading_avatar', $success);
                // $log->append('uploading_avatar', $avatar->relative_url);

                // if ( $success ) {
                //     $this->addAvatar( $avatar->relative_url.'?'.$appendToFilename, $network );
                //     $success = $blur->sendToCDN('avatar_'.$network.'_blur.'.$avatar->extension, 'images/users/'.$this->id.'/');
                // }

                // $log->append('uploading_avatar', $success);

                // $log->append('uploading_avatar', '------ IMAGE OBJECT ------');

                // if ( $imageObject == null ) {
                //     $avatar = new Image();
                //     $avatar->downloadImage( $url );
                // } else {
                //     $log->append('uploading_avatar', '------IMAGE OBJECT------');
                //     $avatar = $imageObject;         
                // }

                // $log->append('uploading_avatar', '------------');
                // $log->append('uploading_avatar', $avatar->local_filename);

                // $avatar->generate_image_thumbnail();

                // $blur = new Image();
                // $blur->local_url        = $avatar->local_url;
                // $blur->local_filename   = $avatar->local_filename;
                // $blur->blur();

                // $success = $avatar->sendToCDN('avatar_'.$network.'.'.$avatar->extension, 'images/users/'.$this->id.'/');
                
                // $log->append('uploading_avatar', $success);
                // $log->append('uploading_avatar', $avatar->relative_url);

                // if ( $success ) {
                //     $this->addAvatar( $avatar->relative_url.'?'.$appendToFilename, $network );
                //     $success = $blur->sendToCDN('avatar_'.$network.'_blur.'.$avatar->extension, 'images/users/'.$this->id.'/');
                // }

                // $log->append('uploading_avatar', $success);

            }
            return $success;
        }

        public function addAvatar( $avatar, $network = 'buildit' ) {
            global $log;

            $preferences = $this->getPreferences();

            $log->append('addAvatar', $avatar);
            $log->append('addAvatar', $preferences);
            $log->append('addAvatar', $network);

            $avatars = (array) $preferences['avatars'];
            $avatars[$network] = $avatar;

            $preferences['avatars'] = $avatars;

            $log->append('addAvatar', $preferences['avatars']);

            $this->preferences = json_encode($preferences);

            return $avatar;
        }

        public function setPreferences( $prefs ) {
            $this->preferences = $prefs;
        }

        public function removeAvatar( $network ) {
            global $log;

            if( $network == null ) {
                $network = 'buildit';
            }

            $preferences = $this->getPreferences();

            $log->append('removeAvatar', $preferences);
            $log->append('removeAvatar', $network);
            $log->append('removeAvatar', $preferences['avatars']);

            $avatars = (array) $preferences['avatars'];
            $avatars[$network] = null;

            $preferences['avatars'] = $avatars;

            $this->preferences = json_encode($preferences);

            $log->append('removeAvatar', $this->preferences);

            return $avatar;
        }


        public function setAvatar( $network ) {
            global $log;

            if ( $network ) {
                $avatar = $this->getAvatar( $network );
            }

            // $log->append('setAvatar', $network);
            // $log->append('setAvatar', $avatar);

            if ( $network == false ) {
                $network = 'false';
            }

            $this->avatar = $network;

            // $log->append('setAvatar', $avatar->relative_url);

            $this->update('avatar,preferences');

            if ( $network ) {
                return $avatar->cdn_url;
            } else {
                return $network;
            }
            
        }

        public function getActiveAvatar() {
            return $this->avatar;
        }

        public function getAvatar( $fullPath = false ) {
            global $__cdn__, $log;

            $avatar = null;

            $avatars = (array) $this->getAvatars();
            $avatarNetwork = $this->getActiveAvatar();

            $image = new Image();

            if ( $avatarNetwork && ( in_array($avatarNetwork, $this->_networks ) || $avatarNetwork == 'buildit')) {

                if ( $avatars[$avatarNetwork] != '' || $avatars[$avatarNetwork] != null ) {
                    $image->setURL( $avatars[$avatarNetwork] );
                    $avatar = $image;
                }
            }


            if ( !empty($avatar) && $fullPath == true ) {
                $avatar = $image->cdn_url;
            }

            return $avatar;
        }


         public function getAvatars( $fullPath = false ) {
            global $log, $__cdn__;
            $preferences = $this->getPreferences();

            $avatars = (array) $preferences['avatars'];

            if ( $fullPath == true ) {
                foreach ($avatars as $key => $value) {
                    if ( $value != null ) {
                        $avatars[$key] = $__cdn__.$value;
                    }
                }
            }

            return $avatars;
        }

        public function getEmailPreferences() {
            $prefs = $this->getPreferences();
            return $prefs['email_notifications'];
        }

        public function updatePreferences( $data, $update_db = true ) {
            global $log;

            requireFunction('forceBoolsInArray');

            $log->append('updatePreferences', $data);

            $data = forceBoolsInArray($data);

            $log->append('updatePreferences', $data);

            $result = $this->getPreferences();

            $log->append('updatePreferences', $result);


            if ( is_array( $data ) ) {
                $currentPrefs = $result;
                $result = array_replace_recursive( $currentPrefs, $data );
                $this->preferences = json_encode($result);
            }

            $log->append('updatePreferences', '----- result ----');
            $log->append('updatePreferences', $result);


            if ( $update_db == true ) {
                $result = $this->update('preferences');
            }

            return $result;

        }

        public function getPreferences( $fullPath = false) {
            global $log, $pdo;

            $preferences = $this->preferences;
            // $log->append('getPreferences', $preferences);

            $obj = (array) json_decode($preferences, true);

            $emailNotificationsDefault = array(
                    "notify_followed"   => true,
                    "notify_idea_fav"   => true,
                    "notify_updates"    => true
                );

            if ( !$preferences || $preferences == null ) {
                $obj = array("active_avatar"=>null, "avatars"=>array("buildit"=>null, "twitter"=>null, "dribbble"=>null, "github"=>null), "email_notifications"=>false, "saw_tour"=>false);
            }

            $log->append('email_notifications', is_array($obj) );

            if ( $obj['email_notifications'] == false ) {
                $obj['email_notifications'] = $emailNotificationsDefault;
            }

            if ( $fullPath == true ) {
                $obj['avatars'] = $this->getAvatars( $fullPath );
            }

            $obj['active_avatar'] = $this->avatar;

            return $obj;

        }

        public function setUniqueIdentifier( $identifier ) {
            global $log;

            $identifierField = null;

            if ( is_numeric( $identifier ) ) {
                $identifierField = 'id';
            } else if ( filter_var($identifier, FILTER_VALIDATE_EMAIL) ) {
                $identifierField = 'account_email';
            } else {
                $identifierField = 'username';
            }

            $this->$identifierField = $identifier;

            return $identifierField;

        }


    }

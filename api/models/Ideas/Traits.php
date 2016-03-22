<?php
	
	requireModel('image');

  	//**************************************************************
    //
    // User Traits
    //
    //**************************************************************
    trait IdeaTraits {
   		public $id;
        public $user_id;
        public $name;
        
        public $created_at;
        public $updated_at;

        public $idea_type;
        // public $offering;
        public $url;
        public $like_count;
        public $view_count;
        public $about;
        public $looking_for;
        public $is_looking_for = 0;
        public $extra_info;
        public $images = array();

        public $traits;
        public $popularity_alltime;

        public $description;
        public $favorited;

        public $twitter;
        public $website;
        public $privacy;

        public $featured;
        public $user_data;
        public $images_count;

        public $cover;
        public $icon;

        // public $image_1;
        // public $image_2;
        // public $image_3;
        // public $image_4;

        public $_gallery;

        //
        // database related
        //
        protected $_pull_table          = "ideas";
        protected $_pull_blacklist      = array();

        protected $_set_blacklist       = array("id");
        
        protected $_push_table          = "ideas";
        protected $_push_mandatory      = array();

        protected $_insert_blacklist    = array("id", "_exists", "images", "user_data", "user", "_gallery");
        protected $_update_blacklist    = array("id", "_exists", "images", "user_data", "user", "_gallery");

        public function dataBeforeUpdate( $data ) {
            return $this->dataDefaults( $data );
        }

        public function dataBeforeInsert( $data ) {
            return $this->dataDefaults( $data );
        }

        public function dataDefaults( $data ) {
            if ( empty($data['url']) ) {
                $url            = $this->generateUrl();
                $this->url      = $url;
                $data['url']    = $url;
            }
            return $data;
        }

        public function countMatch( $preg, $str ) {
            global $log;

            $count = count(preg_grep($preg, explode(" ", $str)));

            $log->append('countMatch', '-----------------');
            $log->append('countMatch', $preg.' - '.$str);
            $log->append('countMatch', $count);

            return $count;
        }

        public function calcSearch( $for, $forArray ) {
            global $log;

            $search_points = 10;

            $name           = strtolower($this->name);
            $description    = strtolower($this->description);
            $status         = strtolower($this->status);

            $looking_for    = strtolower($this->looking_for);
            $about          = strtolower($this->about);
            $extra_info     = strtolower($this->extra_info);

            $pop            = $this->popularity_alltime;


            //
            // Creating a preg regex with match
            //
            $forArrayStr = implode('|', $forArray);
            $preg = "/(".$forArrayStr.")/";
            $pregArray = array();

            //
            // Username
            //

            if (strpos( $name, $for) !== false) {
                $search_points += 100;
            }
            if ( $name == $for) {
                $search_points += 150;
            }
            $search_points += similar_text($for, $name) * 10;
            $search_points += $this->countMatch( $preg, $name )*50;
            $search_points += round($pop/10);

            //
            // Name
            //
            if (strpos( $description, $for) !== false) {
                $search_points += 100;
            }
            if ( $description == $for) {
                $search_points += 150;
            }
            $search_points += $this->countMatch( $preg, $description )*30;
            $search_points += similar_text($for, $description) * 10;

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
            // looking_for
            //
            if ( in_array($looking_for, $forArray) ) {
                $search_points += 30;
            }
            if (strpos( $looking_for, $for) !== false) {
                $search_points += 50;
            }
            $search_points += $this->countMatch( $preg, $looking_for )*30;
            $search_points += similar_text($for, $looking_for) * 10;


            //
            // about
            //
            if ( in_array($about, $forArray) ) {
                $search_points += 20;
            }
            if (strpos( $about, $for) !== false) {
                $search_points += 50;
            }
            $search_points += $this->countMatch( $preg, $about )*20;
     

            //
            // extra_info
            //
            if ( in_array($extra_info, $forArray) ) {
                $search_points += 20;
            }
            if (strpos( $extra_info, $for) !== false) {
                $search_points += 50;
            }
            $search_points += $this->countMatch( $preg, $extra_info )*30;
            $search_points += similar_text($for, $extra_info) * 10;

            $this->search_points = $search_points;

        }


        public function generateUrl( $add = '1267' ) {
            requireFunction('generateRandomChars');
            $hash = base64_encode(generateRandomChars(4, false).$add.'1');
            return $hash;
        }

        // public function getImages() {
        //     $images = array();

        //     if ( !empty($this->$image_1) ) {
        //         $images[] = $this->$image_1;
        //     }

        //     return $images;
        // }

        protected function generate_query_basic() {
            $id                 = $this->id;
            $pull_query    = null;
            if ( !empty($id) ) {
                $pull_query = "SELECT id, user_id, name FROM ideas WHERE id = ".$id;
            } 
            return $pull_query;
        }

        protected function generate_query_gallery() {
            $id  = $this->id;
            $pull_query    = null;
            if ( !empty($id) ) {
                $pull_query = "SELECT id, user_id, gallery, cover, icon FROM ideas WHERE id = ".$id;
            } 
            return $pull_query;
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

        protected function generate_query_from_user() {
            $id         = $this->id;
            $user_id    = $this->user_id;
            $pull_query    = null;
            if ( !empty($id) ) {
                $pull_query = "SELECT id FROM ideas WHERE id = ".$id." AND user_id = ".$user_id;
            } 
            return $pull_query;
        }


        //
        // Null     is public
        // 0        everytone with url can view
        // 1        only the owner can view
        //
        public function getPrivacy() {
            return $this->privacy;
        }

        //         if ( $idea->userCanSee() == 0 || $idea->privacy == 1 || $idea->privacy == 0 || $idea->privacy == 1 ) {


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

        //
        // @TODO ? this query is a huge loop
        //  check getFavoritedIdea from user list
        //
        public function getUsersFavorite() {
            global $status, $log;

            $tmpStatus = $status;
            $listArray = null;

            $like_count = $this->like_count;

            if ( $like_count > 0 ) {

                // requireCollection('UserCollection');
                // $userList = new UserList();
                // $userList->per_page = 2;

                // $id = $this->id ? $this->id : $this->user_id;

                // $log->append('getUsersFavorite', $id);

                // $listArray = $userList->getFavoritedIdea( $this->id );

                $status = $tmpStatus;

            }

            return $listArray;
        }

        public function getFavorited( $pull = true ) {
            global $pdo, $log;

            if ( isset($this->favorited) ) {
                return $this->favorited == 1 ? true : false;
            } else {
                if (  $pull == true ) {
                
                    $query_likes    = "SELECT like_id FROM likes WHERE user_id = ".$_SESSION['user']->id." AND idea_id = ".$this->id;
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
            }

        }   

        public function contact( $message, $extradata = array() ) {
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

                $data = array( $data, $extradata );

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

            if ( !empty($this->idea_type) && !empty($__types__) ) {
                $result = $__types__->getWhere('id', $this->idea_type);
            }

            return $result;
        }


        public function getAll( $data = null ) {
            if ( is_array($data) ) {
                $array = $datas;
            } else {
                $array = $this->getProfile();
            }

            $array['view_count'] = $this->view_count;
            $array['like_count'] = $this->like_count;
            $array['popularity_alltime']    = $this->popularity_alltime;
            $array['popularity_featured']   = $this->popularity_featured;

            return $array;
        }

        public function getPopular() {
            $array = $this->getSummary();
            $array['popularity_alltime'] = $this->popularity_alltime;
            return $array;
        }

        public function getProfile( $pull = false ) {
            global $__cdn__, $log;

            // include_once "user.php";

            $array = array();
            $images = null;

            $array['id']            = $this->id;

            $array['name']          = $this->name;
            
            $array['created_at']    = $this->created_at;
            $array['updated_at']    = $this->updated_at;

            $array['avatar']        = $this->getAvatar();

            $array['idea_types']    = $this->getTypes();
            $array['url']           = $this->url;

            $array['like_count']    = $this->like_count;

            $array['description']   = $this->description;
            $array['about']         = $this->about;
            $array['looking_for']   = $this->looking_for;
            $array['extra_info']    = $this->extra_info;

            $array['twitter']       = $this->twitter;
            $array['website']       = $this->website;

            $array['traits']        = $this->getTraits( $this->traits );

            $array['favorites_count']   = $this->like_count;
            $array['favorites_users']   = null; //$this->getUsersFavorite();

            $array['cover']         = $this->getCover();
            $array['images']        = $this->getGallery();
            $array['is_looking_for']    = $this->is_looking_for;
            $array['id_cover']          = $this->id_cover;


            if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $array['favorited'] = $this->getFavorited();
                if ( $_SESSION['user']->id && $_SESSION['user']->id == $this->user_id) {
                    $array['view_count']    = $this->view_count;
                    $array['featured']      = $this->featured;
                }
            }

            $array['images_count']     = count($array['images']);
            $array['privacy'] = $this->getPrivacy();


            if ( $this->user_id ) {
                $user = new StaticUser();
                $user->id = $this->user_id;
                $user->pull();
                $array['user'] = $user->getEssentials();
            }

            return $array;
        }

        public function getGalleryObject( $u = '' ) {
            global $log;

            $data = json_decode( $this->gallery );

            $log->append('getGalleryObject', '--------------- '.$u);
            $log->append('getGalleryObject', $data);

            requireCollection('ImageCollection');

            $gallery = new ImageCollection();

            $log->append('getGalleryObject', $gallery);
            $gallery->set($data);
            $log->append('getGalleryObject', $gallery);

            return $gallery;
        }

        public function getGallery() {
            global $log;

            $images = $this->getGalleryObject('getGallery');

            $log->append('getGallery', $images);

            if ( $images->count ) {

                $array = $images->getCDNArray();
                $log->append('getGallery', $array);

                return $array;
            } else {
                return null;
            }

        }


        // if ( $this->image_1 ) {
        //     $images = array();

        //     if( strpos( $this->image_1, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_1 );
        //     } else {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
        //     }
        // }

        // if ( $this->image_2 ) {
        //     if( strpos( $this->image_2, "http://") !== false || strpos( $this->image_1, "https://") !== false  ) {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_2 );
        //     } else {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
        //     }
        // }

        // if ( $this->image_3 ) {
        //     if( strpos( $this->image_3, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_3 );
        //     } else {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
        //     }
        // }

        // if ( $this->image_4 ) {
        //     if( strpos( $this->image_4, "http://") !== false || strpos( $this->image_1, "https://") !== false ) {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$this->image_4 );
        //     } else {
        //         $images[]  = array("description"=>"Ipsum Pharetra Inceptos","image"=>$__cdn__.'temp-image-'.rand(1, 4).'.jpg' );
        //     }
        // }

        public function getAvatar() {
            $gallery = $this->getGallery();
            $url = null;

            if ( $this->icon ) {

            } else if ( is_numeric($this->id_cover) && isset($gallery[$this->id_cover]) ) {
                $url = $gallery[$this->id_cover]['image'];
            }

            // if ( $this->icon && $gallery && $gallery[$this->icon] ) {
            //     $image = $gallery[$this->icon];
            //     $url = $image['image'];
            // }

            return $url;
        }

        public function getCover() {
            global $__cdn__;

            $cover = $this->cover;

            if ( $cover ) {
                return $__cdn__.$this->cover;
            } else {
                return null;
            }
        }

        public function isFeatured() {
            $is = false;
            if ( is_numeric($this->popularity_featured) ) {
                $val = intval($this->popularity_featured);
                if ( $val > 0 ) {
                    $is = true;
                }
            }
            return $is;
        }

        public function getListSummary() {
            global $__cdn__;

            $array = array();

            $array['id']            = $this->id;
            // $array['user_id']       = $this->user_id;

            $array['avatar']        = $this->getAvatar();

            $array['name']          = $this->name;
            $array['created_at']    = $this->created_at;
            $array['url']           = $this->url;

            $array['like_count']    = $this->like_count;

            $array['featured']      = $this->isFeatured();

            $array['cover']         = $this->getCover();

            return $array;
        }

        public function getSummary( $empty = false, $include = null ) {
            global $__cdn__;

            $array = array();

            $array['id']            = $this->id;
            // $array['user_id']       = $this->user_id;

            $array['avatar']        = $this->getAvatar();

            $array['name']          = $this->name;
            $array['created_at']    = $this->created_at;
            $array['idea_type']     = $this->getTypes( $this->idea_type );
            $array['url']           = $this->url;

            $array['like_count']    = $this->like_count;

            $array['featured']      = $this->isFeatured();

            $array['description']   = $this->description;
            $array['looking_for']   = $this->looking_for;
            
            $array['traits']        = $this->getTraits( $this->traits );

            $array['privacy']       = $this->privacy;
            $array['cover']         = $this->getCover();
            $array['favorited']     = $this->getFavorited();
            $array['is_looking_for'] = $this->is_looking_for;

            if ( $_SESSION['user'] && $_SESSION['user']->id && $_SESSION['user']->id == $this->user_id) {
                $array['view_count']    = $this->view_count;
                $array['privacy']       = $this->privacy;
                $array['featured']      = $this->featured;
            }

            $array['images']            = $this->getGallery();
            $array['images_count']     = count($array['images']);

            if ( isset($this->user__id) ) {
                $array['user'] = $this->getUserEssentials();
            }


            if ( $include ) {
                $array[$include] = $this->$include;
            }


            return $array;
        }

        public function get_user_data( $key, $val ) {
            global $log;
            if ( strpos($key,'user__') !== false ) {
                $log->append('get_user_data', $key." : ".$val);
                $this->user_data[ str_replace("user__", "", $key) ] = $val;
            }
            return $val;
        }

        public function getUserEssentials() {
            global $log;

            if ( !is_array($this->user_data) ) {
                $this->user_data = array();
            }
            $this->get('user_data');
            // $log->append('getUserEssentials', $this->user_data);

            $user = new StaticUser();
            $user->set($this->user_data);
            $user->id = $this->user_data['id'];

            // $log->append('getUserEssentials', $user->getEssentials());

            return $user->getEssentials();
        }


    }

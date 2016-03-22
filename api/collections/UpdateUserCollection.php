<?php

    requireCollection('UserCollection');
    requireModel('Users/DynamicUser');
    requireModel('image');

    //**************************************************************
    //
    // A collection of Users
    //
    //**************************************************************
    class UpdateUserList extends UserList {

        public $ModelClass = 'DynamicUser';

        public function pull_update_popularity ( $user ) {
            global $log;
            $total = $user->calcPopularity();
            // $this->set( array("popularity_alltime"=>$total) );
            // $log->append('pull_update_popularity', $user->username.' = '.$total);
            $user->popularity_alltime = $total;
            $user->update();
        }

        public function pull_update_avatars ( $user ) {
            global $log;
            $url = $user->avatar;

            if ( strpos($url,'gravatar') !== false ) {
                $url .= '?s=400&d=NULL';
            } else if ( strpos($url,'twitter') !== false  ) {
                $twitterHandle = $this->get_string_between($url,'profile_image/','?size');
                if (!empty($twitterHandle)) {
                    $this->twitter = $twitterHandle;
                    $url = 'https://twitter.com/'.$twitterHandle.'/profile_image?size=original';
                }                 

            }

            $urlObject = parse_url( $url );
            if( $urlObject['scheme'] == 'https' || $urlObject['scheme'] == 'http') {

                $avatar = new Image();
                $avatar->downloadImage( $url );
                $avatar->generate_image_thumbnail();

                $blur = new Image();
                $blur->local_url = $avatar->local_url;
                $blur->local_filename = $avatar->local_filename;
                $blur->blur();


                $success = $avatar->sendToCDN('avatar_buildit.'.$avatar->extension, 'images/users/'.$user->id.'/');

                if ( $success ) {

                    $user->addAvatar( $avatar->relative_url );
                    $user->setAvatar('buildit');
                    $log->append('update_avatars', $avatar->url);
                    $user->update();
                    
                    $blur->sendToCDN('avatar_buildit_blur.'.$avatar->extension, 'images/users/'.$user->id.'/');

                }

            }

        }

        protected function get_string_between($string, $start, $end){
            $string = " ".$string;
            $ini = strpos($string,$start);
            if ($ini == 0) return "";
            $ini += strlen($start);
            $len = strpos($string,$end,$ini) - $ini;
            return substr($string,$ini,$len);
        }

        protected function pre_filter_oldavatars() {
            $this->where_filters[] = "avatar != 'https://id.val.io/uploads/avatars/default-avatar.png' and avatar is not NULL";
        }

    }

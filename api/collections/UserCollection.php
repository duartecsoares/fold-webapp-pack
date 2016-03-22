<?php

    requireCollection('StaticCollection');

    //**************************************************************
    //
    // A collection of Users
    //
    //**************************************************************
    class UserList extends StaticCollection {

        public $ModelClass = 'StaticUser';

        //
        //
        // Pre filters
        //
        //
        protected function pre_filter_traits( $traits, $andOr = 'and' ) {
            $this->where_filters[] = "traits LIKE '%".$traits."%'";
        }

        protected function pre_filter_work( $has = "false" ) {
            if ( $has == "true" ) {
                $this->where_filters[] = "connections_data IS NOT NULL and connections_data <> '{\"dribbble\":null,\"github\":null}' and connections_data not like '%images\":[]%' and connections_data not like '%repos\":[]%'";
            }
        }

        protected function pre_filter_status( $status ) {
            $this->where_filters[] = "status LIKE '".$status."'";
        }

        protected function pre_filter_avatar( $hasAvatar = true ) {
            global $log;
            $log->append('pre_filter_avatar', $hasAvatar);

            if ( $hasAvatar == true ) {
                $this->where_filters[] = "avatar <> ''";
            } else {
                $this->where_filters[] = "avatar IS NULL";
            }
        }

        protected function pre_filter_following( $id = null ) {
            global $log;

            if ( $id ) {
                $this->where_filters[] = "follows_users.following_id = ".$id." AND users.id = follows_users.follower_id";
            } 
        }

        protected function pre_filter_follower( $id = null ) {
            global $log;

            if ( $id ) {
                $this->where_filters[] = "follows_users.follower_id = ".$id." AND users.id = follows_users.following_id";
            } 
        }

        protected function pre_filter_likedidea( $id = null ) {
            global $log;

            if ( $id ) {
                $this->where_filters[] = "likes.idea_id = ".$id." AND likes.user_id = users.id";
            } 
        }

        protected function search_v1( $for,  $array, $string ) {
            if ( strlen($value) < 3 ) {
                $where = " username LIKE '".$string."%'";
            } else {
                $where = " username LIKE '%".$string."%'";
            }  
            foreach ($array as $key => $value) {

                if ( strlen($value) > 2 ) {
                    //$where .= " OR LOWER(fullname) LIKE '%".$value."%' OR LOWER(status) LIKE '%".$value."%' OR CONTAINS(skills, '".$value."') OR CONTAINS(bio, '".$value."')";
                } 
                //$where .= " OR LOWER(username) LIKE '%".$value."%' OR LOWER(fullname) LIKE '%".$value."%' OR LOWER(status) LIKE '%".$value."%' OR CONTAINS(skills, '".$value."') OR CONTAINS(bio, '".$value."')";
            }
            $log->append('search_v1', '----------------------');
            $log->append('search_v1', $array);
            $log->append('search_v1', $string);            
            $log->append('search_v1', $where);  
            return $where;
        }

        protected function search_v2( $for,  $array, $string ) {
            if ( strlen($value) < 3 ) {
                $where = " username LIKE '".$string."%'";
            } else {
                $where = " username LIKE '%".$string."%'";
            }  
            foreach ($array as $key => $value) {

                if ( strlen($value) > 4 ) {
                    $where .= " OR fullname LIKE '%".$value."%' OR status LIKE '%".$value."%' OR skills LIKE '%".$value."%' OR bio LIKE '%".$value."%'";
                    //$where .= " OR LOWER(fullname) LIKE '%".$value."%' OR LOWER(status) LIKE '%".$value."%' OR CONTAINS(skills, '".$value."') OR CONTAINS(bio, '".$value."')";
                } else if( strlen($value) >= 2 ) {
                    $where .= " OR fullname LIKE '".$value."%' OR status LIKE '".$value."%' OR skills LIKE '%".$value."%' OR bio LIKE '%".$value."%'";
                    //$where .= " OR LOWER(fullname) LIKE '%".$value."%' OR LOWER(status) LIKE '%".$value."%' OR CONTAINS(skills, '".$value."') OR CONTAINS(bio, '".$value."')";
                }
                //$where .= " OR LOWER(username) LIKE '%".$value."%' OR LOWER(fullname) LIKE '%".$value."%' OR LOWER(status) LIKE '%".$value."%' OR CONTAINS(skills, '".$value."') OR CONTAINS(bio, '".$value."')";
            }
            $log->append('search_v2', '----------------------');
            $log->append('search_v2', $array);
            $log->append('search_v2', $string);            
            $log->append('search_v2', $where);            
            return $where;
        }

        protected function search_v3( $for, $array, $string ) {
            global $log;

            if ( strlen($for) <= 3 ) {
                $where = " username LIKE '".$for."%'" ;
            } else {
                $where = " username LIKE '%".$string."%' OR fullname LIKE '%".$string."%'";
            }  

            if ( count($array) > 1 ) {
                foreach ($array as $key => $value) {
                    if ( strlen($value) > 2 ) {
                        $where .= " OR fullname LIKE '%".$value."%'";
                    }
                }
            }

            if ( strlen($for) >= 3 ) {
                $where .= " OR status LIKE '%".$string."%' OR skills LIKE '%".$string."%' OR bio LIKE '%".$string."%'";
            }
            $log->append('search_v3', '----------------------');
            $log->append('search_v3', $for);
            $log->append('search_v3', $array);
            $log->append('search_v3', $string);            
            $log->append('search_v3', $where);
            return $where;
        }

        protected function pre_filter_search( $for = null ) {
            global $log;

            if ( $for ) {

                $for    = strtolower($for);
                $array  = explode(" ", $for);
                $string = implode("%", $array);

                $this->searchForArray = $array;

                $this->where_filters[] = $this->search_v3( $for, $array, $string);

            } 
        }

        protected function pre_filter_date( $date = '' ) {
            $this->where_filters[] = "created_at LIKE '%".$date."%'";
        }


        protected function pre_filter_active( $date = '' ) {
            $this->where_filters[] = "last_seen LIKE '%".$date."%'";
        }


        protected function pre_filter_session( $user_id, $request = null ) {
            global $log;

            $following_ids = $_SESSION['user']->getFollowingIDs( false );

            $log->append('pre_filter_session', $user_id);
            $log->append('pre_filter_session', $following_ids);

            if ( $following_ids === false ) {
                $log->append('pre_filter_session', 'following over limit');
                $this->setFields("*, SUM(IF(follows_users.follower_id = ".$user_id.", true, false)) as followed");
                $this->join         = "LEFT JOIN follows_users ON (users.id = follows_users.following_id)";
                $this->group_by     = "GROUP BY users.id";
            } else if ( $request == 'following' || $request == 'followers') {
                $this->setPullTable('users, follows_users');
                $log->append('pre_filter_session', 'following under limit');
            }

        }

        //
        //
        // Getting the right data for each situation
        //
        //
        protected function pull_summary( $obj ) {
            global $log;
            return $obj->getSummary();
        }

        protected function pull_list_summary( $obj ) {
            global $log;
            return $obj->getListSummary();
        }

        protected function pull_profile( $obj ) {
            global $log;
            return $obj->getProfile();
        }

        protected function pull_search( $obj ) {
            global $log;
            $searchFor      = $this->searchFor;
            $searchForArray = $this->searchForArray;
            $obj->calcSearch($searchFor, $searchForArray);
            return $obj->getSummary( false, 'search_points');
        }

        public function setOrderBySearch() {
            global $log;
            $models = $this->models;

            $log->append('setOrderBySearch_User', '-----------');
            $log->append('setOrderBySearch_User', $models);

            $search_points = array();

            // Obtain a list of columns
            foreach ($models as $key => $row) {
                $search_points[$key] = $row['search_points'];
            }

            // Sort the data with mid descending
            // Add $data as the last parameter, to sort by the common key
            array_multisort($search_points, SORT_DESC, $models);

            $log->append('setOrderBySearch_User', $models);
            $log->append('setOrderBySearch_User', $search_points);

            $this->models = $models;

        }

        public function setSession( $extra = null ) {
            global $log;
            $log->append('setSession', '-------------');
            $log->append('setSession', $_SESSION['user']);
            if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $this->setPreFilter('session', $_SESSION['user']->id, $extra);
            }
        }

        //
        //
        // Shortcuts for Getting list of users by a certain order
        //
        //
        public function getNew( $return = 'summary' ) {
            $this->setSession();
            $this->setOrderBy('id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function getToday( $return = 'summary' ) {
            $this->setSession();
            $this->setOrderBy('id', 'DESC');
            $this->setPreFilter('date', date("Y-m-d") );
            $this->pull( $return );
            return $this->models;
        }

        public function getActive( $return = 'summary' ) {
            $this->setSession();
            $this->setOrderBy('last_seen', 'DESC');
            $this->setPreFilter('active', date("Y-m-d") );
            $this->pull( $return );
            return $this->models;
        }

        public function getOld( $return = 'summary' ) {
            $this->setSession();
            $this->setOrderBy('id', 'ASC');
            $this->pull( $return );
            return $this->models;
        }

        // public function getFavd() {
        //     return $this->getFollowing();
        // }

        public function getFollowing( $id, $return = 'summary' ) {
            $this->setSession('following');
            $this->setPreFilter('follower', $id);
            $this->setOrderBy('follows_users.follow_id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function getFollowers( $id, $return = 'summary' ) {
            $this->setSession('followers');
            $this->setPreFilter('following', $id);
            $this->setOrderBy('follows_users.follow_id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function getFavoritedIdea( $id, $return = 'summary' ) {
            $this->setPullTable('users, likes');
            $this->setPreFilter('likedidea', $id);
            $this->setOrderBy('likes.like_id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function getPopular( $return = 'summary' ) {
            $this->setSession();
            $this->setOrderBy('popularity_alltime', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function search( $for = '', $return = 'search' ) {
            $this->setSession();
            $this->setOrderBy('popularity_alltime', 'DESC');
            $this->setPreFilter('search', $for );
            $this->searchFor = strtolower($for);
            $this->pull( $return );
            $this->setOrderBySearch();
            return $this->models;
        }


    }

<?php

    requireCollection('StaticCollection');
    requireModel('Ideas/StaticIdea');

    //**************************************************************
    //
    // A collection of Users
    //
    //**************************************************************
    class IdeaList extends StaticCollection {

        public $ModelClass = 'StaticIdea';
        //
        // database related
        //
        protected $_pull_table = "ideas";

        //
        //
        // Pre filters
        //
        //
        protected function pre_filter_traits( $traits, $andOr = 'and' ) {
            $this->where_filters[] = "ideas.traits LIKE '%".$traits."%'";
        }

        protected function pre_filter_image( $has = "false" ) {
            if ( $has == "true" ) {
                $this->where_filters[] = "gallery IS NOT NULL and gallery <> ''";
            }
        }

        protected function pre_filter_status( $status ) {
            $this->where_filters[] = "status LIKE '".$status."'";
        }

        protected function pre_filter_type( $traits ) {
            $this->where_filters[] = "idea_type LIKE '%".$traits."%'";
        }

        protected function pre_filter_privacy( $showHidden = false ) {
            if( $showHidden == false ) {
                $this->where_filters[] = "(privacy = 0 OR privacy IS NULL)";
            } else if ( $showHidden == true) {
                $this->where_filters[] = "(privacy LIKE '%')";
            }
        }

        protected function pre_filter_user( $id = null ) {
            global $log;
            if ( $id ) {
                $this->where_filters[] = "likes.user_id = ".$id." AND ideas.id = likes.idea_id";
            } 
        }

        protected function search_v1($for, $array, $string) {

            if ( strlen($value) < 2 ) {
                $where = " LOWER(name) LIKE '".$string."%'";
            } else {
                $where = " LOWER(name) LIKE '%".$string."%'";
            }
            
            foreach ($array as $key => $value) {

                if ( count($array) > 1 && strlen($value) > 2 ) {
                    $where .= " OR CONTAINS(name,'".$value."')";
                }

                if ( strlen($value) > 2 ) {
                    $where .= " OR CONTAINS(description, '".$value."') OR CONTAINS(looking_for, '".$value."')";
                // } else if ( strlen($value) > 3 ) {
                    $where .= " OR CONTAINS(about, '".$value."') OR CONTAINS(ideas.extra_info, '".$value."')";
                }
                // $where .= " OR LOWER(name) LIKE '%".$value."%' OR LOWER(description) LIKE '%".$value."%' OR LOWER(ideas.status) LIKE '%".$value."%' OR LOWER(looking_for) LIKE '%".$value."%' OR LOWER(about) LIKE '%".$value."%' OR LOWER(ideas.extra_info) LIKE '%".$value."%'";
            }

        }

        protected function search_v2($for, $array, $string) {
            global $log;

            $log->append('search_v2', '----------------------');

            if ( strlen($for) <= 2 ) {
                $where = " name LIKE '".$for."%'" ;
            } else {
                $where = " name LIKE '".$string."%' OR description LIKE '%".$string."%'";
            }  

            // if ( count($array) > 1 ) {

            //     $log->append('search_v2', 'words:');
            //     $log->append('search_v2', $words);

            //     $words = implode('|',$array);

            //     $where .= " OR description REGEXP '%".$words."%'";

            //     // foreach ($array as $key => $value) {
            //     //     if ( strlen($value) > 2 ) {
            //     //         $where .= " OR description LIKE '%".$value."%'";
            //     //     }
            //     // }
            // }

            if ( strlen($for) >= 3 ) {
                $where .= " OR looking_for LIKE '%".$string."%' OR about LIKE '%".$string."%'";
            }
            $log->append('search_v2', $for);
            $log->append('search_v2', $array);
            $log->append('search_v2', $string);            
            $log->append('search_v2', $where);
            return $where;
        }

        protected function search_v3($for, $array, $string) {
            global $log;

            if ( strlen($for) <= 2 ) {
                $where = " name LIKE '".$for."%'" ;
            } else {
                $where = " name LIKE '%".$string."%'";
            }  

            $where .= " AND MATCH (name, description, looking_for, about, ideas.extra_info) AGAINST ('".$for."')";

            $log->append('search_v3_ideas', '----------------------');
            $log->append('search_v3_ideas', $for);
            $log->append('search_v3_ideas', $where);
            return $where;
        }

        protected function search_v4($for, $array, $string) {
            global $log;

            if ( strlen($for) <= 2 ) {
                $where = " name LIKE '".$for."%'" ;
            } else {
                $where = " name LIKE '%".$string."%'";
            }  

            // $where = " name LIKE '%".$string."%'";

            $this->fields .= ", ideas.popularity_alltime as OrderKey ";
            $tmpWhere = $this->generate_where_filters();
            $this->where_filters[] = " MATCH (name, description, looking_for, about, ideas.extra_info) AGAINST ('".$for."') UNION SELECT ".$this->fields." from ".$this->_pull_table." ".$this->join." WHERE ".$tmpWhere." AND ".$where. " ";

            $log->append('search_v4_ideas', '----------------------');
            $log->append('search_v4_ideas', $this->where_filters);
            $log->append('search_v4_ideas', $this->fields );
            $log->append('search_v4_ideas', $this->generate_where_filters() );

            return $where;
        }

        protected function pre_filter_search( $for = null ) {
            global $log;

            if ( $for ) {

                $for    = strtolower($for);
                $array  = explode(" ", $for);
                $string = implode("%", $array);

                $this->searchForArray = $array;

                //$this->where_filters[] = $this->search_v4($for, $array, $string);
                $this->search_v4($for, $array, $string);

            } 
        }

        protected function pre_filter_date( $date = '' ) {
            $this->where_filters[] = "ideas.created_at LIKE '%".$date."%'";
        }

        protected function pre_filter_session( $user_id ) {
            // $this->setFields("*, SUM(IF(likes.user_id = ".$user_id.", true, false)) as favorited");
            // $this->join         = "LEFT JOIN likes ON (idea_id = id)";
            // $this->group_by     = "GROUP BY ideas.id";
            
            $this->setFields("
                ideas.id, ideas.popularity_featured, ideas.popularity_alltime, ideas.created_at, ideas.user_id, ideas.id_cover, ideas.gallery, ideas.icon, ideas.cover, ideas.name, ideas.like_count, ideas.idea_type, ideas.description, ideas.looking_for, ideas.is_looking_for, ideas.traits, ideas.privacy,
                users.username as user__username, users.fullname as user__fullname, users.avatar as user__avatar, users.id as user__id, users.preferences as user__preferences,
                SUM(IF(likes.user_id = ".$user_id.", true, false)) as favorited");

            $this->join             = "LEFT JOIN likes ON (idea_id = id)";
            $this->group_by         = "GROUP BY ideas.id";


// SELECT *, users.id as aid,
//     SUM(IF(likes.user_id = 15595, true, false)) as favorited
// FROM ideas, users LEFT JOIN likes ON (idea_id = id)
// WHERE privacy = 0 AND users.id = ideas.user_id GROUP BY ideas.id ORDER BY ideas.popularity_alltime DESC LIMIT 0, 21

        }

        protected function pre_filter_has_old_images() {
            $this->where_filters[] = "(ideas.gallery = '' or ideas.gallery IS NULL) and ideas.image_1 IS NOT NULL and ideas.image_1 <> ''";
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

        protected function pull_profile( $obj ) {
            global $log;
            return $obj->getProfile();
        }

        protected function pull_popular( $obj ) {
            global $log;
            return $obj->getPopular();
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

            $log->append('setOrderBySearch_Idea', '-----------');
            $log->append('setOrderBySearch_Idea', $models);

            $search_points = array();

            // Obtain a list of columns
            foreach ($models as $key => $row) {
                $search_points[$key] = $row['search_points'];
            }

            // Sort the data with mid descending
            // Add $data as the last parameter, to sort by the common key
            array_multisort($search_points, SORT_DESC, $models);

            $log->append('setOrderBySearch_Idea', $models);
            $log->append('setOrderBySearch_Idea', $search_points);

            $this->models = $models;

        }

        public function setSession() {
            global $log;

            if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $this->setPreFilter('session', $_SESSION['user']->id);
                return true;
            } else {
                $this->setFields("ideas.id, ideas.popularity_featured, ideas.popularity_alltime, ideas.created_at, ideas.id_cover, ideas.gallery, ideas.icon, ideas.cover, ideas.user_id, ideas.name, ideas.like_count, ideas.idea_type, ideas.description, ideas.looking_for, ideas.is_looking_for, ideas.traits, ideas.privacy,
                users.username as user__username, users.fullname as user__fullname, users.avatar as user__avatar, users.id as user__id, users.preferences as user__preferences");
                return false;
            }
        }

        //
        //
        // Shortcuts for Getting list of users by a certain order
        //
        //
        public function getFromUser( $return = 'summary', $user_id ) {
            $this->setSession();
            $this->setPullTable('users, ideas');
            $this->where_filters[]  = "users.id = ideas.user_id";
            $this->where_filters[]  = "ideas.user_id = ".$user_id;
            $this->setOrderBy('id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function getNew( $return = 'summary' ) {
            $this->setSession();
            $this->setPullTable('users, ideas');
            $this->where_filters[]  = "users.id = ideas.user_id ";
            $this->setOrderBy('id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function getToday( $return = 'summary' ) {
            $this->setSession();
            $this->setPullTable('users, ideas');
            $this->where_filters[]  = "users.id = ideas.user_id ";
            $this->setOrderBy('id', 'DESC');
            $this->setPreFilter('date', date("Y-m-d") );
            $this->pull( $return );
            return $this->models;
        }

        public function getOld( $return = 'summary' ) {
            $this->setSession();
            $this->setPullTable('users, ideas');
            $this->where_filters[]  = "users.id = ideas.user_id ";
            $this->setOrderBy('id', 'ASC');
            $this->pull( $return );
            return $this->models;
        }

        public function getFavd( $id, $return = 'summary' ) {
            // $this->setPullTable('ideas, likes, users');
            // $this->where_filters[]  = "users.id = ideas.user_id ";
            $this->setSession();
            $this->setPullTable('users, ideas');
            $this->where_filters[]  = "users.id = ideas.user_id ";
            $this->setPreFilter('user', $id);
            $this->setOrderBy('likes.like_id', 'DESC');
            $this->pull( $return );
            return $this->models;
        }

        public function popularityTimeFriction() {
            global $log;

            // $lastMonthTime = strtotime('2009-11-04');
            $lastMonthTime = strtotime(date('Y-m-d')." -1 month");
            $lastMonth = date('Y-m-d', $lastMonthTime);

            $lastYear = date('Y', $lastMonthTime);
            $lastMonth = date('m', $lastMonthTime);
            $lastDay = date('d', $lastMonthTime);

            if ( $lastDay > 28 ) {
                $lastDay = 29;
            }

            $ts1 = strtotime( $lastYear.'-'.$lastMonth.'-'.$lastDay );
            
            // $log->append('popularityTimeFriction', 'lastMonth : '.$lastYear.'-'.$lastMonth.'-'.$lastDay);
            // $log->append('popularityTimeFriction', 'lastMonthDay : '.$lastDay);
            // $log->append('popularityTimeFriction', 'ts1 : '.$ts1);

            $newModels = array();

            foreach ($this->models as $key => $value) {
                $ts2 = strtotime( $value['created_at'] );
                // $log->append('popularityTimeFriction', 'created_at : '.$value->created_at);
                // $log->append('popularityTimeFriction', 'ts2 : '.$ts2);
                $seconds_diff = $ts2 - $ts1;
                
                if ( $seconds_diff > 86400 ) {
                   $days_diff = intval($seconds_diff/24/60/60);
                }
                
                if ( $days_diff < 2 ) {
                    $days_diff = 2;
                }


                // $log->append('popularityTimeFriction', '------------------');
                // $log->append('popularityTimeFriction', $value);
                // $log->append('popularityTimeFriction', 'VS : '.$ts2.' - '.$ts1);
                // $log->append('popularityTimeFriction', 'Name : '.$value['name']);
                // $log->append('popularityTimeFriction', 'created_at : '.$value['created_at']);
                // $log->append('popularityTimeFriction', 'Days : '.$days_diff);
                // $log->append('popularityTimeFriction', 'Days : '.$days_diff);

                // $log->append('popularityTimeFriction', 'Pop : '.$value['popularity_alltime']);
                $value['pop'] = intval($value['popularity_alltime']/(($days_diff)/5));
                
                // $log->append('popularityTimeFriction', 'Pop Total : '.$value['pop']);
                
                unset($value['popularity_alltime']);
                $newModels[] = $value;
            }


            function sortByPop($a, $b){
                global $log;
                return $b['pop'] - $a['pop'];
            }

            usort($newModels, "sortByPop");
            // $log->append('final_order', $newModels);
            $this->models = array_filter($newModels);
            return $this->models;
        }


        public function getPopular( $return = 'popular' ) {
            $this->setSession();
            $this->setPullTable('users, ideas');

            // $lastMonthTime = strtotime(date('Y-m-d')." -1 month");
            // $lastMonth = date('Y-m-d', $lastMonthTime);
            // $lastYear = date('Y', $lastMonthTime);
            // $lastMonth = date('m', $lastMonthTime);
            // $lastDay = date('d', $lastMonthTime);

            // if ( $lastDay > 28 ) {
            //     $lastDay = 29;
            // }

            $ts1 = strtotime( $lastYear.'-'.$lastMonth.'-'.$lastDay );

            $this->where_filters[]  = "users.id = ideas.user_id";
            $this->setOrderBy('ideas.popularity_alltime', 'DESC');
            $this->pull( $return );
            return $this->popularityTimeFriction();
        }

        public function search( $for = '', $return = 'search' ) {
            $this->setSession();
            $this->setPullTable('users, ideas');
            $this->where_filters[]  = "users.id = ideas.user_id ";
            // $this->setOrderBy('ideas.popularity_alltime', 'DESC');
            $this->setOrderBy('OrderKey', 'DESC');

            $this->setPreFilter('search', $for );
            $this->searchFor = strtolower($for);
            $this->pull( $return );
            $this->setOrderBySearch();

            // global $log;
            // $log->append('search!!', $this->models);
            // $log->append('search!!', count($this->models));
            // $log->append('search!!', $this->models[0]);
            // $log->append('search!!', $this->models[0]['id']);
            // $log->append('search!!', count($this->models) == 1 && $this->models[0]->id == 0 );

            $this->deleteWhere('id', 0);

            if ( count($this->models) == 1 && $this->models[0]['id'] == 0 ) {
                // $log->append('search!!', 'NULL');
                return null;
            } else {
                // $log->append('search!!', 'BANG');
                return $this->models;
            }
        }

    }

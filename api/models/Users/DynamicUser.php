<?php
	
	requireModel('Users/Traits');
    requireModel('DynamicModel');

    //**************************************************************
    //
    // Dynamic (editable) user
    //
    //**************************************************************
    class DynamicUser extends DynamicModel {
        use UserTraits;

        public function addView( $pull = true, $update = true ) {

            if ( $pull == true ) {
                $this->pull();
            }

            $this->view_count += 1;

            if ( $update == true ) {
                $this->update('view_count');
            }

        }

        public function messagesSentToday( $newMessage = "") {
            requireCollection('MessagesCollection');

            global $log;

            $messages = new MessagesCollection();
            $messages->setFrom( $this->id );
            $messages->setDate( date("Y-m-d") );
            $sent   = $messages->pull();
            $count  = 0;
            
            $similarity     = 0;
            $similarities   = array();
            $similarityAbove90Count = 0;

            if ( $sent ) {
                $count = $messages->count;
            }

            // $log->append("messagesSentToday", "-----------------");

            foreach($messages->models as $key => $message) {

                similar_text($message->message, $newMessage, $similarity); 

                if ( $similarity > 80 ) {
                    $similarityAbove90Count++;
                }

                // $log->append("messagesSentToday", $message->message." <-> ".$newMessage);
                $log->append("messagesSentToday", $similarity);
                $log->append("messagesSentToday", $similarityAbove90Count);


            }

            // $log->append('messagesSentToday', "Total : ".$count);

            return array("count"=>$count,"similar"=>$similarityAbove90Count);
        }

        public function conversationsStartedToday() {
            
        }

        public function storeSpamMessage( $to_id, $message ) {
            //
            // spam alert
            //
            requireModel('Conversations/DynamicConversation');

            $conversation = new DynamicConversation();
            $conversation->from_id  = $this->id;
            $conversation->to_id    = $to_id;

            $conversationExists = $conversation->getConversation();

            if ( $conversationExists ) {
                $conversation->addMessage( $this->id, $message, null, true );
            }
        }

        public function blockUser() {
            $this->blocked = true;
            $this->update('blocked');
        }

        public function canSendMessage( $message, $to_id = null ) {
            global $log;
            $blackListedWords = array(
                "equityowl",
                "equityowl.com",
                "equity owl",
                "hiring a partner for equity",
                "hired co-founders",
                "officialhangout.co",
                "officialhangout",
                "easy to earn equity",
                "a great (and free) way",
                "hired a partner for equity",
                "this is cornelius",
                "http://uploto.com/",
                "opportunity to be a co-founder or earn equity",
                "co-founder or earn equity",
                "earn equity in startups",
                "georgepaynebusiness",
                "georgepaynebusiness@gmail.com",
                "lets connect to chat about equity",
                "chat about equity",
                "about equity in startups",
                "ryansshaw15",
                "ryansshaw",
                "georgepayne",
                "currently open to the opportunity to be a co-founder",
                "mitchellbuilder",
                "mitchell@officialhangout.co",
                "mitchelltrulli",
                "super easy to trade equity",
                "easy to trade equity",
                "startups for talented professionals",
                "traded equity for work",
                "equity for work"
                );
            $block = false;

            $lowerMessage = strtolower( $message );

            foreach ($blackListedWords as $word) {
                if (strpos($lowerMessage, $word) !== FALSE) { // Yoshi version
                    $block = true;
                }
            }

            $messagesSentToday = $this->messagesSentToday( $message );

            $count      = $messagesSentToday["count"];
            $similar    = $messagesSentToday["similar"];

            // $log->append('canSendMessage', "Total   : ".$messagesSentToday["count"]);
            // $log->append('canSendMessage', "Similar : ".$messagesSentToday["similar"]);
            // $log->append('canSendMessage', "Block   : ".$block);

            if ( $block == true ) {
                $can = false;

                //
                // spam alert
                //
                $this->storeSpamMessage($to_id, $message);

                if ( $similar > 3 ) {
                    $this->blockUser();
                }

            } else if ( $count > 3 && $similar > 3 ) {
                $can = false;

                //
                // spam alert
                //
                $this->storeSpamMessage($to_id, $message);

            } else if ( $count > 9 ) {
                $can = false;

                //
                // spam alert
                //
                $this->storeSpamMessage($to_id, $message);
                
            } else {
                $can = true;
            }

            if ( $similar > 5 ) {
                $this->blockUser();
            }

            // $log->append('canSendMessage', "Can Message? : ".$can);

            return $can;

        }

        protected function deleteLikes() {
            global $log, $pdo;

            $success = false;

            if ( $this->id ) {

                $data           = array($this->id);
                $query          = "DELETE FROM likes WHERE user_id = ?";
                $sql_prepare    = $pdo->prepare($query);
                $sql_execute    = $sql_prepare->execute($data);
                $success        = $sql_execute;

                $log->append('user_onBeforeDelete', '----- [DELETE LIKES] -----');
                $log->append('user_onBeforeDelete', $query);
                $log->append('user_onBeforeDelete', $data);
                $log->append('user_onBeforeDelete', $sql_execute);

            }

            return $success;
        }

        protected function deleteIdeas() {
            global $log, $pdo;

            $success = true;

            if ( $this->id ) {

                $successArray = array();

                requireModel('Ideas/DynamicIdea');

                $log->append('user_onBeforeDelete', '----- [DELETE IDEAS] -----');

                $ideas = $this->pullIdeas();

                foreach($ideas as $key => $idea) {

                    $ideaClass           = new DynamicIdea();
                    $ideaClass->id       = $idea['id'];
                    $ideaClass->user_id  = $this->id;

                    $success = $ideaClass->delete();
                    $successArray[] = $success;

                    $log->append('user_onBeforeDelete', 'delete : '.$idea['id']);
                    $log->append('user_onBeforeDelete', 'delete : '.$success);

                }

                if ( in_array(false, $successArray) ) {
                    $success = false;
                } else {
                    $success = true;
                }
                // $data           = array($this->id);
                
                // $query          = "DELETE FROM ideas WHERE user_id = ?";
                // $sql_prepare    = $pdo->prepare($query);
                // $sql_execute    = $sql_prepare->execute($data);

                // $success        = $sql_execute;

                $log->append('user_onBeforeDelete', $successArray);
                // $log->append('user_onBeforeDelete', $query);
                // $log->append('user_onBeforeDelete', $data);
                // $log->append('user_onBeforeDelete', $sql_execute);

            }
            
            return $success;
        }

        protected function deleteFollowing() {
            global $log, $pdo;

            $success = false;

            if ( $this->id ) {

                $data           = array($this->id);
                
                $query          = "DELETE FROM follows_users WHERE follower_id = ?";
                $sql_prepare    = $pdo->prepare($query);
                $sql_execute    = $sql_prepare->execute($data);

                $success        = $sql_execute;

                $log->append('user_onBeforeDelete', '----- [DELETE FOLLOWING] -----');
                $log->append('user_onBeforeDelete', $query);
                $log->append('user_onBeforeDelete', $data);
                $log->append('user_onBeforeDelete', $sql_execute);

            }
            
            return $success;
        }

        protected function deleteFollowers() {
            global $log, $pdo;

            $success = false;

            if ( $this->id ) {

                $data           = array($this->id);
                
                $query          = "DELETE FROM follows_users WHERE following_id = ?";
                $sql_prepare    = $pdo->prepare($query);
                $sql_execute    = $sql_prepare->execute($data);

                $success        = $sql_execute;

                $log->append('user_onBeforeDelete', '----- [DELETE FOLLOWERS] -----');
                $log->append('user_onBeforeDelete', $query);
                $log->append('user_onBeforeDelete', $data);
                $log->append('user_onBeforeDelete', $sql_execute);

            }
            
            return $success; 
        }        

        //
        // before deleting an idea, delete the likes
        //
        protected function onBeforeDelete() {
            global $log, $pdo;

            $success = $this->deleteLikes();

            if ( $success ) {

                $success = $this->deleteIdeas();
                
                if ( $success ) {
            
                    $success = $this->deleteFollowing();
            
                    if ( $success ) {
                        $success = $this->deleteFollowers();
                    }
            
                }
            
            }
           
            // if ( $sql_execute != true ) {
            //     $status = 400;
            //     $success = false;
            // } else {
            //     $status = 200;
            //     $success = true;
            // }

            $log->append('user_onBeforeDelete', '------ RESULT ------');
            $log->append('user_onBeforeDelete', $success);

            return $success;

        }

        public function generateResetPasswordHash() {
            requireFunction('generateRandomChars');
            $hash = md5(time().generateRandomChars(12));
            $this->password_auth_created = date("Y-m-d H:i:s", time());
            $this->password_auth = $hash;
            return $hash;
        }

        protected function generate_query_is_followed($data = array()){
            global $query, $log;
            $query = "SELECT follows_users.following_id as id, followed_count FROM ".$this->_pull_table.", follows_users WHERE users.username = '".$this->username."' AND users.id = follows_users.following_id AND follows_users.follower_id = ".$data['user_id'];
            return $query;
        }
        public function deleteConnection($service = ''){

            $service = strtolower( $service );

            if ( !empty($service) ) {

                $connectObject = $this->_connections;

                if ( $connectObject->$service ) {

                    $connectObject->$service->disconnect();
                    
                    $this->connections_data = $connectObject->getStorableData();
                    $this->update('connections_data');

                }
            }

        }

        public function follow( $user_id = null ) {
            global $log;

            requireModel('Likes/LikeUserModel');

            $result = false;

            if ( $user_id == null && isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $user_id = $_SESSION['user']->id;
            }

            $like = new LikeUserModel();
            $like->setFollowing( $this->id );
            $like->setFollower( $user_id );

            $result = $like->push();

            if ( $result != false ) {
                $this->followed_count++;
            }

            return $result;
        }

        public function unFollow( $user_id = null ) {
            global $log;

            requireModel('Likes/LikeIdeaModel');

            $result = false;

            if ( $user_id == null && isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $user_id = $_SESSION['user']->id;

            }

            $like = new LikeUserModel();
            
            $like->setFollowing( $this->id );
            $like->setFollower( $user_id );

            $result = $like->delete();

            if ( $result != false ) {
                $this->followed_count--;
            }

            return $result;
        }

        public function updateFollowingCount( $push = false ) {
            global $log;

            $result = false;

            //
            // reset counts the likes in the like collection for this idea
            //  
            requireCollection('LikesUserCollection');

            $likes = new LikesUserCollection();
            $likes->setFollower( $this->id );
            $result = $likes->pull();

            $this->following_count = $likes->count;

            $log->append('updateFollowingCount', $likes);
            $log->append('updateFollowingCount', $push);
            $log->append('updateFollowingCount', $this->following_count);


            if ( $result != false && $push == true ) {
                $this->update('following_count');
            } 

            return $result;

        }

        public function updateFollowerCount( $push = false ) {
            global $log;

            $result = false;

            //
            // reset counts the likes in the like collection for this idea
            //  
            requireCollection('LikesUserCollection');

            $likes = new LikesUserCollection();
            $likes->setFollowing( $this->id );
            $result = $likes->pull();

            $this->followed_count = $likes->count;

            $log->append('updateFollowerCount', $likes);
            $log->append('updateFollowerCount', $push);
            $log->append('updateFollowerCount', $this->followed_count);


            if ( $result != false && $push == true ) {
                $this->update('followed_count');
            } 

            return $result;

        }

        public function sendEmail( $emailName = null, $extraData = array() ) {
            global $log, $__dev__;

            $log->append('sendEmailPrefs', '----------');

            $emailPrefs = $this->getEmailPreferences();

            $log->append('sendEmailPrefs', $emailPrefs);
            $log->append('sendEmailPrefs', $emailName);

            requireModel('Email/EmailModel');
            $success = false;

            if ( $emailPrefs && $emailName ) {

                $proceed = true;

                if ( $emailName == 'FavoriteIdea' && !$emailPrefs['notify_idea_fav'] ) {
                    $log->append('sendEmailPrefs', 'User doesnt want to be notified of Favorites');
                    $proceed = false;
                } else if ( $emailName == 'FavoriteUser' && !$emailPrefs['notify_followed'] ) {
                    $log->append('sendEmailPrefs', 'User doesnt want to be notified of Follows');
                    $proceed = false;
                }

                if ( $proceed ) {
            
                    $name           = $this->getName();
                    $address        = $this->getContact();
                    $defaultData    = array("to"=>$this->getProfile());
                    $data           = array_merge($defaultData, $extraData);

                    $sessionUserContact     = $_SESSION['user']->getContact();
                    $sessionUserName        = $_SESSION['user']->getName();

                    if ( !empty($address) ) {
                        $email = new Email();
                        $email->addTo( $address, $name );  

                        if ( $emailName == 'ContactUser' ) {
                            $email->replyTo( $sessionUserContact, $sessionUserName );  
                        }

                        $success = $email->send($emailName, $data);
                    }
                }

            }

            return $success;
        }

        public function calcIdeaCount( $pull = true, $update = true ) {
            global $log;
            if ( $pull == true ) {
                $this->pullIdeas( true );
            }
            $log->append('calcIdeaCount', $this->idea_count);
            return $this->update('idea_count');
        }

        public function calcPopularity( $pull = true, $update = true, $pullIdea = false, $updateIdea = false  ) {

            // requireModel('Ideas/DynamicIdea');

            global $log;

            $total = 0;
            $points = array(
                "fullname"  => 10,
                "traits"    => 50,
                "website"   => 20,
                "twitter"   => 20,

                "followed_count" => function($count) {
                    global $log;
                    $log->append('calcPopularity', $count, 'followed_count');
                    return $count*70;
                },

                "ideas"     => function($ideas = null, $pullIdea = false, $updateIdea = false) {
                    return $eachIdea*40;
                },
                "connections" => function($connections = null) {

                    global $log;

                    $total = 10;
                    $eachConnection = 40;
                    $count = 0;

                    foreach($connections as $key => $connection) {
                        if ( $connection ) {
                            $count++;
                        }
                    }

                    $multiplier = 1;

                    if ( $count == 2 ) {
                        $multiplier = .8;
                    } else if ( $count == 3 ) {
                        $multiplier = .7;
                    }
                    $log->append('popularity_connections', 'multiplier : '.$multiplier);

                    // $eachDribbbleShot = 15;
                    // $eachDribbbleShotLike = 0.5;

                    // $eachGithubRepo = 15;
                    // $eachGithubRepoStar = 0.5;


                    foreach($connections as $key => $connection) {

                        $total += $eachConnection;

                        $log->append('popularity_connections', $key);
                        $log->append('popularity_connections', $connection);
                        $log->append('popularity_connections', $this->_connections);

                        if ( $this->_connections && $this->_connections->$key && method_exists($this->_connections->$key, 'calculatePopularity') ) {
                            $log->append('popularity_connections', 'CALCULATE POPULARITY: '.$key);
                            $popularity = $this->_connections->$key->calculatePopularity()*$multiplier;
                            $log->append('popularity_connections', 'CALCULATE POPULARITY: '.$key.' = '.$popularity);

                            if ( $popularity > 2000 ) {

                                $half = $popularity/5;
                                $log->append('popularity_connections', 'CALCULATE POPULARITY: '.$half);
                                $popularity = 2000 +  $half;
                            }
                            $log->append('popularity_connections', 'CALCULATE POPULARITY: after fix :'.$key.' = '.$popularity);
                            $total += $popularity;

                        }

                    }

                    return round($total);
                },

                "more_info" => 20,
                "location"  => 10,
                "avatar"    => 220,
                "bio"       => 50,
                "skills"    => 20,

                "view_count"    => function($count) {
                    global $log;
                    return $count*2;
                },

                // actions
                "action_view"       => 3,
                "action_guest_view" => 1,
                "action_faved"      => 20
            );

            if ( $pull == true ) {
                $this->pull();
                // $this->pullIdeas();
            }

            $data = $this->getAll();


            //
            // calculate points for each and sum
            //
            foreach($data as $key => $value) {

                if ( !empty($points[$key]) && !empty($value) ) {
                    $add = 0;

                    if ( is_callable($points[$key]) ) {
                        $add = $points[$key]( $value, $pullIdea, $updateIdea );
                        $log->append('calcPopularity', $key.' [FUNC] - '.$add );
                    } else if ( is_numeric($points[$key]) ) {
                        $add = $points[$key];
                        $log->append('calcPopularity', $key.' [NUM] - '.$add );
                    }

                    $total += $add;
                }
            }

            $log->append('calcPopularity', 'TOTAL - '.$total );

            $this->popularity_alltime = $total;

            if ( $update == true ) {
                $this->update('popularity_alltime');
            }

            return $total;
        }

        public function updatePassword( $new, $old ) {
            global $log;

            $success = false;

            if ( $this->verifyPassword( $old ) ) {
                $success = $this->setPassword( $new );
            }

            $log->append('updatePassword', $this->password);
            $log->append('updatePassword', 'old: '.$oldPassword);
            $log->append('updatePassword', 'new: '.$new);
            
            return $success;

        }

        public function verifyPassword( $old ) {
            global $log, $pdo;

            $password = md5($old);

            $this->pull();
 
            if ( $this->exists( false ) ) {

                $log->append('verifyPassword', 'verifyPassword');
                $log->append('verifyPassword', $old);
                $log->append('verifyPassword', $password);
                $log->append('verifyPassword', $this->password);
                $log->append('verifyPassword', password_verify($password, $this->password));

                 if ( password_verify($password, $this->password) ) {
                    $success = true;
                 } else {

                    $log->append('verifyPassword', 'old version');
                    $query = "SELECT id FROM users WHERE username = '".$this->username."' AND password = '".$password."'";
                    $result = $pdo->query($query);
                    $log->append('verifyPassword', $result);
                    $log->append('verifyPassword', $query);

                    if ( $result != false ) {
                        $count = $result->rowCount();
                        if ( $count == 1 ) {
                            $success = true;
                        }
                    } else {
                        $success = false;
                    }

                 }

            } else {
                $success = false;
            }

            return $success;

        }

        public function isPasswordValid( $password ) {
            return true;
        }

        public function generatePassword( $password ) {
            global $log;
            
            $log->append('generatePassword', $password);
            $log->append('generatePassword', md5($password));
            
            $passwordHash = password_hash(md5($password), PASSWORD_DEFAULT);

            $log->append('generatePassword', $passwordHash);

            return $passwordHash;
        }

        public function setPassword( $password ) {

            if ( $this->isPasswordValid($password) ) {
                $this->password = $this->generatePassword($password);
                return true;
            } else {
                return false;
            }
        }

        public function isPasswordResetValid( $minutes = 30 ){
            global $log;

            $valid = true;

            $log->append('isPasswordResetValid', $this->password_auth_created);

            $stored_time    = new DateTime($this->password_auth_created);

            $log->append('isPasswordResetValid', $stored_time);

            $current_time   = new DateTime();
            $interval       = $stored_time->diff($current_time);

            if ( $interval->m > $minutes || $interval->h > 0 || $interval->d > 0 || $interval->y > 0 || $interval->days > 0 ) {
                $valid = false;
            }

            $log->append('time_diff', $interval);

            return $valid;
        }

        public function update_set__connections() {
            return "";
        }

        protected function process_traits( $traits ) {
            global $log;

            $str = "";

            $log->append('process_traits', $traits );

            if( is_array($traits) ) {
                $log->append('process_traits', 'is_array');

                foreach ($traits as $trait) {

                    $str .= $trait['id'];

                    if ( $trait !== end($traits) ) {
                        $str .= ",";
                    }

                }

            } else if( is_string($traits) ) {
                $log->append('process_traits', 'is_string');
                $str = $traits;
            }

            $log->append('process_traits', $str);

            return $str;
        }

        protected function insert_set_traits( $traits ) {
            return $this->process_traits( $traits );
        }

        public function update_set_traits( $traits ) {
            global $log;
            $log->append('update_set_traits', $traits);
            return  $this->process_traits( $traits );
        }

    }

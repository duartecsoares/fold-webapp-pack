<?php
    $log->addFile( __FILE__ );

    // GET users
    $dirFile = dirname(__FILE__);

    include_once $dirFile . "/../../models/idea.php";

    $id     = !empty($__request__[3]) ? $__request__[3] : null;
    $action = !empty($__request__[4]) ? $__request__[4] : null;

    $fieldname  = "ideas";
    $count      = 0;
    $ideas      = null;

    $log->append('params', array('id'=>$id) , "REST");

    //
    // route requests to actions and others
    //
    if ( !empty($id) ) {

        if ( !empty($action) ) {
            $action = strtolower($action);
            $file = "_delete_".$action."_idea.php";

            if ( file_exists(dirname(__FILE__).'/'.$file) ) {
                include_once $file;
            } else {
                $status = 400;
                $log->error('warning', array('id'=>1000, 'description'=>'Api request does not exist.') );      
            }

        } else if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {

            requireModel('Ideas/DynamicIdea');

            $idea           = new DynamicIdea();
            $idea->id       = $id;
            $idea->user_id  = $_SESSION['user']->id;

            if ( $idea->exists(true, 'from_user') ) {

                $log->append('DynamicIdea_delete', $idea);
                $log->append('DynamicIdea_delete', method_exists( $idea, 'delete') );

                $result = $idea->delete();

                //
                // If idea exists, that means that it was already favorited
                //
                if ( $result != false ) {
                    
                    $json['deleted'] = true;
                    $status = 200;

                    //
                    // After Process
                    //
                    class Process extends ServerTask {
                        public $user;

                        function __construct($user) {
                            $this->user = $user;
                        }

                        public function run() {
                            return $this->user->calcIdeaCount();
                        }
                    }

                    $process = new Process( $_SESSION["user"] );

                } else {
                    $status = 404;
                    $json['deleted'] = false;
                }

            } else {
                $status = 403;
                $log->error('warning', array('id'=>1003,'description'=>'Cant delete Idea, user not owner.','details'=>''));
            }

        } else {
            $status = 400;
        }

    } else {

        $status = 404;
        $log->error('warning', array('id'=>1000, 'description'=>'Api request does not exist.') );
    
    }


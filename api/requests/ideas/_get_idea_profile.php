<?php
    $log->addFile( __FILE__ );
    $log->append('params', array('id'=>$id) , "REST");

    requireModel('Ideas/DynamicIdea');

    $idea       = new DynamicIdea();
    $idea->id   = $id;
    $result     = $idea->pull();

    $log->append('full_data', $idea);

    if ( $result != false ) {

        if ( $idea->userCanView() ) {
            $json['idea'] = $idea->getProfile();

            //
            // Inquiry to the user
            // 
            //
            // After Process
            //
            class Process extends ServerTask {

                public $idea;

                function __construct($idea) {
                    $this->idea     = $idea;
                }

                public function run() {
                    global $log;

                    $idea = $this->idea;

                    $log->append('calcPopularity_idea', $idea);

                    //
                    // update view and popularity
                    //
                    $idea->addView( false, false );
                    $idea->calcPopularity( false, false );

                    return $idea->update('view_count,popularity_alltime');
                }
            }

            $process = new Process( $idea );
            $process->run();
            $process = null;


        } else {
            $json['idea'] = null;
            $status = 404;
        }
        
    } else {
        $status = 404;
        $json['idea'] = null;
    }




<?php

    requireCollection('IdeaCollection');
    requireModel('Ideas/DynamicIdea');

    //**************************************************************
    //
    // A collection of Users
    //
    //**************************************************************
    class UpdateIdeaList extends IdeaList {

        public $ModelClass = 'DynamicIdea';

        public function pull_update_popularity ( $idea ) {
            global $log;
            $log->append('pull_update_popularity', $idea);
            $total = $idea->calcPopularity();
            $idea->popularity_alltime = $total;
            $idea->update();
        }
    }

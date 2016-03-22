<?php
    
    requireCollection('StaticCollection');
    requireModel('Images/StaticImage');
    requireModel('Ideas/StaticIdea');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class ImageCollection extends StaticCollection {
        
    	public $ModelClass         = 'StaticImage';
        protected $_pull_table      = "ideas";
        protected $fields           = "id, gallery";
        protected $on_pull_append   = false;
        
        protected function pull_default( $obj ) {
            $data = array();
            $gallery = $obj->gallery;
            foreach($gallery  as $image => $value) {
                $image = new StaticImage();
                $image->set($value);
                $data[] = $image;
            }
            return $data;
        }

        public function onBeforeDeleteModel( $model ) {
            global $log;
            $relative_url = $model->relative_url;

            $success = false;

            $log->append('onBeforeDeleteModel', $model);

            if ( $relative_url ) {

                $success = $model->deleteFromCDN();
                $log->append('onBeforeDeleteModel', $success);

            }

            return $success;
        }

        public function getCDNArray() {
            global $__cdn__;
            $arr = array();
            foreach($this->models  as $image => $value) {
                $toStore = array();
                $toStore['image'] = $__cdn__.$value->relative_url;
                $toStore['id'] = $value->id;
                $arr[] = $toStore;
            }
            return $arr;
        }

        public function newID() {

        }

        public function getRelativeArray() {
            global $log;
            $arr = array();
            
            $log->append('getRelativeArray', '################');
            $log->append('getRelativeArray', $this->models);

            foreach($this->models  as $image => $value) {
                $toStore = array();
                $toStore['image'] = $value->image;
                $toStore['id'] = $value->id;
                $log->append('getRelativeArray', $toStore);
                $arr[] = $toStore;
            }
            $log->append('getRelativeArray', '---------');
            $log->append('getRelativeArray', $arr);
            return $arr;
        }

        public function add( $image = null, $onEach = null ) {
            global $log;
            $log->append('addImage', $image);
            $log->append('add', $image);
            $this->append($image);
        }

        public function getStorableData() {
            $arr = $this->getRelativeArray();
            return json_encode($arr);
        }

        //
        // database related
        //
 		protected function pre_filter_idea( $id ) {
            $this->id = $id;
            $this->where_filters[] = "id = ".$id;
        }

    }
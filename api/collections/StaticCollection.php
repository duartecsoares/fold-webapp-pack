<?php
    requireModel('StaticModel');
    requireModel('Users/StaticUser');

    //**************************************************************
    //
    // Collection (list) of models
    //
    //**************************************************************
    class StaticCollection extends StaticModel {

        public $ModelClass      = 'StaticModel';
        public $models          = array();

        protected $_pull_table  = "users";
        protected $fields       = "*";
        protected $where        = "";
        protected $where_filters = array();

        protected $join         = "";
        protected $group_by     = "";
        protected $order        = "ORDER BY id";
        protected $limit        = "LIMIT 50";
        protected $on_pull_append = true;
        public $max_per_page    = 1000;

        public $valueWhenEmpty  = null;
        public $per_page        = 24;
        public $current_page    = 1;
        public $count           = 0;


        //
        //
        // Fetch mode, what (if needed), classes should be created
        // when creating the list
        //
        //
        protected function getFetchMode( $obj ) {
            return $obj->setFetchMode(PDO::FETCH_CLASS, $this->ModelClass);
        }

        public function toJSON() {
            $models = $this->models;
            return json_encode($this->models);
        }

        public function toArray() {
            global $log;

            $models = $this->models;
            $array = array();

            $log->append('collection_toArray', '------------');
            $log->append('collection_toArray', $array);

            foreach($models as $row => $value) {
                $array[] = $value->get();
            }

            $log->append('collection_toArray', $array);

            return $array;
        }

        public function deleteWhere( $field, $whereValue ) {
            global $log;
            $models = $this->models;
            $index = 0;
            

            foreach($models as $row => $value) {
                $log->append('deleteWhere', '----------------');
                $log->append('deleteWhere', $field);
                $log->append('deleteWhere', $value);
                $log->append('deleteWhere', $row);

                if ( $value[$field] == $whereValue ) {
                    unset($this->models[$index]);
                    $log->append('deleteWhere', 'delete!');
                }

                $index++;
            }

        }

        public function deleteWhereIndex( $index ) {
            global $log;

            $success = false;

            if ( is_numeric($index) ) {

                $models = $this->models;
                $len    = $this->count;    

                $log->append('deleteWhereIndex', '---------------------');
                $log->append('deleteWhereIndex', $len);
                $log->append('deleteWhereIndex', get_class($this));

                if ( $index < $this->models && $index >= 0 && $this->models[$index] ) {

                    $log->append('deleteWhereIndex', $models[$index]);

                    if ( method_exists($this, 'onBeforeDeleteModel') ) {
                        $success = $this->onBeforeDeleteModel( $models[$index] );
                    } else {
                        $success = true;
                    }

                    if ( $success ) {

                        unset($this->models[$index]);
                        
                    }

                    $log->append('deleteWhereIndex', count($this->models));

                }
            }

            return $success;
        }


        public function setPullTable( $str = '') {
            $this->_pull_table = $str;
        }

        public function setFields( $fields ) {
            $this->fields = $fields;
        }
        public function setModel( $model ) {
            $this->ModelClass = $model;
        }

        //
        //
        // by default return the object (all plubic attributes)
        //
        //
        protected function pull_default( $obj ) {
            return $obj;
        }

        public function reset() {
            $this->models   = array();
            $this->count    = 0;
        }

        public function append( $obj ) {
            global $log;
            $this->models[] = $obj;
            $this->count += 1;
        }


        //
        //
        // generate the pull query with filters and pagination
        //
        //
        protected function generatePullQuery( $method = null, $data = null ) {
            global $log;
            
            $query = "";
            $pullMethod = 'generate_query__default';

            if ( is_string($method) ) {
                $pullMethod = 'generate_query_'.$method;
            }

            if ( method_exists($this, $pullMethod) ) {
                $query = $this->$pullMethod( $data );
            } 

            return $query;
        }

        protected function generate_where_filters() {
            $where_filters = implode(' AND ', $this->where_filters);
            $where_filters = ltrim ($where_filters, ' AND');
            return $where_filters;
        }

        protected function generate_query__default( $data ) {
            global $log;
            $where_filters = $this->generate_where_filters();
            $where = strlen($where_filters) > 0 ? $this->where : "";
            $log->append('generate_query__default', strlen($where_filters));
            $query = "SELECT ".$this->fields." FROM ".$this->_pull_table." ".$this->join." ".$where.$where_filters." ".$this->group_by." ".$this->order." ".$this->get_page();
            $log->append('generate_query__default', $query);
            return $query;
        }

        public function set( $data = NULL, $blackList = NULL ) {
            global $log;
            $models = array();
            $log->append('collection_set', '########### '.get_class($this).' ########');
            $log->append('collection_set', $data);

            if ( is_array($data) ) {
               foreach($data as $image => $value) {
                    $log->append('collection_set', '--------------');
                    $log->append('collection_set', $image);
                    $log->append('collection_set', $value);
                    $model = new $this->ModelClass();
                    $model->set( $value );
                    $log->append('collection_set', $model);
                    $models[] = $model;
                }    
            }
            $this->models = $models;
            $this->count = count($models);
        }

        //
        //
        // pull the list from the database and create a list of classes
        //
        //
        public function pull( $onEach = 'default', $method = null, $data = null ) {
            global $pdo, $log;
            $pull_query     = null;
            $success        = false;
            $onEachMethod   = 'pull_'.$onEach;
            $pull_query     = $this->generatePullQuery($method, $data);

            if ( $pull_query != null ) {
                $log->addQuery( $pull_query, null, $this );
                $pull_result = $pdo->query($pull_query);

                if ( !empty($pull_result) && $pull_result->rowCount() > 0 ) {


                    //
                    // reset list
                    //
                    $this->reset();
                    $this->getFetchMode($pull_result);

                    while ($data = $pull_result->fetch() ) {
                        if ( method_exists($this, $onEachMethod) ) {
                            $append = $this->$onEachMethod( $data );
                        } else {
                            $append = $data;
                        }

                        if ( $this->on_pull_append == true ) {
                            $this->append( $append );
                        }
                    }


                    if ( $this->on_pull_append == false ) {
                        $this->models = $append;
                        $this->count = count($append);
                    }

                    $success = true;
                    $this->pullOnNotEmpty($pull_result);
                    
                } else {
                    $this->models = $this->valueWhenEmpty;
                    $success = false;
                    $this->pullOnEmpty($pull_result);
                }

            } else {
                $success = false;
                $this->pullOnError($pull_result);
            }

            return $success;
        }

        protected function add_default( $obj ) {
            return $obj;
        }

        public function add( $array = null, $onEach = 'default' ) {
            $onEachMethod   = 'add_'.$onEach;
            if ( is_array($array) ) {
                foreach($data as $key => $item) {

                    if ( !is_a($item, $this->ModelClass ) && method_exists($this->ModelClass, 'set') ) {
                        $model = new $this->ModelClass();
                        $model->set( $item );
                        $item = $model;
                    }

                    if ( method_exists($this, $onEachMethod) ) {
                        $this->append( $this->$onEachMethod( $item ) );
                    } else {
                        $this->append( $item );
                    }
                }
            }
        }

        protected function get_page() {
            $page = ($this->current_page-1)*$this->per_page;
            return "LIMIT ".$page.", ".$this->per_page;
        }

        public function setPage( $page ) {
            if ( $page < 1 ) {
                $page = 1;
            }
            $this->current_page = $page;
        }

        public function setLimit( $limit ) {
            $this->limit = $limit;
        }

        public function setPerPage( $amount ) {
            if ( $amount > $this->max_per_page ) {
                $amount = $this->max_per_page;
            }
            $this->per_page = $amount;
        }

        public function setMaxPerPage( $amount ) {
            $this->max_per_page = $amount;
        }

        protected function pre_filter_default() {
            $this->where = "";
        }

        public function setOrderBy( $by = 'id', $order = 'ASC' ) {
            if ( $by != false ) {
                $this->order = "ORDER BY ".$by." ".$order;
            } else {
                $this->order = "";
            }
        }


        //
        //
        // prefilters by method
        //
        //
        public function setPreFilter( $filter = 'default', $data = null, $extra = null ) {
            $this->where = "WHERE ";
            $filterMethod = 'pre_filter_'.$filter;
            $this->$filterMethod( $data, $extra );
        }


        //
        //
        // see the models
        //
        //
        public function get( $return = 'default' ) {
            $this->pull( $return );

            if ( method_exists($this, 'onBeforeGet') ) {
                $this->models = array_values( $this->onBeforeGet($this->models) );
            }

            return $this->models;
        }

        public function getModels() {
            return $this->models;
        }

        protected function returnModelWhere( $field, $value ) {
            global $log;
            $models = $this->getModels();
            $return = false;
            foreach($models as $key => $model) {
                if ( $model->$field == $value ) {
                    $return = $model;
                }
            }
            return $return;
        }

        public function getWhere( $field, $values ) {
            global $log;

            $log->append('$values ', $values);

            if ( !is_array($values) ) {
                $valuesArray = explode(",",$values);
            } else {
                $valuesArray = $values;
            }

            $models = $this->valueWhenEmpty;

            if ( is_array($valuesArray) && count($valuesArray) > 0 ) {
                $models = array();

                foreach($valuesArray as $key => $value) {
                    $model = $this->returnModelWhere($field, $value);
                    if ( $model != false ) {
                        $models[] = $model;
                    }
                }

                if ( count($models) == 0 ) {
                    $models = $this->valueWhenEmpty;
                }
            }

            return $models;
        }

        //
        //
        //
        public function join( $field = null, $separateBy = ',') {
            global $log;

            $str        = $this->valueWhenEmpty;
            $models     = $this->getModels();

            if ( is_string($field) && is_array($models) ) {

                $str        = '';
                $last_model = end($models);

                foreach($models as $key => $model) {
                    $str .= $model->$field;
                    if ( $last_model != $model) {
                        $str .= $separateBy;
                    }
                }

                if ( strlen($str) == 0 ) {
                    $str = $this->valueWhenEmpty;
                }

            }

            return $str;
        }

        public function arrayWith($field = null, $separateBy = ',') {
            global $log;

            $array      = $this->valueWhenEmpty;
            $models     = $this->getModels();

            if ( is_string($field) && is_array($models) ) {
                $array = array();

                foreach($models as $key => $model) {
                    $array[] = $model->$field;
                }
            }

            return $array;
        }
    }
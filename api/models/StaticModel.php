<?php
    //**************************************************************
    //
    // V2 Models
    //
    //**************************************************************
    class WhereConditional {
        public $field;
        public $compare = '=';
        public $operator = 'AND';
    }
    
    class StaticModel {

        protected $_db_fields   = '*';
        protected $_db_where    = array('id'=>array('compare'=>' = ', ''));
        protected $_pull_mandatory;

        protected function addWhere( $field = null ) {

        }

        protected function generatePullQuery( $method = null, $data = null ) {
            global $log;
            
            $query = "";
            $pullMethod = 'generate_query__default';

            if ( is_string($method) ) {
                $pullMethod = 'generate_query_'.$method;
            }

            $log->append('generatePullQuery', $pullMethod);

            if ( method_exists($this, $pullMethod) ) {
                
                $query = $this->$pullMethod( $data );

                $log->append('generatePullQuery', true);
                $log->append('generatePullQuery', $query);

            } 

            return $query;
        }

        protected function generate_query__default( $data ) {
            global $log;
            $log->append('generate_query__default', true);
            return "SELECT * FROM ".$this->_pull_table." WHERE id = ".$this->id;
        }

        public function pull( $method = null, $extraData = null ) {
            global $pdo, $log;

            $pull_query = null;
            $success = false;

            $pull_query = $this->generatePullQuery( $method, $extraData );

            if ( $pull_query != null ) {
                $log->addQuery( $pull_query, null, $this );

                $pull_result = $pdo->query($pull_query);

                if ( !empty($pull_result) && $pull_result->rowCount() == 1 ) {

                    while ($data = $pull_result->fetch(PDO::FETCH_OBJ)) {
                        $this->set( get_object_vars($data), isset($this->_pull_blacklist) ? $this->_pull_blacklist : null );
                        $success = true;
                    }

                    $this->pullOnNotEmpty($pull_result);
                    $this->_exists = true;

                } else {
                    $this->pullOnEmpty($pull_result);
                    $success = false;
                    $this->_exists = false;
                }

            }

            return $success;
        }

        public function setPullOnError( $error = 'warning', $st = 400, $id = 1000, $description = 'Bad Request', $details = null ) {
            global $status, $log;
            $status = $st;
            $log->error($error, array('id'=>$id,'description'=>$description, 'details'=>$details.' | '.get_class($this)));
        }


        public function pullOnError( $result ) {
            $this->setPullOnError('error', 400, 1002);
        }

        public function pullOnEmpty() {
            // $this->setPullOnError('warning', 404, 1001, 'No results.');
        }

        public function pullOnNotEmpty( $result ) {
            global $status;
            $status = 200;
        }


        public function exists( $pull = true, $pullType = null ) {
            if ( $pull == true ) {
                $this->pull( $pullType );
            }
            return $this->_exists;
        }

        public function get( $onEach = "" ) {
            $data = $this;
            $onEachMethod = "get_".$onEach;
            $array = array();
            foreach($data as $key => $value) {
                if ( !method_exists($this, $onEachMethod) ) {
                    $array[$key] = $value;
                } else {
                    $array[$key] = $this->$onEachMethod($key, $value);
                }
            }
            return $array;
        }

        //
        // Set the public fields
        //
        public function set( $data = null, $blackList = null ) {
            global $log;

            // $log->append('static_model_set', $data);

            if ( $blackList == null && !empty($this->_set_blacklist) ) {
                $blackList = $this->_set_blacklist;
            } else {
                $blackList = array();
            }

            if ( is_array($data) ) {
                $data = array_diff_key($data,array_flip($blackList));
            }
            
            // $log->append('static_model_set', $data);

            if ( $data ) {
                foreach($data as $key => $value) {
                    $methodName = 'set_'.$key;
                    // $log->append('static_model_set', $key.' > '.$value);

                    if ( !method_exists($this, $methodName) ) {
                        $this->$key = $value;
                    } else {
                        $this->$methodName( $value );
                    }
                }   
            }

        }
    }
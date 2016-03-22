<?php
    requireModel('StaticModel');
    
    function clearNulls( $value ) {
        return ( $value !== null );
    }
    
    //**************************************************************
    //
    // Dynamic Model
    //
    //**************************************************************
    class DynamicModel extends StaticModel {

        protected $_class_blacklist = array(
            '_pull_table',
            '_pull_table',
            '_pull_blacklist',
            '_set_blacklist',
            '_push_table',
            '_push_mandatory',
            '_pull_mandatory',
            '_insert_blacklist',
            '_update_blacklist',
            '_class_blacklist',

            '_networks',
            '_ideasList',

            '_db_fields',
            '_db_where',
            '_following_ids',
            '_liked_ideas_ids'
        );

        //
        //
        // Check Mandatory Fields
        //
        //
        protected function checkMandatory( $mandatory = null, $validate = true ) {
            global $log;

            $success = true;

            $data = get_object_vars($this);
            $data = array_filter($data, 'clearNulls');

            if ( $mandatory == null ) {
                $mandatory = $this->_pull_mandatory;
            }

            if ( $mandatory != null ) {
                foreach($mandatory as $key => $value) {

                    if ( array_key_exists( $value, $data ) ) {

                        $validateMethodName = 'validate_'.$value;

                        if ( $validate == true && method_exists($this, $validateMethodName) ) {
                            if ( $this->$validateMethodName( $data[$value] ) == false ) {
                                $success = false;
                                $this->checkMandatoryOnValidateFalse( $key, $value, $data[$value] );
                            }
                        } else {

                        }

                    } else {
                        $success = false;
                        $this->checkMandatoryOnMissingField( $key, $value, $data[$value] );
                    }

                    if ( $success == false ) {
                        break;
                    }
                }
            }

            // $log->append('checkMandatory', $success);

            return $success;

        }

        protected function checkMandatoryOnValidateFalse( $key, $value, $data) {
            global $log;
            $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Validation Failed ['.$value.']','details'=>'Validation result: '));
        }

        protected function checkMandatoryOnMissingField( $key, $value, $data) {
            global $log;
            $log->error('warning', array('id'=>1002,'description'=>'Bad Request - Missing Field ['.$value.']','details'=>'This value cannot be empty.'));
        }

        //
        //
        // Update data to database
        //
        //
        public function update( $fields = null ) {
            global $status, $log;

            $log->append('update_model', '------ '.get_class($this).' -------');

            // check mandatory
            $success = $this->checkMandatory();
            
            $log->append('update_model', $success);

            $log->append('update_model', $success);

            if ( $success == true ) {
                $success = $this->updateToDB( $fields );

                if ( $success == true ) {
                    $success = $this->onUpdateSuccess( $fields );
                }

            } else {
                $status = 400;
            }

            $log->append('update_model', $success);

            return $success;
        }

        protected function onUpdateSuccess() {
            return true;
        }

        protected function updateToDB( $fields ) {
            global $pdo, $log, $status;

            $success = true;

            $updateObject = $this->generateUpdateSql( null, $fields );

            // $log->append('updateToDB', $updateObject);

            $log->addQuery( $updateObject["query"], $updateObject["values"], $this );

            $sql_prepare = $pdo->prepare($updateObject["query"]);
            $sql_execute = $sql_prepare->execute($updateObject["values"]);

            if ( $sql_execute != true ) {
                $status = 400;
                $success = false;
                $this->updateToDBOnError($updateObject, $sql_prepare);
            }

            return $success;

        }

        protected function updateToDBOnError($updateObject, $sql_prepare) {
            global $log, $pdo;

            $log->append('updateToDBOnError', '----------------------');
            $log->append('updateToDBOnError', $updateObject["query"]);
            $log->append('updateToDBOnError', $updateObject["values"]);
            $log->append('updateToDBOnError', $sql_prepare->errorInfo());

            $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not update '.$this->_push_table.'.','details'=>'Updating into database returned an error.'));
        }

        protected function updateWhere() {
            return " id = ?";
        }

        protected function updateWhereValues() {
            return $this->id;
        }

        // UPDATE ideas SET
        protected function generateUpdateSql( $blackList = null, $fields = null ) {
            global $log;

            if ( $blackList == null && isset($this->_insert_blacklist) ) {
                $blackList = $this->_insert_blacklist;
            } else {
                $blackList = array();
            }

            $query  = "UPDATE ".$this->_push_table." SET ";
            $values = "";
            $where  = " WHERE ".$this->updateWhere();


            $filteredData = array();

            $data = get_object_vars($this);

            $log->append('data_update', $data);

            $data = array_diff_key($data,array_flip($blackList));
            $data = array_diff_key($data,array_flip($this->_class_blacklist));

            $log->append('data_update', $data);

            $data = array_filter($data, 'clearNulls');

            $log->append('data_update', $data);


            if ( is_string($fields) ) {
                $fieldsArray = explode(',',$fields);
                $log->append('generateUpdateSql', $fieldsArray );
                $data = array_intersect_key($data, array_flip($fieldsArray));
            }

            $log->append('data_update', $data);


            //
            // Before Update
            //
            if ( method_exists($this, 'onBeforeUpdate') ) {
                $data = $this->onBeforeUpdate( $data );
            }

            $dataKeys = array_keys($data);
            $last_key = end($dataKeys);

            if ( $data ) {
                foreach($data as $key => $value) {

                    // $log->append('generateUpdateSql', 'key : '.$key);

                    $updateMethodName = 'update_set_'.$key;

                    $values .= " ".$key." = ?";

                    if ( method_exists($this, $updateMethodName) ) {
                        $value = $this->$updateMethodName($value);
                    }

                    $filteredData[] = $value;

                    if ( $key != $last_key ) {
                        $values .= ",";
                    }
                }
            }

            $filteredData[] = $this->updateWhereValues();

            $log->append('generateUpdateSql', $query.$values.$where);
            $log->append('generateUpdateSql', $filteredData);

            return array("query"=>$query.$values.$where, "values"=>$filteredData);
        }
        

        //
        //
        // Push data to database
        //
        //
        public function push() {
            global $log, $status;

            // check mandatory
            $success = $this->checkMandatory( );

            if ( $success == true ) {
                $success = $this->insertInDB();

                if ( $success == true ) {
                    $success = $this->onPushSuccess();
                }

            } else {
                $status = 400;
            }

            return $success;
        } 

        protected function onPushSuccess() {
            return true;
        }

        protected function insertInDB() {
            global $pdo, $log, $status;

            $success = true;

            $insertObject = $this->generateInsertSql();

            $log->addQuery( $insertObject["query"], $insertObject["values"], $this );

            $sql_prepare = $pdo->prepare($insertObject["query"]);
            $sql_execute = $sql_prepare->execute($insertObject["values"]);

            if ( $sql_execute != true ) {
                $status = 400;
                $success = false;
                $this->insertInDBOnError();
            } else {
                $this->id = $pdo->lastInsertId();
            }

            //
            // After Insert
            //  
            if ( method_exists($this, 'onAfterInsert') ) {
                $success = $this->onAfterInsert();
            }

            return $success;

        }

        protected function insertInDBOnError() {
            global $log;
            $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not create '.$this->_push_table.'.','details'=>'Inserting into database returned an error.'));
        }

        protected function generateInsertSql( $blackList = null ) {
            global $log;

            if ( $blackList == null && isset($this->_insert_blacklist) && is_array($this->_insert_blacklist)) {
                $blackList = $this->_insert_blacklist;
            } else {
                $blackList = array();
            }

            $query  = "INSERT INTO ".$this->_push_table;
            $fields = "";
            $values = "";

            $filteredData = array();

            $data = get_object_vars($this);
            $data = array_diff_key($data,array_flip($blackList));
            $data = array_diff_key($data,array_flip($this->_class_blacklist));
            $data = array_filter($data, 'clearNulls');


            //
            // Before Insert
            //  
            if ( method_exists($this, 'onBeforeInsert') ) {
                $data = $this->onBeforeInsert( $data );
            }

            $last_key = end(array_keys($data));

            if ( $data ) {
                foreach($data as $key => $value) {

                    $insertMethodName = 'insert_set_'.$key;

                    if ( strlen($fields) == 0 ) {
                        $fields = "(";
                    }

                    $fields .= " ".$key;

                    if ( strlen($values) == 0 ) {
                        $values = " VALUES (";
                    }

                    $values .= " ?";

                    if ( method_exists($this, $insertMethodName) ) {
                        $value = $this->$insertMethodName($value);
                    }

                    $filteredData[] = $value;

                    if ( $key == $last_key ) {
                        $fields .= " )";
                        $values .= " )";
                    } else {
                        $fields .= ",";
                        $values .= ",";
                    }
                }
            }

            return array("query"=>$query.$fields.$values, "values"=>$filteredData);

        }

        //
        //  DELETE FROM table_name
        //  WHERE some_column = some_value
        //

        //
        //
        // delete from database
        //
        //
        public function delete() {

            global $log, $status;

            if ( method_exists($this, 'onBeforeDelete') ) {
                $sucess = $this->onBeforeDelete();
            } else {
                $sucess = true;
            }

            if ( $sucess == true ) {

                $success = $this->deleteFromDB();

                if ( $success == true ) {
                    $success = $this->onDeleteSuccess();
                }
            }

            return $success;
        } 

        protected function onDeleteSuccess() {
            return true;
        }

        protected function deleteFromDB() {
            global $pdo, $log, $status;

            $success = true;

            $dbObject = $this->generateDeleteSql();

            $log->addQuery( $dbObject["query"], $dbObject["values"], $this );

            $log->append('deleteFromDB', $dbObject["values"] );


            $sql_prepare = $pdo->prepare($dbObject["query"]);
            $sql_execute = $sql_prepare->execute($dbObject["values"]);

            if ( $sql_execute != true ) {
                $status = 400;
                $success = false;
                $this->deleteFromDBOnError();
            }

            return $success;

        }

        protected function deleteFromDBOnError() {
            global $log;
            $log->error('error', array('id'=>1002,'description'=>'Bad Request - Could not delete '.$this->_push_table.'.','details'=>'Deleting database row returned an error.'));
        }

        protected function deleteWhereValues() {
            return $this->id;
        }

        protected function deleteWhere() {
            return " id = ?";
        }

        protected function generateDeleteSql() {
            global $log;

            $query  = "DELETE FROM ".$this->_push_table;
            $where  = " WHERE ".$this->deleteWhere();

            $filteredData = array();

            $appendValues = $this->deleteWhereValues();

            if ( is_array($appendValues) ) {
                $filteredData = array_merge($filteredData, $appendValues);
            } else {
                $filteredData[] = $appendValues;
            }

            return array("query"=>$query.$where, "values"=>$filteredData);

        }

    }
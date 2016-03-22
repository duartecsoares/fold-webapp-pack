<?php
    requireModel('DynamicModel');
    requireModel('Ideas/Traits');

    //**************************************************************
    //
    // Dynamic Idea
    //
    //**************************************************************

    define('MAX_GALLERY', 6);

    class DynamicIdea extends DynamicModel {
        use IdeaTraits;

        function __construct( $user_id = null ) {
            global $log;
            if ( $user_id ) {
                $this->user_id = $user_id;
            }
        }

        protected function generate_query_is_favorite($data = array()){
            global $query, $log;
            $query = "SELECT id, like_count, likes.user_id, ideas.user_id as idea_user_id FROM ".$this->_pull_table.", likes WHERE ideas.id = ".$this->id." AND ideas.id = likes.idea_id AND likes.user_id = ".$data['user_id'];
            return $query;
        }

        public function deleteImageFromGallery( $id ) {
            global $log;

            $galleryObject  = $this->getGalleryObject();
            $gallery        = $galleryObject->models;
            $galleryLen     = count($gallery);
            $success        = $galleryObject->deleteWhereIndex( $id );

            $log->append('deleteImageFromGallery', '----------------');
            $log->append('deleteImageFromGallery', $success);
            $log->append('deleteImageFromGallery', $id.' vs '.$this->id_cover);

            if ( $success ) {   

                $log->append('deleteImageFromGallery', $id == $this->id_cover);


                if ( $id == $this->id_cover ) {
                    //
                    // the cover was deleted
                    //
                    $this->id_cover = 0;

                    $log->append('deleteImageFromGallery', 'same cover! set the first');

                } else {
                    $this->id_cover = $this->id_cover - 1;

                    $log->append('deleteImageFromGallery', 'different covers, set : '.$this->id_cover);

                    if ( $this->id_cover == - 1 && $galleryLen - 1 > 0 ) {
                        $this->id_cover = 0;
                    }

                    $log->append('deleteImageFromGallery', 'different covers, set : changed ? '.$this->id_cover);

                }
                
                $this->gallery = $galleryObject->getStorableData();               
                $success = $this->update('gallery,id_cover');

                $log->append('deleteImageFromGallery', $success);

            }

            return $success;
        }

        //
        // before deleting an idea, delete the likes
        //
        protected function onBeforeDelete() {
            global $log, $pdo, $status;
            //
            // reset counts the likes in the like collection for this idea
            //  
            $query  = "DELETE FROM likes WHERE idea_id = ?";
            $data   = array($this->id);

            $sql_prepare = $pdo->prepare($query);
            $sql_execute = $sql_prepare->execute($data);

            $log->append('onBeforeDelete', $query);
            $log->append('onBeforeDelete', $data);
            $log->append('onBeforeDelete', $sql_execute);

            if ( $sql_execute != true ) {
                $status = 400;
                $success = false;
            } else {
                $status = 200;
                $success = true;
            }

            return $success;

        }

        public function onBeforeUpdate( $data ) {
            global $log;

            $log->append('onBeforeUpdate', '------ BEFORE & AFTER -----' );
            $log->append('onBeforeUpdate', $data );

            if ( is_array($data) ) {

                //
                // set the cover & avatar
                //
                if ( isset($data['id_cover']) && is_numeric($data['id_cover']) ) {
                    $id_cover = $data['id_cover'];

                    $galleryObject = $this->getGalleryObject();
                    $gallery = $galleryObject->getRelativeArray();

                    $log->append('onBeforeUpdate', $gallery);
                    $log->append('onBeforeUpdate', 'id cover: '.$id_cover);
                    $log->append('onBeforeUpdate', 'this->id cover: '.$this->id_cover);

                    if ( isset($gallery[$id_cover]) ) {

                        $fileName       = $gallery[$id_cover]['image'];
                        $ext            = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newFileName    = substr($fileName, 0 , (strrpos($fileName, ".")));

                        $log->append('onBeforeUpdate_cover', $fileName);
                        $log->append('onBeforeUpdate_cover', $ext);
                        $log->append('onBeforeUpdate_cover', $newFileName);

                        $finalFileName  = $newFileName.'_blur.'.$ext;

                        $log->append('onBeforeUpdate_cover', $finalFileName);

                        $data['cover'] = $finalFileName;

                    }
                }

            }

            $log->append('onBeforeUpdate', $data );

            return $data;
        }

        public function appendToGallery($image, $id) {
            global $log;

            $log->append('appendToGallery', '-------------');
            $log->append('appendToGallery', $this->gallery);

            $log->append('appendToGallery', $image);

            $gallery = $this->getGalleryObject('appendToGallery');

            $log->append('appendToGallery', $gallery);

            $gallery->add($image);

            $log->append('appendToGallery', $gallery);

            $this->gallery = $gallery->getStorableData();


            $log->append('appendToGallery', $this->gallery);

            $success = $this->update('gallery');

            $log->append('appendToGallery', $success);

            if ( $success ) {
                $success = $id;
            }

            return $success;
        }

        public function generateCoverBlurFromLocalImage( $image, $folder = '', $fileName, $fileExt ) {
            global $log;
            requireModel('Images/StaticImage');
            $blur = new StaticImage();
            $blur->local_url        = $image->local_url;
            $blur->local_filename   = $image->local_filename;

            $success = $blur->setTask('CoverBlur', array("folder"=>$folder, "filename"=>$fileName.'_blur.'.$fileExt) );
            $success = false;
            // // $blur->blur();
            // // $success = $blur->sendToCDN($fileName.'_blur.'.$fileExt, $folder);

            // $log->append('generateCoverBlurFromLocalImage', $image->toJSON());
            // $log->append('generateCoverBlurFromLocalImage', $folder);
            // $log->append('generateCoverBlurFromLocalImage', $fileName.'_blur.'.$fileExt);

            // if ( $success ) {
            //     $log->append('generateCoverBlurFromLocalImage', '------ sent ------');
            //     $log->append('generateCoverBlurFromLocalImage', $success);
            //     $log->append('generateCoverBlurFromLocalImage', $blur);
            //     $log->append('generateCoverBlurFromLocalImage', $this->hasNoCover());

            //     if ( $this->hasNoCover() ) {
            //         $log->append('generateCoverBlurFromLocalImage', '------ HAS NO COVER ------');
            //         $this->cover = $blur->relative_url;
            //     }
            // }

            return array("success"=>$success, "blur"=>$blur);
        }

        public function generateIconFromGallery( $image ) {
            global $log, $__tempStoragePath__;
            
            $success = false;

            if ( is_object($image) ) {
                //
                // it is an object, retrieve the image and send
                //
                $log->append('generateIconFromGallery', $image->toJSON());
                $success = $image->cdnDuplicateAs('cover');
                $log->append('generateIconFromGallery', $success);


            } else if ( is_numeric($image) ) {
                //
                // its an id / index in the gallery
                //

            }

            $log->append('generateIconFromGallery', 'Final: ');
            $log->append('generateIconFromGallery', $success);

            //
            // store in db
            //
            if ( $success ) { 
            }

            return $success;
        }

        public function hasNoCover() {
            if ( $this->cover ) {
                return true;
            } else {
                return false;
            }
        }

        public function hasNoIcon() {
            if ( $this->id_cover ) {
                return true;
            } else {
                return false;
            }
        }

        public function sendImageToCDN($where = 'gallery', $image = null, $name = null) {
            global $log, $status;

            $success = false;

            if ( $image ) {
                $log->append('uploading_image_to_Gallery', '--------------');
                $log->append('uploading_image_to_Gallery', $image->toJSON());
                $log->append('uploading_image_to_Gallery', $status);

                //
                // variables
                //
                $appendToFilename   = time();
                $folder             = 'images/ideas/'.$this->id.'/';


                if ( $where == 'gallery' ) {

                    //
                    // check current gallery
                    //
                    $galleryObject = $this->getGalleryObject();
                    $gallery = $this->getGallery();
                    $count = count($gallery);

                    if ( !$gallery || $count < MAX_GALLERY ) {
                        //
                        // still available to upload
                        //
                        $status = 200;
                        $fileNameComplete = '';
                        // $newID = $galleryObject->newID();

                        if ( !$name ) {
                            $fileName = 'image_'.$this->generateUrl( $appendToFilename );
                            $fileNameComplete = $fileName.'.'.$image->extension;
                        } else {
                            $fileName = $name;
                            $fileNameComplete = $name;
                        }

                        $log->append('uploading_image_to_Gallery', '------------');
                        $log->append('uploading_image_to_Gallery', $image->toJSON());
                        $log->append('uploading_image_to_Gallery', $fileName);
                        $log->append('uploading_image_to_Gallery', $folder);

                        $log->append('uploading_image_to_Gallery', '--- [SEND TO CLUSTER] ---');

                        $success = $image->setTask('IdeaThumbnail', array("folder"=>$folder, "filename"=>$fileNameComplete, "filenameNoExt"=>$fileName, "extension"=>$image->extension));
                        $log->append('uploading_image_to_Gallery', $success);

                        if ( $success && $success['status'] == 200 ) {
                            $thumbnailResponse = json_decode($success['response']);
                            $log->append('uploading_image_to_Gallery', $thumbnailResponse);
                            $log->append('uploading_image_to_Gallery', $image);
                            $success = true;

                            if ( $success->relative_blur_url ) {
                                $coverBlurSuccess = true;

                                if ( $this->hasNoCover() ) {
                                    $this->cover = $success->relative_blur_url;
                                }
                            } else {
                                $coverBlurSuccess = false;
                            }

            // if ( $success ) {
            //     $log->append('generateCoverBlurFromLocalImage', '------ sent ------');
            //     $log->append('generateCoverBlurFromLocalImage', $success);
            //     $log->append('generateCoverBlurFromLocalImage', $blur);
            //     $log->append('generateCoverBlurFromLocalImage', $this->hasNoCover());

            //     if ( $this->hasNoCover() ) {
            //         $log->append('generateCoverBlurFromLocalImage', '------ HAS NO COVER ------');
            //         $this->cover = $blur->relative_url;
            //     }
            // }

                        } else {
                            $success = false;
                        }
                        
                        // $image->generate_image_thumbnail( null, 'gallery' );
                        // 
                        // $coverBlurSuccess = $this->generateCoverBlurFromLocalImage( $image, $folder, $fileName, $image->extension );
                        // $success = $image->sendToCDN($fileNameComplete, $folder); 

                        $log->append('uploading_image_to_Gallery', $success);

                        //
                        // on success update gallery on database
                        //
                        if ( $success ) {
                            
                            unlink($image->local_url);
                            $image->local_url = null;

                            $image->folder      = $folder;
                            $image->filename    = $fileNameComplete;

                            $imageID = $this->appendToGallery($image, $count);
                            $log->append('uploading_image_to_Gallery', '--- LETS UPDATE THE DATABASE ---');
                            $log->append('uploading_image_to_Gallery', $imageID);
                            $log->append('uploading_image_to_Gallery', $status);
                            $log->append('uploading_image_to_Gallery', is_numeric($imageID) );

                            if ( isset($imageID) && is_numeric($imageID) ) {
                                //
                                // all good! keep goin
                                //

                                $log->append('uploading_image_to_Gallery', '!!! generate icon');
                                $log->append('uploading_image_to_Gallery', $this->hasNoIcon() );
                                $log->append('uploading_image_to_Gallery', $this->icon );

                                if ( $this->hasNoIcon() == false ) {
                                    $iconSuccess = $this->generateIconFromGallery( $image );
                                    if ( $iconSuccess ) {
                                        $this->id_cover = $imageID; // @NOTE CHANGE TO this->icon eventually
                                    }

                                    $log->append('uploading_image_to_Gallery', 'final update');
                                    $log->append('uploading_image_to_Gallery', $iconSuccess);
                                    $log->append('uploading_image_to_Gallery', $coverBlurSuccess);

                                    //
                                    // updates
                                    //
                                    if ( $iconSuccess && $coverBlurSuccess ) {
                                        $this->update('icon,cover,id_cover');
                                    } else if ( $iconSuccess && !$coverBlurSuccess ) {
                                        $this->update('icon');
                                    } else if ( !$iconSuccess && $coverBlurSuccess ) {
                                        $this->update('cover,id_cover');
                                    }

                                }

                                $status = 200;

                            } else {
                                $status = 400;
                            }

                        } else {
                            $status = 400;
                            $log->append('uploading_image_to_Gallery', 'could not upload');
                        }

                    } else {
                        //
                        // reached limit of images, delete one first
                        //
                        $status = 400;
                        $log->error('warning', array('id'=>1070, 'description'=>'Max Reached {6}') );      
                    }
     
                }

                $log->append('uploading_image_to_Gallery', 'Result:');
                $log->append('uploading_image_to_Gallery', $success);
                $log->append('uploading_image_to_Gallery', $status);

            }

            return $success;

        }

        public function favorite( $user_id = null ) {
            global $log;

            requireModel('Likes/LikeIdeaModel');

            $result = false;

            if ( $user_id == null && isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $user_id = $_SESSION['user']->id;

            }

            $like = new LikeIdeaModel();
            $like->setIdea( $this->id );
            $like->setUser( $user_id );
            $result = $like->push();

            if ( $result != false ) {
                $this->like_count++;
            }

            return $result;
        }

        public function unFavorite( $user_id ) {
            global $log;

            requireModel('Likes/LikeIdeaModel');

            $result = false;

            if ( $user_id == null && isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
                $user_id = $_SESSION['user']->id;

            }

            $like = new LikeIdeaModel();
            $like->setIdea( $this->id );
            $like->setUser( $user_id );
            $result = $like->delete();

            if ( $result != false ) {
                $this->like_count--;
            }

            return $result;
        }

        public function updateFavoriteCount( $push = false ) {
            global $log;

            $result = false;

            //
            // reset counts the likes in the like collection for this idea
            //  
            requireCollection('LikesIdeaCollection');

            $likes = new LikesIdeaCollection();
            $likes->setIdea( $this->id );
            $likes->setPerPage( 1000 );
            $result = $likes->pull();

            $this->like_count = $likes->count;

            $log->append('updateFavoriteCount', $likes);
            $log->append('updateFavoriteCount', $push);
            $log->append('updateFavoriteCount', $this->like_count);

            if ( $result != false && $push == true ) {
                $this->update('like_count');
            } 

            return $result;

        }


        public function addView( $pull = true, $update = true ) {

            if ( $pull == true ) {
                $this->pull();
            }

            $this->view_count += 1;

            if ( $update == true ) {
                $this->update('view_count');
            }

        }

        // 
        // being old makes you a bit weaker
        //
        private function calcPopularityTimeFriction( $total ) {
            global $log;

            $log->append('calcPopularity', 'calcPopularityTimeEffect');

            $delta_time = time() - strtotime($this->created_at);
            $hours = floor($delta_time / 3600);
            $delta_time %= 3600;
            $oldEffect = round($hours/24) * 10;
            
            $log->append('calcPopularity', 'init : '.$total);
            $log->append('calcPopularity', 'old time : days : '.round($hours/24));
            $log->append('calcPopularity', 'old time : total :'.$oldEffect);

            //
            // TODO, if a recent update was done, decrease the old effect
            //
            // $delta_time = time() - strtotime($this->updated_at);
            // $hours = floor($delta_time / 3600);
            // $delta_time %= 3600;
            // $multiplier = round($hours/24) * 10;
            // $log->append('calcPopularity', $hours);
            // $log->append('calcPopularity', $hours);

            $minimum = round($total*.3);
            $log->append('calcPopularity', 'minimum 30% of total:'.$minimum);

            if ( $total - $oldEffect < $minimum ) {
                $total = $minimum;
            } else {
                $total = $total - $oldEffect;
            }

            $log->append('calcPopularity', $total);

            return $total;
        }

        public function calcPopularity( $pull = true, $update = true ) {
    		global $log;

            $log->append('calcPopularity', '-IDEA-');

            $total = 0;
            $points = array(
                "idea_type" 	=> 100,
                "status"   		=> 40,
                "like_count"	=> function($count) {
                    return $count*100;
                },
                "view_count"  	=> function($count) {
                    return $count*1;
                },
                "about"    		=> 100,
                "description" 	=> 100,
                "cover"         => 120,
                "looking_for"  	=> 50,
                "is_looking_for"   => 100,
                "extra_info"    => 50,
                "images"        => function($data) {
                    global $log;
                    $count = count($data);
                    return 100*$count;
                },

                "popularity_featured" => function($value) {
                    global $log;
                    return $value;
                },

                // actions
                "twitter"       => 50,
                "website" 		=> 50,
                "traits"      	=> 40
            );

            if ( $pull == true ) {
                $this->pull();
            }

            $data = $this->getAll();


            //
            // calculate points for each and sum
            //
            foreach($data as $key => $value) {

                if ( !empty($points[$key]) && !empty($value) ) {
                    $add = 0;
                    if ( is_callable($points[$key]) ) {
                        $add = $points[$key]( $value );
                        $log->append('idea_calcPopularity', 'add from method : '.$key.' : '.$value.' = '.$add);

                    } else if ( is_numeric($points[$key]) ) {
                        $add = $points[$key];
                        // $log->append('idea_calcPopularity', 'add : '.$key.' : '.$add);
                    }

                    $total += $add;
                }
            }

            //
            // old effect:
            //

            $this->popularity_alltime = $this->calcPopularityTimeFriction( $total );

            if ( $update == true ) {
                $this->update('popularity_alltime');
            }

            return $total;
        }

        protected function array_of_fields( $data, $field = 'id' ) {
            global $log;
            $str = "";
            if( is_array($data) ) {
                foreach ($data as $item) {
                    $str .= $item[$field];
                    if ( $item !== end($data) ) {
                        $str .= ",";
                    }
                }
            } else if( is_string($data) ) {
                $str = $data;
            }
            return $str;
        }

        protected function process_types( $data ) {
            return $this->array_of_fields($data);
        }

        protected function process_traits( $data ) {
            return $this->array_of_fields($data);
        }

        protected function insert_set_traits( $traits ) {
            return $this->process_traits( $traits );
        }

        public function update_set_traits( $traits ) {
            global $log;
            return  $this->process_traits( $traits );
        }

        protected function insert_set_idea_type( $traits ) {
            return $this->process_types( $traits );
        }

        public function update_set_idea_type( $traits ) {
            global $log;
            $data = $this->process_types( $traits );
            return  $data;
        }

        // //
        // // process set icon / cover
        // // 
        // public function process_cover( $value ) {
        //     global $log;

        //     $log->append('process_cover', $value);
            
        //     $this->pull('gallery');

        //     $galleryObject = $this->getGalleryObject();
        //     $gallery = $galleryObject->getRelativeArray();
        //     $result = null;
        //     $count = count($gallery);

        //     $log->append('process_cover', $count);
        //     $log->append('process_cover', $value);

        //     $log->append('process_cover', $gallery);
        //     $log->append('process_cover', $gallery[$value]);

        //     $log->append('process_cover', '-----------------');
        //     $log->append('process_cover', $count > 0);
        //     $log->append('process_cover', $count >= $value );

        //     if ( $count > 0 && $count >= $value ) {
        //         $image = $gallery[$value];

        //         if ( $image && $image['image'] ) {
        //             $result = $image['image'];
        //         }

        //         $log->append('process_cover', '> '.$result);
        //     }

        //     $newFileName = substr($result, 0 , (strrpos($result, ".")));

        //     $result = $newFileName.'_blur.png';

        //     $log->append('process_cover', '---> '.$result);
        //     $log->append('process_cover', '---> '.$newFileName);

        //     return $result;
        // }

        // protected function insert_set_cover( $value ) {
        //     return $this->process_cover( $value );
        // }

        // public function update_set_cover( $value ) {
        //     global $log;
        //     $log->append('update_set_cover', $value);
        //     return  $this->process_cover( $value );
        // }

        //
        // Looking for
        //

        protected function insert_set_is_looking_for( $value ) {
            return $value == true || $value == 1 || $value == "1" ? 1 : 0;
        }

        public function update_set_is_looking_for( $value ) {
            return $value == true || $value == 1 || $value == "1" ? 1 : 0;
        }
	}
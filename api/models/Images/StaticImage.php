<?php

    requireConfig('sizes');

    class StaticImage {

    	public $id;
    	public $description;
    	public $url;

        public $relative_url = ''; 
    	public $cdn_url;
        public $original_url;
        public $local_url;
        public $image;
        public $folder;
        public $filename;

        public $extension;


        public function toJSON() {
            return get_object_vars($this);
        }

        public function getRelative() {

        }

        public function task_CoverBlur( $data ) {
            global $log;

            $log->append('task_CoverBlur', $this);
            $log->append('task_CoverBlur', $data);

            $params = array(
                    "download"=>"tmp/".$this->local_filename
                );

            $this->folder = $data['folder'];
            $this->filename = $data['filename'];

            $params = array_merge($params, $data);

            return $this->processOnCluster('idea-blur', $params);
        }

        public function task_UserThumbnail( $data ) {
            global $log;

            $log->append('task_UserThumbnail', $this);
            $log->append('task_UserThumbnail', $data);

            $params = array(
                    "download"=>"tmp/".$this->local_filename,
                    "type" => "avatar"
                );

            $this->folder = $data['folder'];
            $this->filename = $data['filename'];

            $params = array_merge($params, $data);

            $log->append('task_UserThumbnail', $params);
            
            return $this->processOnCluster('idea-thumbnail', $params);
        }

        public function task_IdeaThumbnail( $data ) {
            global $log;

            $log->append('task_IdeaThumbnail', $this);
            $log->append('task_IdeaThumbnail', $data);

            $params = array(
                    "download"=>"tmp/".$this->local_filename
                );

            $this->folder = $data['folder'];
            $this->filename = $data['filename'];

            $params = array_merge($params, $data);

            return $this->processOnCluster('idea-thumbnail', $params);
        }

        public function setTask( $task, $data ) {
            $success = false;
            $method = 'task_'.$task;
            if ( method_exists($this, 'task_'.$task) ) {
                $success = $this->$method($data);

                if ( $success && $success['status'] == 200 ) {
                    $response = json_decode( $success['response'] );

                    if ( $response ) {
                        if ($response->cdn_url) {
                            $this->cdn_url = $response->cdn_url;
                            $this->image = $this->folder.$this->filename;
                        }
                    }

                }

            }
            return $success;
        }


        public function processOnCluster( $what = 'gallery', $data ) {
            global $log;

            $log->append('ClusterImage', '- Init -------------------');

            //
            // send file to cluster
            // 
            requireFunction('ClusterTask');

            $ClusterImage = new ClusterTask();   

            $processWhat = 'biwm-image-'.$what;

            $ClusterImage->set($processWhat, $data, 'POST');

            $log->append('ClusterImage', $ClusterImage);

            $success = $ClusterImage->run();

            $log->append('ClusterImage', $success);

            return $success;

        }

        public function moveUploadedImage( $file ) {

            global $status, $log, $__tempStoragePath__;

            requireFunction('generateRandomChars');

            $FILELIMIT      = 6000000;
            $success        = true;
            $tmpFilename    = time().'-'.generateRandomChars(10, false);

            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if ( !isset($file['error']) || is_array($file['error']) ) {
                $success = false;
                $log->error('error', array('id'=>1050,'description'=>'Image Upload - Invalid parameters.'));
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $success = false;
                    $log->error('error', array('id'=>1051,'description'=>'Image Upload - No File.'));
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $success = false;
                    $log->error('error', array('id'=>1052,'description'=>'Exceeded filesize limit.'));
                default:
                    $success = false;
                    $log->error('error', array('id'=>1053,'description'=>'Unknown errors.'));
            }

            // You should also check filesize here. 
            if ($file['size'] > $FILELIMIT) {
                $success = false;
                $log->error('error', array('id'=>1054,'description'=>'Exceeded filesize limit.'));
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($file['tmp_name']),
                array(
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    // 'gif' => 'image/gif',
                ),
                true
            )) {
                $success = false;
                $log->error('error', array('id'=>1055,'description'=>'Invalid file format.'));
            }

            if ( $success == false ) {
                $log->error('error', array('id'=>1055,'description'=>'Invalid file format.'));
                $status = 400;
            }

            $mime = $finfo->file($file['tmp_name']);
            $ext = 'jpg';
            if ( $mime = 'image/png') {
                $ext = 'png';
            } else if ( $mime = 'image/jpeg') {
                $ext = 'jpg';
            }
            $tmpFilename .= '.'.$ext;

            $log->append('file_type', $tmpFilename);
            $log->append('file_type', $finfo->file($file['tmp_name']));

            // $tmp_name = $_FILES["pictures"]["tmp_name"][$key];
            // $name = $_FILES["pictures"]["name"][$key];
            // move_uploaded_file($tmp_name, "$uploads_dir/$name");
            // $name = $_FILES["pictures"]["name"].''
            
            if ( $success == true ) {
                $success = move_uploaded_file($file['tmp_name'], $__tempStoragePath__.'/'.$tmpFilename);
                if ( $success ) {
                    $log->append('moveUploadedImage', '----------------------------');
                    $log->append('moveUploadedImage', $__tempStoragePath__.'/'.$tmpFilename);
                    $log->append('moveUploadedImage', $tmpFilename);
                    $log->append('moveUploadedImage', $ext);

                    $this->local_url = $__tempStoragePath__.'/'.$tmpFilename;
                    $this->local_filename = $tmpFilename;
                    $this->extension = $ext;
                    $status = 200;
                }
            } else {
                $log->error('error', array('id'=>1055,'description'=>'Invalid file format.'));
                $status = 400;
            }

            return $success;
        }

        public function set( $path ) {
            global $__cdn__, $log;

            if ( is_object($path) ) {
                $path = $path->image;
            } else if ( is_array($path) ) {
                $path = $path['image'];
            }

            $url = parse_url( $path );
            $cdn_url = null;
            $relative = null;
            if( isset($url['scheme']) && ($url['scheme'] == 'https' || $url['scheme'] == 'http')) {
                if (strpos( $path, $__cdn__) !== false) {
                    $relative = str_replace($__cdn__, "", $path);
                    $cdn_url = $path;
                }
            } else {
                $relative = $path;
            }

            $this->relative_url = $relative;
            $this->image        = $relative;
            $this->cdn_url      = $cdn_url;
        }

        public function setURL( $path ) {

            global $__cdn__, $log;

            $url = parse_url( $path );
            $relative = null;
            $cdn_url = null;

            $log->append('setURL', $path);

            // full path
            if( $url['scheme'] == 'https' || $url['scheme'] == 'http') {
                if (strpos( $path, $__cdn__) !== false) {
                    $relative = str_replace($__cdn__, "", $path);
                    $cdn_url = $path;
                }
            } else {
                $cdn_url = $__cdn__.$path;
            }


            $log->append('setURL', $cdn_url);

            $this->relative_url = $relative;
            $this->image        = $relative;
            $this->cdn_url      = $cdn_url;

            $this->original_url = $path;
            $this->url          = $path;

        }

        public function downloadImage( $url, $filename = null, $extension = null ) {
            global $log, $__tempStoragePath__;

            $dirFile = dirname(__FILE__);
            include_once $dirFile . "/../functions/generateRandomChars.php";

            if ( !$filename ) {
                $filename = time().'-'.generateRandomChars(10, false);
            }
            
            $ext = pathinfo($url, PATHINFO_EXTENSION);

            // $log->append('download_image', $ext);

            if ( $extension ) {
                $filename.=$extension;
                $ext = $extension;
            }

            if ( !$ext || strlen($ext) == 0 ) {
                $ext = 'jpg';
            }

            $target = $__tempStoragePath__.$filename.'.'.$ext;

            $content = @file_get_contents($url);
            
            if($content === FALSE) {
            
                $target = false;

            } else {

                file_put_contents($target, $content);

                if(@is_array(getimagesize($target))) {

                    $this->extension = $ext;
                    $this->local_filename = $filename.'.'.$ext;

                    $this->url              = $url;
                    $this->original_url     = $url;
                    $this->local_url        = $target;
                } else {
                    unlink($target);
                    $this->local_url = null;
                    return false;
                }
            }

            return $target;
        }

        public function getRelativePath( ) {
            return $this->folder.$this->filename;
        }

        public function cdnDuplicateAs( $filename ) {
            global $log, $__cdnFolder__;

            $req = requireFunction('connectS3');

            $log->append('cdnDuplicateAs', $this->toJSON());

            $from   = $__cdnFolder__.$this->getRelativePath();
            $to     = $__cdnFolder__.$this->folder.$filename.'.'.$this->extension;

            $vars = get_object_vars($this);

            $log->append('cdnDuplicateAs', $vars);

            $log->append('cdnDuplicateAs', 'Folder : '.$this->folder);
            $log->append('cdnDuplicateAs', 'Filename : '.$this->filename);

            $log->append('cdnDuplicateAs', $from);
            $log->append('cdnDuplicateAs', $to);

            $cdn = new ConnectS3();
            $success = $cdn->copyTo( $from, $to );
            $log->append('cdnDuplicateAs', $success);

            return $success;
        }

        public function deleteFromCDN( $file ) {
            global $log;

            $filename = is_string($file) ? $file : ( is_string($this->relative_url) ? $this->relative_url : null);
            $success = false;

            $log->append('deleteFromCDN', $filename);

            if ( $filename ) {

                $req = requireFunction('connectS3');
                $cdn = new ConnectS3();
                $success = $cdn->delete( $filename );
                $log->append('deleteFromCDN', $success);
            }

            return $success;

        }

        public function setLocal($filename) {
            global $log, $__tempStoragePath__;

            $this->local_url = $__tempStoragePath__.'/'.$filename;

            $ext = pathinfo($this->local_url, PATHINFO_EXTENSION);

            if ( !$ext || strlen($ext) == 0 ) {
                $ext = 'jpg';
            }

            $this->extension = $ext;

            return $this->local_url;
        }

        public function sendToCDN( $filename, $folder = '', $delete = true ) {
            global $log;

            $req = requireFunction('connectS3');

            $log->append('sendToCDN', $filename);
            $log->append('sendToCDN', $this->local_url);
            $log->append('sendToCDN', $folder);

            $cdn_url = false;

            if ( $this->local_url ) {
                $cdn = new ConnectS3();
                $cdn_url = $cdn->uploadFile( $this->local_url, $folder, $filename );
            }

            $log->append('sendToCDN', $cdn_url);

            if ($cdn_url) {
                $this->relative_url     = $cdn->relative_url;
                $this->image            = $cdn->relative_url;
                $this->cdn_url          = $cdn_url;
                $this->url              = $cdn_url;

                if ( $delete == true ) {
                    unlink($this->local_url);
                    $this->local_url = null;
                }
            }

            return $cdn_url;

        }

        public function retrieve( $url, $filename = null, $folder = '', $delete = true ) {
            $this->downloadImage( $url );  
            $cdn_url = $this->sendToCDN( $filename, $folder);

            return $cdn_url;
        }

        //
        // http://salman-w.blogspot.pt/2008/10/resize-images-using-phpgd-library.html
        //
        public function generate_image_thumbnail($source_image_path = null, $size = 'avatar') {
            global $log, $__tempStoragePath__;

            $source_image_path = $this->local_url;
            // $thumbnail_image_path = $__tempStoragePath__.'/resized/';

            if ( file_exists($source_image_path) ) {

                list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
                switch ($source_image_type) {
                    case IMAGETYPE_GIF:
                        $source_gd_image = imagecreatefromgif($source_image_path);
                        break;
                    case IMAGETYPE_JPEG:
                        $source_gd_image = imagecreatefromjpeg($source_image_path);
                        break;
                    case IMAGETYPE_PNG:
                        $source_gd_image = imagecreatefrompng($source_image_path);
                        break;
                }
                if ($source_gd_image === false) {
                    return false;
                }
                $source_aspect_ratio = $source_image_width / $source_image_height;


                if ( $size == 'avatar' ) {

                    $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
                    if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
                        $thumbnail_image_width = $source_image_width;
                        $thumbnail_image_height = $source_image_height;
                    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                        $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
                        $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
                    } else {
                        $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
                        $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
                    }

                    $quality = AVATAR_QUALITY;

                } else if ( $size == 'gallery' ) {

                    $thumbnail_aspect_ratio = GALLERY_IMAGE_MAX_WIDTH / GALLERY_IMAGE_MAX_HEIGHT;
                    if ($source_image_width <= GALLERY_IMAGE_MAX_WIDTH && $source_image_height <= GALLERY_IMAGE_MAX_HEIGHT) {
                        $thumbnail_image_width = $source_image_width;
                        $thumbnail_image_height = $source_image_height;
                    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                        $thumbnail_image_width = (int) (GALLERY_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
                        $thumbnail_image_height = GALLERY_IMAGE_MAX_HEIGHT;
                    } else {
                        $thumbnail_image_width = GALLERY_IMAGE_MAX_WIDTH;
                        $thumbnail_image_height = (int) (GALLERY_IMAGE_MAX_WIDTH / $source_aspect_ratio);
                    }

                    $quality = GALLERY_QUALITY;

                }

                $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                
                //imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                imagejpeg($thumbnail_gd_image, $source_image_path, $quality);

                imagedestroy($source_gd_image);
                imagedestroy($thumbnail_gd_image);

                return true;

            } else {

                return false;
            }
        }

        // http://stackoverflow.com/questions/20261869/php-implementation-of-stackblur-algorithm-available/20264482#20264482
        public function blur( $times = 4, $resize = false ) {
            global $log, $__tempStoragePath__;


            $source_image_path = $this->local_url;
            $target_image_path = $__tempStoragePath__.'blur-'.$this->local_filename;

            $log->append('blurin', '-----------------');
            $log->append('blurin', $times);
            $log->append('blurin', $source_image_path);
            $log->append('blurin', $target_image_path);
            $log->append('blurin', $this->local_filename);

            if ( file_exists($source_image_path) ) {

                list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
                switch ($source_image_type) {
                    case IMAGETYPE_GIF:
                        $source_gd_image = imagecreatefromgif($source_image_path);
                        break;
                    case IMAGETYPE_JPEG:
                        $source_gd_image = imagecreatefromjpeg($source_image_path);
                        break;
                    case IMAGETYPE_PNG:
                        $source_gd_image = imagecreatefrompng($source_image_path);
                        break;
                }

                if ( $resize == true ) {

                    $thumbnail_aspect_ratio = THUMBNAILBLUR_IMAGE_MAX_WIDTH / THUMBNAILBLUR_IMAGE_MAX_HEIGHT;
                    if ($source_image_width <= THUMBNAILBLUR_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAILBLUR_IMAGE_MAX_HEIGHT) {
                        $thumbnail_image_width = $source_image_width;
                        $thumbnail_image_height = $source_image_height;
                    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                        $thumbnail_image_width = (int) (THUMBNAILBLUR_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
                        $thumbnail_image_height = THUMBNAILBLUR_IMAGE_MAX_HEIGHT;
                    } else {
                        $thumbnail_image_width = THUMBNAILBLUR_IMAGE_MAX_WIDTH;
                        $thumbnail_image_height = (int) (THUMBNAILBLUR_IMAGE_MAX_WIDTH / $source_aspect_ratio);
                    }                    

                    $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);

                    $image = $thumbnail_gd_image;
                } else {
                    $image = $source_gd_image;
                }


                // $log->append('blurin', $source_image_width.' | '.$source_image_height.' | '.$source_image_type);
                $devider = $times+1;

                //
                //
                // Resizing the image two times and bluring at every step
                // gives better results
                //
                //
                /* Scale by 25% and apply Gaussian blur */
                $s_img1 = imagecreatetruecolor($source_image_width/$devider, $source_image_height/$devider);
                imagecopyresampled($s_img1, $image, 0, 0, 0, 0, $source_image_width/$devider, $source_image_height/$devider, $source_image_width, $source_image_height);
            

                for( $i = 0; $i < $times; $i++ ) {
                    imagefilter($s_img1, IMG_FILTER_GAUSSIAN_BLUR);
                    
                    //##########
                    // sleep(0.1);

                    imagefilter($s_img1, IMG_FILTER_SMOOTH, -4);
                }

                //
                // resize up a bit and blur
                //
                $deviderhalf = round($times/2);
                $s_img2 = imagecreatetruecolor($source_image_width/$deviderhalf, $source_image_height/$deviderhalf);
                imagecopyresampled($s_img2, $s_img1, 0, 0, 0, 0, $source_image_width/$deviderhalf, $source_image_height/$deviderhalf, $source_image_width/$devider, $source_image_height/$devider);
                
                //##########
                // sleep(0.25);

                for( $i = 0; $i < $times; $i++ ) {
                    imagefilter($s_img2, IMG_FILTER_GAUSSIAN_BLUR);
                    
                    //##########
                    // sleep(0.1);
                    
                    imagefilter($s_img2, IMG_FILTER_SMOOTH, -4);
                }

                $last = $s_img2;

                //##########
                // sleep(0.25);

                //
                // finaly resize to a bigger image than the original and blur a bit more
                //
                $final = imagecreatetruecolor($source_image_width*1.5, $source_image_height*1.5);
                imagecopyresampled($final, $last, 0, 0, 0, 0, $source_image_width*1.5, $source_image_height*1.5, $source_image_width/$deviderhalf, $source_image_height/$deviderhalf);
                imagedestroy($last);   
                for( $i = 0; $i < round($times/2); $i++ ) {

                    //##########
                    // sleep(0.25);
                    
                    imagefilter($final, IMG_FILTER_GAUSSIAN_BLUR);
                    
                    //##########
                    // sleep(0.1);

                    imagefilter($final, IMG_FILTER_SMOOTH, -4);
                }

                $log->append('blurin', $target_image_path);

                imagejpeg($final, $target_image_path, 90);
                $this->local_url = $target_image_path;

                $log->append('blurin', $this->local_url);

                return true;
            } else {
                return false;
            }

        }

        //
        // Todo, NOT WORKING
        //
        public function resize( $width = 400, $height = 400 ) {
            // $source_image_path = $this->local_url;
            // list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
            // switch ($source_image_type) {
            //     case IMAGETYPE_GIF:
            //         $source_gd_image = imagecreatefromgif($source_image_path);
            //         break;
            //     case IMAGETYPE_JPEG:
            //         $source_gd_image = imagecreatefromjpeg($source_image_path);
            //         break;
            //     case IMAGETYPE_PNG:
            //         $source_gd_image = imagecreatefrompng($source_image_path);
            //         break;
            // }
            // $source_aspect_ratio = $source_image_width / $source_image_height;
            // $thumbnail_aspect_ratio = $width / $height;
        
            // $thumbnail_image_width = $width;
            // $thumbnail_image_height = (int) ($width / $source_aspect_ratio);
            // $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
            // imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width*2, $thumbnail_image_height*2, $source_image_width, $source_image_height);
        }

    }
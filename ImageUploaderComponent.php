<?php

App::uses('Component', 'Controller');
  
class ImageUploaderComponent extends Component { 
 //Load the component we need
 
 /*
  * Loading the component with parameters
  * parameters is an array('extensions'=>array('with','the allowed','extensions'),
  * 'sizeLimit'=> allowed size (in bytes),
  * 'overwrite'=> boolean to allow a file to be overwritten,
  * 'custom_name' => custom name for the uploaded file
  * 
  * )
  * */
 public $components = array('FileUploader'=>array('extensions'=>array('jpeg','gif','bmp','jpg','png'),'sizeLimit'=>5242880));
 
 
 
 /**
 * Called before the Controller::beforeFilter().
 *
 * @param Controller $controller Controller with components to initialize
 * @return void
 * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::initialize
 */
  public function initialize($controller) {
    
  }

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::startup
 */
  public function startup($controller) {
  }

/**
 * Called before the Controller::beforeRender(), and before 
 * the view class is loaded, and before Controller::render()
 *
 * @param Controller $controller Controller with components to beforeRender
 * @return void
 * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRender
 */
  public function beforeRender($controller) {
  }

/**
 * Called after Controller::render() and before the output is printed to the browser.
 *
 * @param Controller $controller Controller with components to shutdown
 * @return void
 * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::shutdown
 */
  public function shutdown($controller) {
  }

/**
 * Called before Controller::redirect().  Allows you to replace the url that will
 * be redirected to with a new url. The return of this method can either be an array or a string.
 *
 * If the return is an array and contains a 'url' key.  You may also supply the following:
 *
 * - `status` The status code for the redirect
 * - `exit` Whether or not the redirect should exit.
 *
 * If your response is a string or an array that does not contain a 'url' key it will
 * be used as the new url to redirect to.
 *
 * @param Controller $controller Controller with components to beforeRedirect
 * @param string|array $url Either the string or url array that is being redirected to.
 * @param integer $status The status code of the redirect
 * @param boolean $exit Will the script exit.
 * @return array|null Either an array or null.
 * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRedirect
 */
  public function beforeRedirect( $controller, $url, $status = null, $exit = true) {
  }
  
  
   /*
      Function to create thumbnails
    * @param string $file_name is the name of the file
    * @param string $file_path is the path where the file will be stored
    * @param array $options('max_width'=>,'max_height'=>,'upload_dir'=>)
    * return Boolean true if created false otherwise
    */
  public function create_scaled_image($file_name, $file_path, $options) {
    $new_file_path = $options['upload_dir'].'/'.$file_name;
    
    if(!is_dir($options['upload_dir'])){
      mkdir($options['upload_dir'], 0755, true);
    }
    
    list($img_width, $img_height) = @getimagesize($file_path);
    
    if (!$img_width || !$img_height)return false;
    
    $scale = min(
        $options['max_width'] / $img_width,
        $options['max_height'] / $img_height
    );
    
	if ($scale > 1) $scale = 1;
    
	$new_width = $img_width * $scale;
    $new_height = $img_height * $scale;
    $new_img = @imagecreatetruecolor($new_width, $new_height);
        
    switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                break;
				
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                break;
				
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                break;
				
            default:
                $src_img = $image_method = null;
        }
        
    $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path);
		
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        
    return $success;
    }
  
 /*
  * function upload, calls the fileuploader component and upload the image
  * 
  * @param array $formData('name' => , 'type' => ,'tmp_name' => ,'error' => ,'size' =>)
  * @param string $path is the destination where the file will be uploaded
  *
  * @param array $options('thumbnail'=>array("max_width"=>180,"max_height"=>100, "path"=>'file/path/for/thumbnail/', "custom_name")
  * 					  'max_width'=>
  * 					  'custom_name'=>)
  * 
  * @return array('bool'=>).
  * 
  */
  
  public function upload($formData, $path=null,$options=array('custom_name'=>null, 'thumbnail'=>null, 'max_width'=>null)){
   
   /*
    * set the Create the destination folder variable to true if it doesn't exist the folder will be created
    * */
    $this->FileUploader->create_destination(TRUE);
    $this->FileUploader->setDestination($path);
    
	/*
	 * check in the options if there's a custom name for our file
	 * if true then set the file name using the File Uploader component
	 * 
	 * */
    if(!empty($options['custom_name'])) $this->FileUploader->setFilename($options['custom_name']);
    
	/*
	 * check in the options if a thumbnail of the uploaded picture should be created
	 * if true set the $thumbsize variable with the options' data 
	 */
	 $thumbnail = (!empty($options['thumbnail']))? $options['thumbnail'] : FALSE;
	
	
	/*
	 *	sometimes you'll upload pictures that are too (WAY TOO BIG ie : 2000 pixel wide and 5000 pixel tall),
	 *  if a maximum width is specified, the picture will be rescaled by the $max_width 
	 *
	 */
	 $max_width = (!empty($options['max_width']))? $options['max_width'] : FALSE;
	 
	/*
	 * We first upload the file as is,
	 * then if a maximum width has been specified then the image will be rescaled and name [filename]_$max_width.[file extension]
	 * then if you need to create a thumbnail for this image, a thumbnail will be created using the $thumbsize data
	 */
	 $current_filePath = null;
   
  
  	 /*
	  * 
	  * Tada this is where the magic happens this function is called and if anything goes wrong an error will be thrown
	  * 
	  * */
     $this->FileUploader->upload($formData,$path);
          
     
     $fullFilename = $this->FileUploader->getFilename();
     $current_destination = $this->FileUploader->getDestination();
		  
     $current_filePath = $current_destination.$fullFilename;
		  
     //we get the following information for our image
     list($width, $height, $type, $attr) = getimagesize($current_filePath);
          
     /*
	  * 
	  * We check if a max_width was specified then rescale it
	  * 
	  * */
     $new_filePath=null;
     if($max_width !== FALSE && $width > $max_width){
              
       $new_filename=time().'_'.$max_width.'_'.$fullFilename;
              
       $max_height = ($max_width * $height) / $width;
              
       $new_filePath = ($this->create_scaled_image($new_filename,$current_filePath, array("upload_dir"=>$current_destination, "max_width"=>$max_width,"max_height"=>$max_height))) ? $current_destination.$new_filename : null;
                
	 }
      
	  /*
	   * 
	   * Create a thumbnail if necessary
	   * 
	   * */    
     $thumbnail_path=null;
     if($thumbnail !== FALSE){
    	
		 $thumbnail_filename = (!empty($thumbnail['custom_name']))? $thumbnail['custom_name'] : time().'_thumbnail_'.$fullFilename;
    			  
    	 $thumbnail_path = ($this->create_scaled_image($thumbnail_filename, $current_filePath, array("upload_dir"=>$thumbnail['path'], "max_width"=>$thumbnail['max_width'],"max_height"=>$thumbnail['max_height'])))? $thumbnail['path'].$thumbnail_filename  : null;
     }
		  
      /*
      * If we get to this point then no errors has been thrown we return the result in the form of an array
      * 
      * file_path => this is the original file uploaded
      * file_path_max_width => this is the file path after the resizing if a max_width was specified
      * thumb_path => this is the file path of the thumbnail if thumbs options have been specified
      * 
      */
      
      return array('bool'=>TRUE,'file_path'=>$current_filePath,'file_path_max_width'=>$new_filePath,'thumb_path'=>$thumbnail_path);
          
    
  }
 
} 


?>
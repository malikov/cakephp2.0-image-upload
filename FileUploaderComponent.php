<?php

App::uses('Component', 'Controller');

class FileUploaderComponent extends Component {
  
  /* constant for error types
   * @var integer
   */
 const SUCCESS = 0;
 
 const FILESIZE_EXCEED_SERVER_MAX = 1;

 const FILESIZE_EXCEED_FORM_MAX = 2;

 const PARTIAL_UPLOAD = 3;

 const NO_FILE_UPLOAD = 4;

 const NO_DIRECTORY_FOR_UPLOAD = 6;

 const SERVER_WRITE_FAIL = 7;
    
 const FILESIZE_EXCEEDS_CODE_MAX = 98;

 const FILE_FORMAT_NOT_ALLOWED = 99;

 const DESTINATION_NOT_AVAILABLE = 100;
 
 /*
  * variables (self explanatory)
  * */
 private $allowedExtensions = array();
 
 /* the size limit by default is basicaly 1 MB = 1 * 1024 * 1024
  * so if you want to change it while loading this component instantiate it with the value of your choice x MB = x * 1024 *1024  
  * */
 private $sizeLimit = 10485760;
 
 /*
  * UploadFile $uploadedFile is of type of the class created bellow
  *  
  * */
 private  $uploadedFile=null;
 
 /*
  * filename is a string
  * */
 private $filename=null;
 
 /*
  * path of where to store the file 
  * */
 private $destination = null;
 
 /*
  * 
  * */
 private $content_only = false;

 private $create_destination = true;
 private $overwrite = true;
 
 
 public function __construct(ComponentCollection $collection, $settings = array()) {
    parent::__construct($collection, $settings);
    
    if (!empty($settings['extensions'])) $this->allowedExtensions =  array_map("strtolower",$settings['extensions']);
    if (!empty($settings['sizeLimit'])) $this->sizeLimit = $settings['sizeLimit'];
    if(!empty($settings['custom_name'])) $this->filename = $settings['custom_name'];
	(!empty($settings['overwrite']))? $this->overwrite($settings['overwrite']) : $this->overwrite(false); 
 
 }
 
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
   * This function checks if your server settings are ok regarding the maximum size a file has to be in order to be uploaded to your server
   * you can use this function if you want to check the settings or can check you .ini file on your php server
   * 
   * */
  public function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
          $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
          die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
      }
  
  /*
   * function used in the checkServerSettings function
   * 
   * */
  public function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
          case 'g': $val *= 1024;
          case 'm': $val *= 1024;
          case 'k': $val *= 1024;        
        }
        return $val;
      }
  
   
   /*
   *
   *	getter function   
   * 
   * */  
  public function getFilename(){
    return $this->filename;
  }
  
  /**
   * set a custom name to use for the final uploaded file
   * if not set then the original name of the uploaded file is used
   * @param string $name
   * 
   */
  public function setFilename($name) {
    $this->filename = $name;
  }
  
  
  /**
   * set the destination path for the uploaded file
   * @param string $path
   * @return UploadComponent
   */
   public function setDestination($path) {
      // add trailing slash if there isn't one
      $last_char = substr($path, -1);
      if ($last_char !== '/') $path .= '/';
  
      $this->destination = $path;
  
      return $this;
    }
    
    /*
	   * getter function
     * 
     */
     public function getDestination(){
       return $this->destination;
     }
  
	  /*
	   *  set the allowed extensions for this upload
	   *  @param string $path
	   *  @return UploadComponent
	   * 
	   * */
	  public function setAllowedExtensions($extensions){
	    $this->allowedExtensions = $extensions;
	    return $this;
	  }
	  
  
  /**
   * set the max file size for uploads for added validation
   * if $this->max_size = 0 (default) then upload size is governed
   * by PHP.ini settings and/or form settings.
   * @param integer $size - max size of upload in bytes
   * @return UploadComponent
   */
    public function setSizeLimit($size) {
      $this->sizeLimit = $size;
      return $this;
    }
    
    
  /**
   * setter for the create destination flag. 
   * can be turned off if an error on missing destination is required
   * @param boolean $flag
   * @return UploadComponent
   */
  public function create_destination($flag = true) {
    $this->create_destination = $flag;
    return $this;
  }
  
   /**
   * setter for the create destination flag. 
   * can be turned off if an error on missing destination is required
   * @param boolean $flag
   * @return UploadComponent
   */
  public function overwrite($flag=false){
    $this->overwrite = $flag;
    return $this;
  }
  
 /**
      * parse the response type and return an error string
      * @param integer $type
      * @return string - error text
      */
     private function errors($type = null) {
         switch ($type) {
             case self::FILESIZE_EXCEED_SERVER_MAX:
                 return 'File size exceeds allowed size for server';
                 break;
             case self::FILESIZE_EXCEED_FORM_MAX:
                 return 'File size exceeds allowed size in form';
                 break;
             case self::PARTIAL_UPLOAD:
                 return 'File was partially uploaded. Please check your Internet connection and try again';
                 break;
             case self::NO_FILE_UPLOAD:
                 return 'No file was uploaded.';
                 break;
             case self::NO_DIRECTORY_FOR_UPLOAD:
                 return 'No upload directory found';
                 break;
             case self::SERVER_WRITE_FAIL:
                 return 'Failed to write to the server';
                 break;
             case self::FILE_FORMAT_NOT_ALLOWED:
              return 'File format of uploaded file is not allowed';
              break;
             case self::FILESIZE_EXCEEDS_CODE_MAX:
              return 'File size exceeds maximum allowed size';
              break;
      case self::DESTINATION_NOT_AVAILABLE:
        return 'Destination path does not exist';
        break;
             default:
                 return 'There has been an unexpected error, processing upload failed';
         }
     }
 
 /**
 * Upload the file to the server
 * @param array $formData('name'=>,'tmp_name'=>,'size'=>,'error'=>)
 * @return string - error text
 */
 public function upload($formData, $path=null){
    
  $this->uploadedFile =  new UploadFile($formData);
  
  // silent fail on no image
  if ($formData['error'] == self::NO_FILE_UPLOAD) {
      throw new Exception ($this->errors(self::NO_FILE_UPLOAD), self::NO_FILE_UPLOAD);
	  
  }

  // handle optional path passed in
  if (!empty($path)) $this->setDestination($path);
  
  //check if we have a path and a destination setup otherwise throw an error
  if (empty($path) && empty($this->destination)) $this->uploadedFile->setFileEntry('error',self::NO_DIRECTORY_FOR_UPLOAD);
  
  /*
   * */
  $pathinfo = pathinfo($this->uploadedFile->getFileEntry('name'));
  $ext = $pathinfo['extension'];
  
  /*
   * Checking file extensions
   * */
  if (!empty($this->allowedExtensions)) {
        
        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
          $this->uploadedFile->setFileEntry('error',self::FILE_FORMAT_NOT_ALLOWED);
        }     
  }
  
  // check max size set in code
  if ($this->sizeLimit > 0 && $this->uploadedFile->getFileEntry('size') > $this->sizeLimit) {
      $this->uploadedFile->setFileEntry('error',self::FILESIZE_EXCEEDS_CODE_MAX);
  }
    
  // check error code   
  if ($this->uploadedFile->getFileEntry('error') !== self::SUCCESS) {
      throw new Exception($this->errors($this->uploadedFile->getFileEntry('error')), $this->uploadedFile->getFileEntry('error'));
  }
    
  $destination='';
  
  // parse out class params to make the final destination string
  if (empty($this->filename)) {
      $destination = $this->destination . $this->uploadedFile->getFileEntry('name');
      $this->setFilename($this->uploadedFile->getFileEntry('name'));
  } else {
      $destination = $this->destination . $this->filename.$ext;
  }
    
   // create the destination unless otherwise set
   if ($this->create_destination()) {
      $dir = dirname($destination);
      if (!is_dir($dir)) mkdir($dir, 0755, true);
          
   }else{
      $dir = dirname($destination);
      if (!is_dir($dir)) throw new Exception($this->errors(self::DESTINATION_NOT_AVAILABLE), self::DESTINATION_NOT_AVAILABLE);
   }
  
    /*
     * By default files are not overwritten we append the time to the filename to create another file if it has already been uploaded before
     * Set to true in the constructor when initializing the file uploader component, if you want to overwrite an existing file
	 *  */
    if(!$this->overwrite && file_exists($destination)){
      	
      $newfilename = time().'_'.$this->uploadedFile->getFileEntry('name');
      $destination = $this->destination.$newfilename;
      
      $this->setFilename($newfilename);
     
    }
    
  
 	/*
	 * This is where the magic happens, we call the function save on the object uploadedFile
	 * 
	 * */
    if ($this->uploadedFile->save($destination)){
        return TRUE;
      } else {
        throw new Exception($this->errors(self::SERVER_WRITE_FAIL), self::SERVER_WRITE_FAIL);
      }

    // if we get here without returning something has definitely gone wrong
    throw new Exception($this->errors());
  
 }
 
}

/*
 * This class represents an uploaded file
 * */
class UploadFile {
 /*
  * $file is the information of the file
  * */  
 protected $file=array();
 protected $customName='';
 
 
 /*
  * Constructor
  */
 public function __construct($fileData){
  $this->file = $fileData; 
 }
 
 
 /*
  * Setter 
  */
 public function setFile($fileData){
   $this->file=$fileData;
 }
 
 public function setCustomName($name){
  $this->$customName = $name;    
 }
 
 /*
  * get the name of the file
  * */
 public function getName(){
   return $this->file['name'];
 }
 
 /*
  * get the size of the file
  * */
 
 public function getSize(){
   return $this->file['size'];
 }
 
 
 /*
  * get the file information (the array)
  * return an array
  * */
 public function getFile(){
   return $this->file;
 }
 
 /*
  * get information in the file
  * return content of $file[$paramms]
  * */
 public function getFileEntry($param){
   return $this->file[$param];
 }
 
 /*
  * set information in the file
  * */
 public function setFileEntry($params,$entry){
   $this->file[$params] = $entry;
 }
 
 /*
  * saving the file to the server
  * */
 public function save($path) {
        //print "path for saving file is ".$path;   
        if(!move_uploaded_file($this->file['tmp_name'], $path)){
          return false;
        }
        return true;
   }
 
}


?>
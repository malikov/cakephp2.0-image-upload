#[CakePHP image upload files](http://www.githup.com/cakephp2.0_image_upload)

##Overview
The FileUploaderComponent.php and ImageUploaderComponent.php files are components that will help you upload a file (in our case an image) to your server and in the process create thumbnails if necessary
---
 
##How it works

###step 1
Download and place the FileUploaderComponent.php and ImageUploaderComponent.php files in your app/Controller/Component

###step 2 
Create in a view your form. In our case we had a file input with the following name="data['file']['image']".

We used the following code :

````php
<?php echo $this->Form->create(null, array('enctype' => 'multipart/form-data','url' => array('controller' => 'upload', 'action' => 'picture')
)); ?>
     <?php echo $this->Form->file('file.image'); ?>
     <input type="submit" value="upload a picture" />
<?php echo $this->Form->end(); ?>
````

to produce the following output :

````html
<form action="/upload/picture" enctype="multipart/form-data" method="post" accept-charset="utf-8">
     <div style="display:none;"><input type="hidden" name="_method" value="POST"></div>     
     <input type="file" name="data[file][image]" />     
     <input type="submit" value="upload a picture" />
</form> 
````

###step3
In the ImageUploaderComponent.php the following line includes the FileUploaderComponent.php. Change this line to instantiate the fileuploader component according to your needs.

````php
 /*
  * Loading the component with parameters
  * parameters is an array('extensions'=>array('with','the allowed','extensions'),
  * 'sizeLimit'=> allowed size (in bytes here it is 5 mb = 5 * 1024 * 1024),
  * 'overwrite'=> boolean to allow a file to be overwritten,
  * 'custom_name' => custom name for the uploaded file
  * )
  * 
  */
 public $components = array('FileUploader'=>array('extensions'=>array('jpeg','gif','bmp','jpg','png'),'sizeLimit'=>5242880));
 
````

###step4
In your controller (in our case the controller is  UploadController.php, and the function in which we use the component is 'function picture()') include the ImageUploaderComponent with the following line :

````php
public $components = array('ImageUploader');
````

###step5
In this same controller we call the following function to upload the file
````php
function picture(){
  App::uses('Sanitize', 'Utility');
  
  $output= array();  
  $data=Sanitize::clean($this->request->data);
  
  $file = $data['file']['image'];
  
  //the folder where the files will be stored
  $fileDestination = 'files';
  
  //the folder where the thumbnails will be saved (files/thumbnails/)
  $thumbnailDestination = $fileDestination.'/thumbnails/';
  
  /*
   * 
   * this is an array of options that can be passed to the 
   * ImageUploader function upload($formData, $path=null,$options=array('custom_name'=>null, 'thumbnail'=>null, 'max_width'=>null))
   * 
   * where $formData is the uploaded file, $path is the path where the file will be saved,
   * and options are available when uploading the image 
   * $options('thumbnail'=>array("max_width"=>'width_for_thumbnail',"max_height"=>'height_for_thumbnail', "path"=>'file/path/for/thumbnail/', "custom_name"=>'custom_name_for_the_thumbnail')
   *          'max_width'=>
   *          'custom_name'=>)
   * Where thumbnail is to create a thumbnail of the image when uploaded, 
   * max_width is to rescale the picture with a specific width,
   * custom_name is a custom name for the uploaded image
   * 
   */   
  $options = array('thumbnail'=>array("max_width"=>180,
                                      "max_height"=>100, 
                                      "path"=>$thumbnailDestination),
                   'max_width'=>700);    
  try{
        //this is where the magic happens we call the function upload using the imageuploader component 
        $output = $this->ImageUploader->upload($file,$fileDestination,$options);
       
   }catch(Exception $e){
          
        $output = array('bool'=>FALSE,'error_message'=>$e->getMessage());
       
      }
      
}
````

---

##Copyright and License

You can use it freely, tweak and do whatever you want with it just quote me in your work it will be apreciated, you can follow me on twitter [@vmaliko](http://www.twitter.com/vmaliko)

---

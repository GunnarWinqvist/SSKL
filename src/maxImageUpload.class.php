<?php
/*************************************************
 * Max's Image Uploader
 *
 * Version: 1.0
 * Date: 2008-03-18
 *
 ****************************************************/
class maxImageUpload {
    // Maximum upload size
    var $maxUploadSize = PA_MAXUPLOADSIZE; // 10 Mb
    
    // Image sizes
    var $normalWidth   = PA_NORMALWIDTH;  //480
    var $normalHeight  = PA_NORMALHEIGHT;  //360
    var $thumbWidth    =  PA_THUMBWIDTH;
    var $thumbHeight   =  PA_THUMBHEIGHT;
    
    // Image quality
    var $imageQualityNormal = PA_IMAGEQUALITYNORMAL; //1:Poor ... 5:Very good
    var $imageQualityThumb  = PA_IMAGEQUALITYTHUMB; //1:Poor ... 5:Very good
   
   /*
    // Directories to store images
    var $baseDir     = '';
    var $originalDir = 'original';
    var $normalDir   = 'normal';
    var $thumbDir    = 'thumbnail';
    var $infoDir     = 'info';
    */
    
    // File postfixes
    //var $originalPrefix = PA_ORIGINALPREFIX;
    var $normalPrefix   = PA_NORMALPREFIX;
    var $thumbPrefix    = PA_THUMBPREFIX;
    
    // Internal used variables
    var $error = '';
    var $maxMemoryUsage = 128;  // 128 Mb
    
    /**
     * Constructor to initialize class varaibles
     * The upload locations will be set to the actual 
     * working directory
     *
     * @return maxImageUpload
     */
    function maxImageUpload(){
       /*
       if (!file_exists($this->baseDir)) {
          if (!@mkdir($this->baseDir)){
             $this->baseDir = getcwd();
          }
       }
       
       $this->originalDir = $this->baseDir.DIRECTORY_SEPARATOR.$this->originalDir.DIRECTORY_SEPARATOR;
       if (!file_exists($this->originalDir)) {
          mkdir($this->originalDir);
       }
       $this->normalDir = $this->baseDir.DIRECTORY_SEPARATOR.$this->normalDir.DIRECTORY_SEPARATOR;
       if (!file_exists($this->normalDir)) {
          mkdir($this->normalDir);
       }
       $this->thumbDir = $this->baseDir.DIRECTORY_SEPARATOR.$this->thumbDir.DIRECTORY_SEPARATOR;
       if (!file_exists($this->thumbDir)) {
          mkdir($this->thumbDir);
       }
       $this->infoDir = $this->baseDir.DIRECTORY_SEPARATOR.$this->infoDir.DIRECTORY_SEPARATOR;
       if (!file_exists($this->infoDir)) {
          mkdir($this->infoDir);
       }*/
    }

    /**
     * This function sets the directory where to upload the file
     * In case of Windows server use the form: c:\\temp
     * In case of Unix server use the form: /tmp
     *
     * @param String Directory where to store the files
     */
    function setUploadBaseLocation($dir){
        $this->baseDir = $dir;
    }
    
    function showUploadForm($msg='',$error=''){
?>
       <div id="container">
            <div id="header"><div id="header_left"></div>
            <div id="header_main">Max's Photo Album - Admin</div><div id="header_right"></div></div>
            <div id="content">
<?php
if ($msg != ''){
    echo '<p class="msg">'.$msg.'</p>';
} else if ($error != ''){
    echo '<p class="emsg">'.$error.'</p>';

}
?>

<form action="" method="post" enctype="multipart/form-data" >
   <center>
     <p>Upload file to your photo album. <br/> (Only jpeg files are allowed.)</p>
   </center>
                     
   <table>
      <tr><td>File:</td><td><input name="myfile" type="file" size="30" /></td></tr>
      <tr><td>Title:</td><td><input name="mytitle" type="text" size="30" /></td></tr>
      <tr><td>Description:</td><td><textarea name="mydesc" cols="30" rows="4"></textarea></td></tr>
      <tr><td colspan="2" align="center"><input type="submit" name="submitBtn" class="sbtn" value="Upload" /></td></tr>
   </table>   
</form>

</div>
   <div id="footer"><a href="http://www.phpf1.com" target="_blank">Powered by PHP F1</a></div>
</div>
<?php
    }

    function uploadImage(){
        $result = true;
        
        if (!isset($_POST['submitBtn'])){
            $this->showUploadForm();
        } else {
            $msg = '';
            $error = '';
            
            //Check image type. Only jpeg images are allowed
            if ( (($_FILES['myfile']['type'])=='image/pjpeg') || (($_FILES['myfile']['type'])=='image/jpeg') || (($_FILES['myfile']['type'])=='image/jpg')) {
               
               // Check the output directories
               if ($this->checkDirs()){
                   $target_path = $this->originalDir . basename( $_FILES['myfile']['name']);

                   if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
                      ;
                   } else{
                      $error = "The upload process failed!";
                      $result = false;
                   }

                   // Store resized images
                   if ($result){
                      $this->setMemoryLimit($target_path);

                      // Create normal size image
                      $dest = $this->normalDir.$this->normalPrefix.basename($_FILES['myfile']['name']);
                      $this->resizeImage($target_path,$dest,$this->normalWidth,$this->normalHeight,$this->imageQualityNormal);

                      // Create thumbnail image
                      $dest = $this->thumbDir.$this->thumbPrefix.basename($_FILES['myfile']['name']);
                      $this->resizeImage($target_path,$dest,$this->thumbWidth,$this->thumbHeight,$this->imageQualityThumb);
                      $msg = "Your photo was stored!";
                      
                   }
                }
            } else {
               echo "Only jpeg images are allowed!";
            }
            
            $this->storePhotoInfo();

            $this->showUploadForm($msg,$error);
        }

    }
    
    function storePhotoInfo(){
       $filename = $this->infoDir . basename( $_FILES['myfile']['name']) . ".info";
       $title    = isset($_POST['mytitle']) ? $_POST['mytitle'] : " - ";
       $desc     = isset($_POST['mydesc'])  ? $_POST['mydesc'] : " - ";
       
       $f = fopen($filename,"w+");         
       fwrite($f,$title."\n");
       fwrite($f,$desc."\n");
       fclose($f);
    }
    
    function getThumbList(){
         $mylist = array();
   
	      // Open the actual directory
	      if ($handle = @opendir($this->thumbDir)) {
		    
	         // Read all file from the actual directory
		      while ($file = readdir($handle))  {
		         if (!is_dir($file)) {
		             $mylist[] = $file;
      	      }
		      }
	      }	
	
	      return $mylist;
    }     
    
    function getBaseName($name,$prefix){
    	return substr($name,strlen($prefix));
    }	
    
    function displayThumbList(){
       $tlist = $this->getThumbList();
       $i = 0;
       foreach ($tlist as $value) {
       	echo "<a href=\"showImage.php?id=".$this->getBaseName($value,$this->thumbPrefix)."\" target=\"imgHolder\"><img class=\"timg\" src=\"thumbnail/".$value."\" alt=\"a\" /></a>";
       	$i++;
       }
    }
    
    function displayFirst() {
       $tlist = $this->getNormalList();
       if (sizeof($tlist) > 0){
           echo "showImage.php?id=".$this->getBaseName($tlist[0],$this->normalPrefix);
       } else {
           echo "#";
       }
        
    }
    
    function getNormalList(){
         $mylist = array();
   
	      // Open the actual directory
	      if ($handle = @opendir($this->normalDir)) {
		    
	         // Read all file from the actual directory
		      while ($file = readdir($handle))  {
		         if (!is_dir($file)) {
		             $mylist[] = $file;
      	      }
		      }
	      }	
	
	      return $mylist;
    }     
    
    function displayNormalImage(){
       $tlist = $this->getNormalList();
       foreach ($tlist as $value) {
       	echo "<img class=\"timg\" src=\"thumbnail/".$value."\" alt=\"a\" />";
       }
    }
    
    
    function checkDirs(){
       $result = true;
       
       if (!file_exists($this->originalDir)){
          $this->error = "The target directory ($this->originalDir) doesn't exists!";
          $result = false;
       } else if (!is_writeable($this->originalDir)) {
          $this->error = "The target directory ($this->originalDir) is not writeable!";
          $result = false;
       } else if (!is_writeable($this->normalDir)) {
          $this->error = "The target directory ($this->normalDir) is not writeable!";
          $result = false;
       } else if (!is_writeable($this->normalDir)) {
          $this->error = "The target directory ($this->normalDir) is not writeable!";
          $result = false;
       } else if (!is_writeable($this->thumbDir)) {
          $this->error = "The target directory ($this->thumbDir) is not writeable!";
          $result = false;
       } else if (!is_writeable($this->originalDir)) {
          $this->error = "The target directory ($this->thumbDir) is not writeable!";
          $result = false;
       }

       return $result;      
    }
    
    function setMemoryLimit($filename){
       $width  = 0;
       $height = 0;
       $size   = ini_get('memory_limit');
       
       list($width, $height) = getimagesize($filename);
       $size = $size + floor(($width * $height * 4 * 1.5 + 1048576) / 1048576);
       
       if ($size > $this->maxMemoryUsage) $size = $this->maxMemoryUsage;
         
       ini_set('memory_limit',$size.'M');

    }
    
    function resizeImage($src,$dest,$new_width,$new_height,$quality){
       $width  = 0;
       $height = 0;
       
       list($width, $height) = getimagesize($src);
       
       // Calculate new width and height
       $x_scale = $width / $new_width;
       $y_scale = $height / $new_height;
       
       if ($x_scale > $y_scale){
           $new_height = floor($height / $x_scale);
       } else {
           $new_width = floor($width / $y_scale);
       }
       
       $newImage = imagecreatetruecolor($new_width, $new_height);
       $oldImage = imagecreatefromjpeg($src);
       
       $this->fastimagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $new_width, $new_height, $width, $height, $quality);

       imagejpeg($newImage, $dest, 100);
    }
    
    // Function to resize images
    // Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
    function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
       if (empty($src_image) || empty($dst_image)) { return false; }
       
       if ($quality <= 1) {
         $temp = imagecreatetruecolor ($dst_w + 1, $dst_h + 1);
         imagecopyresized ($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
         imagecopyresized ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
         imagedestroy ($temp);
       } elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
         $tmp_w = $dst_w * $quality;
         $tmp_h = $dst_h * $quality;
         $temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
         imagecopyresized ($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
         imagecopyresampled ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
         imagedestroy ($temp);
       } else {
         imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
       }
       return true;
   }

}
?>
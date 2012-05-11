<?php

/**
 * Add picture (add_picture).
 *
 * This page adds a picture to the album id.
 * The form is recursive i.e. the same page is accessed when the submit button is
 * pressed but then the input from the form is processed and then the form is 
 * dispalayed again for a new picture to be entered.
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();


/*
 * Process input 'id' if exists.
 * If not show error message and send to admin.
 */
$idAlbum = isset($_GET['id']) ? $_GET['id'] : NULL;
if ($debugEnable) $debug .= "id=".$idAlbum."<br />\r\n";
if (!$idAlbum) {
    $_SESSION['ErrorMessage'] = "Inget album-id presenterades.";
    header('Location: ' . WS_SITELINK . "?p=glry");
    exit;
}



$mainTextHTML = "<div id='content'>";

if (isset($_POST['submitBtn'])){

    // If the submit button has been pressed, process the form information.
    
    require_once('src/maxImageUpload.class.php');
    $maxPhoto = new maxImageUpload(); 

    $result = TRUE;
    $msg = "";
    $error = "";

    // Prepare the database.
    $dbAccess           = new CdbAccess();
    $tablePicture       = DB_PREFIX . 'Picture';

    // Get form values.
    $namePicture = $dbAccess->WashParameter(strip_tags($_POST['mytitle']));
    $descriptionPicture = $dbAccess->WashParameter(
        strip_tags($_POST['mydesc']));

    // Register picture in DB and store the information.
    $query = <<<QUERY
INSERT INTO {$tablePicture} (
    picture_idAlbum, 
    namePicture, 
    descriptionPicture)
VALUES (
    '{$idAlbum}', 
    '{$namePicture}',
    '{$descriptionPicture}'
    );
QUERY;
    $dbAccess->SingleQuery($query);

    // Get the picture id.
    $idPicture = $dbAccess->LastId();
    if ($debugEnable) $debug .= "idPicture=".$idPicture.
        " Type=".$_FILES['myfile']['type'].
        " Name=".$_FILES['myfile']['name']."<br />\r\n";
    
    //Check image type. Only jpeg images are allowed
    if (strcasecmp(($_FILES['myfile']['type']), 'image/pjpeg') && 
        strcasecmp(($_FILES['myfile']['type']), 'image/jpeg')  && 
        strcasecmp(($_FILES['myfile']['type']), 'image/jpg')   ){
        $error = "Bara jpeg-bilder kan laddas upp!";
        $result = false;
    }
    
    if ($result){
        // Move uploaded file to a temporary name.
        $target_path = TP_PICTURES . "tmp" . '.jpg';
        if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
            ;
        } else{
            $error = "Något gick fel vid uppladdningen av din bild!";
            $result = false;
        }
    }

    if ($result){
        // Store resized images
        $maxPhoto->setMemoryLimit($target_path);

        // Create normal size image
        $dest = TP_PICTURES . PA_NORMALPREFIX . $idPicture . '.jpg';
        $maxPhoto->resizeImage($target_path, $dest, 
            PA_NORMALWIDTH, PA_NORMALHEIGHT, PA_IMAGEQUALITYNORMAL);

        // Create thumbnail image
        $dest = TP_PICTURES . PA_THUMBPREFIX . $idPicture . '.jpg';
        $maxPhoto->resizeImage($target_path, $dest,
            PA_THUMBWIDTH, PA_THUMBHEIGHT, PA_IMAGEQUALITYTHUMB);
        $msg = "Din bild laddades upp! Vill du ladda upp en till?";
    }

    if (!$result) {
        // If something went wrong, remove the picture id again.
        $query = "
            DELETE FROM {$tablePicture} 
            WHERE idPicture = '{$idPicture}';
        ";
        $dbAccess->SingleQuery($query);
    }

    // Show messages on top of the form.
    if ($msg){
        $mainTextHTML .= "<p><img src='images/ok.gif' alt='ok' />".$msg."</p>";
    } else if ($error){
        $mainTextHTML .= "<p><img src='images/nok.gif' alt='ok' />"
            .$error."</p>";
    }
}


/*
 * Show the file upload form.
 */
$mainTextHTML .= <<<HTMLCode
<form action="?p=add_pict&id={$idAlbum}" method="post" 
    enctype="multipart/form-data" >
   <p>Ladda upp en bild till albumet. <br/> (Endast jpeg-filer är tillåtna.)</p>
                     
   <table>
      <tr><td>File:</td><td><input name="myfile" type="file" size="30" />
        </td></tr>
      <tr><td>Title:</td><td><input name="mytitle" type="text" size="30" />
        </td></tr>
      <tr><td>Description:</td><td>
        <textarea name="mydesc" cols="30" rows="4"></textarea></td></tr>
      <tr><td colspan="2" align="center"><input type="submit" name="submitBtn" 
        class="sbtn" value="Upload" />
          <input type="button" value="Cancel" 
            onclick="location='?p=show_alb&amp;id={$idAlbum}'"/>
          </td></tr>
   </table>   
</form>
</div>
HTMLCode;


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page         = new CHTMLPage(); 
$pageTitle    = "Ladda upp bild till album";

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


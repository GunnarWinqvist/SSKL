<?php

/**
 * Show picture (show_picture).
 *
 * This is not a complete webpage only a page for showing pictures in the iframe of
 * PShowAlbum. Input is the id of the picture to be displayed.
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();


// Input idPicture.
$idPicture = isset($_GET['id']) ? $_GET['id'] : NULL;

$stylesheet = WS_STYLESHEET;

$mainTextHTML = <<<HTMLCode
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
   <link href="{$stylesheet}" rel="stylesheet" type="text/css" />
   <title> </title>
</head>
<body id="if">   
HTMLCode;

if ($idPicture){

    // Prepare the database.
    $dbAccess           = new CdbAccess();
    $tablePicture       = DB_PREFIX . 'Picture';
    
    // Get picture information from the DB.
    $query = "SELECT * FROM {$tablePicture} WHERE idPicture = {$idPicture};";
    $result = $dbAccess->SingleQuery($query);
    $row = $result->fetch_object();
    $imageTitle = $row->namePicture;        
    $imageDesc = nl2br($row->descriptionPicture);
    
    $normalImage = WS_PICTUREARCHIVE . PA_NORMALPREFIX . $idPicture . ".jpg";

    $mainTextHTML .= <<<HTMLCode
<img src='{$normalImage}' alt='a' /><br/>
<div id='imgInfo'>
    <h2>{$imageTitle}</h2>
    <p>{$imageDesc}</p>
</div>
HTMLCode;
}

$mainTextHTML .= "</body>";
echo $mainTextHTML;


?>


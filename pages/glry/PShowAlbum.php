<?php

/**
 * Show Album (show_album)
 *
 * This page shows the album 'id'. 
 * First a list is made with all picture IDs that belongs to the album ID from the
 * DB. Then the first picture in the list is displayed followed by thumbs of all 
 * the pictures in the list.
 * The main picture is shown in a separate iframe. Eache thumb is a link that 
 * adresses the next picture to be shown in the iframe. In this way it's only the
 * iframe that is rewritten not the whole page.
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
if (!$idAlbum) {
    $_SESSION['ErrorMessage'] = "Inget album-id presenterades.";
    header('Location: ' . WS_SITELINK . "?p=glry");
    exit;
}


/*
 * Prepare the database.
 */
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableAlbum             = DB_PREFIX . 'Album';
$tablePicture           = DB_PREFIX . 'Picture';


/*
 * Find the name of the album from the DB and set pageTitle to this name.
 */
$query     = "SELECT nameAlbum FROM {$tableAlbum} WHERE idAlbum = {$idAlbum};";
if ($result = $dbAccess->SingleQuery($query)) {
    $row       = $result->fetch_object();
    $pageTitle = $row->nameAlbum;
    $result->close();
} else {
    $_SESSION['ErrorMessage'] = "Felaktigt album-id.";
    header('Location: ' . WS_SITELINK . "?p=glry");
    exit;
}


/*
 * Find all pictures in the album idAlbum and put their file names in an array.
 */
$query = "SELECT idPicture FROM {$tablePicture} 
    WHERE picture_idAlbum = {$idAlbum};";
if ($result = $dbAccess->SingleQuery($query)) {

    // There are pictures in the album. Make a list of them.
    $pictureList = array();
    while($row = $result->fetch_object()) {
        $pictureList[] = $row->idPicture;
    }
    
    // Show the first picture.
    $mainTextHTML = <<<HTMLCode
<div id="frameHolder">
    <iframe name="imgHolder" frameborder="0" 
        src="?p=show_pict&amp;id={$pictureList[0]}">
    </iframe>
    <div id="list">

HTMLCode;

    // Show the thumb list.
    foreach ($pictureList as $idPicture) {
        $thumbPath = WS_PICTUREARCHIVE . PA_THUMBPREFIX . $idPicture . '.jpg';
        $mainTextHTML .= <<<HTMLCode
<a href="?p=show_pict&amp;id={$idPicture}" target="imgHolder">
    <img class="timg" src="{$thumbPath}" alt="a" />
    </a>

HTMLCode;
    }

    $mainTextHTML .= <<<HTMLCode
    </div>
    <a title='Lägg till bild' href='?p=add_pict&amp;id={$idAlbum}'>
        <img src='images/b_addPict.gif' alt='Lägg till bild' /></a>
</div>

HTMLCode;
    $result->close();


} else {
    // No pictures in the album.
    $mainTextHTML = <<<HTMLCode
<p>Det finns inga bilder i albumet.</p>
<a title='Lägg till bild' href='?p=add_pict&amp;id={$idAlbum}'>
    <img src='images/b_addPict.gif' alt='Lägg till bild' /></a>

HTMLCode;
}


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page         = new CHTMLPage(); 

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


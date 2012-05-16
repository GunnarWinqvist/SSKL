<?php

/**
 * Gallery
 *
 * The latest party pictures etc.
 * 
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
 * Initiate the DB.
 */
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableAlbum             = DB_PREFIX . 'Album';
$tablePicture           = DB_PREFIX . 'Picture';


/*
 * Search the database and list the result.
 */
$query = "SELECT * FROM {$tableAlbum} ORDER BY timeEditedAlbum DESC;";
$result=$dbAccess->SingleQuery($query);

// Start with a title and a button for adding album.
$mainTextHTML = <<<HTMLCode
<h2>Fotogalleri</h2>
<a title='Lägg till ett album' href='?p=edit_alb'>
    <img src='../images/b_addBook.gif' alt='Vidare' /></a>
<br /><br />
<hr />

HTMLCode;


if ($result) {
    date_default_timezone_set(WS_TIMEZONE);
    while($row = $result->fetch_object()) {
        // For every row do.
        if ($row->signaturePictId) {
            // If there is a signature picture chosen.
            $thumb = WS_PICTUREARCHIVE . PA_THUMBPREFIX . $row->signaturePictId
                . ".jpg";
            $mainTextHTML .= <<<HTMLCode
<a title='{$row->nameAlbum}' 
    href='?p=show_alb&amp;id={$row->idAlbum}'>
<img class='floatLeft' alt='thumb' src='{$thumb}' />
</a>

HTMLCode;
        }
        
        $fTimeEdited = date("Y-m-d G:i", $row->timeEditedAlbum);
        $mainTextHTML .= <<<HTMLCode
<h2><a class='noDeco' title='{$row->nameAlbum}' 
    href='?p=show_alb&amp;id={$row->idAlbum}'>{$row->nameAlbum}</a></h2>
<p>{$row->descriptionAlbum}</p>
<p class='time'>Senast ändrat {$fTimeEdited}</p>

HTMLCode;
    
        // Lägg till knappar om det är ägaren som är inlogad.
        $idSession = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : NULL;
        $authoritySession = isset($_SESSION['authorityUser']) 
            ? $_SESSION['authorityUser'] : NULL;

        if (($idSession == $row->album_idUser) 
            || ($authoritySession == "adm")) {
            $mainTextHTML .= <<<HTMLCode
<br />
<a title='Editera album' href='?p=edit_alb&amp;id={$row->idAlbum}'>
    <img src='../images/b_edit.gif' alt='Editera album' /></a>
<a title='Radera album' href='?p=del_alb&amp;idPost={$row->idAlbum}' 
    onclick="return confirm('Vill du radera albumet och alla bilder i det?');">
    <img src='../images/b_delete.gif' alt='Radera album' /></a>

HTMLCode;
        }
        $mainTextHTML .= "<br /><hr />\r\n";
    }
    $result->close();
    
} else {
    // If no result.
    $mainTextHTML .= <<<HTMLCode
<p>Det finns inga album i databasen.</p>

HTMLCode;
}


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page         = new CHTMLPage(); 
$pageTitle    = "Galleri";

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


<?php

/**
 * Signature picture (sign_pict)
 *
 * Store the signature picture chosen on the edit album page in the database.
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
//$intFilter->UserIsAuthorisedOrDie('adm'); //Must be adm to access the page.


/*
 * Prepare the database.
 */
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableAlbum             = DB_PREFIX . 'Album';
$tablePicture           = DB_PREFIX . 'Picture';


/*
 * Process input if exists.
 */
$idAlbum   = isset($_GET['album']) ? $_GET['album'] : NULL;
$idPicture = isset($_GET['pict'])  ? $_GET['pict']  : NULL;
$idAlbum   = $dbAccess->WashParameter($idAlbum);
$idPicture = $dbAccess->WashParameter($idPicture);
if ($debugEnable) $debug.="idAlbum=".$idAlbum." idPicture=".$idPicture.
    "<br /> \r\n";


/*
 * Update the DB.
 */
$query = "
    UPDATE {$tableAlbum}
    SET signaturePictId = '{$idPicture}'
    WHERE idAlbum = '{$idAlbum}';
";
$dbAccess->SingleQuery($query);


if ($debugEnable) { 
    $mainTextHTML = "<a title='Vidare' href='?p=glry'>
        <img src='../images/b_enter.gif' alt='Vidare' /></a>
        <br />\r\n";
} else { // Annars hoppa vidare.
    header('Location: ' . WS_SITELINK . "?p=glry");
    exit;
}


/*
 * Define everything that shall be on the page, generate the right column
 * and then display the page.
 */
$page         = new CHTMLPage(); 
$pageTitle    = "Signature picture";

require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


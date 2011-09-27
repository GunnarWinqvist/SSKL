<?php
///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNoAccess.php
// Sidan anropas inte fr�n index.php utan direkt fr�n access-kontrollerna.
// Sidan visar ett felmeddelande n�r man har f�rs�kt n� en sida man inte har beh�righet till.
// Input till sidan �r $message.
//


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//

$mainTextHTML = <<<HTMLCode
<div class='errorMessage'>{$message}</div>
HTMLCode;

$page = new CHTMLPage(); 
$pageTitle = "Ej beh�righet";

$page->printPage($pageTitle, $mainTextHTML, "", "");

// Bryt k�rningen f�r att inte skriva ut ursprungssidan.
exit;
?>


<?php
///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNoAccess.php
// Sidan anropas inte från index.php utan direkt från access-kontrollerna.
// Sidan visar ett felmeddelande när man har försökt nå en sida man inte har behörighet till.
// Input till sidan är $message.
//


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//

$mainTextHTML = <<<HTMLCode
<div class='errorMessage'>{$message}</div>
HTMLCode;

$page = new CHTMLPage(); 
$pageTitle = "Ej behörighet";

$page->printPage($pageTitle, $mainTextHTML, "", "");

// Bryt körningen för att inte skriva ut ursprungssidan.
exit;
?>


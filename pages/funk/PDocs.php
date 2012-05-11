<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDocuments.php
// Visar en lista med de dokument som ligger under foldern 'documents'. Dokumenten i listan �r klickbara.
// 
// 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();
$intFilter->UserIsAuthorisedOrDie('fnk');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera huvudkolumnen.

$mainTextHTML = <<<HTMLCode
<h2>Dokumentdatabas</h2>
<ul>
HTMLCode;

$dh = opendir(TP_DOCUMENTS);
while ($file = readdir($dh)) {
    if ($file !="." AND $file !="..") {
        $mainTextHTML .= "<li> <a href='../documents/{$file}'>{$file}</a> </li>";
    }
}
closedir($dh);
$mainTextHTML .= "</ul>";

$mainTextHTML .= <<<HTMLCode
<h2>Ladda upp dokument till servern</h2>
<form action='?p=doc_upld' enctype='multipart/form-data' method='post'>
<p>Vilket dokument vill du ladda upp?</p>
<input type='file' name='file' value='' />
<p>Vad ska dokumentet heta p� servern? Skriv namnet utan extension (.pdf, .doc, etc)</p>
<input type='text' name='filename' value='' />

<input type='submit' name='submit' value='Ladda upp' />
</form>
HTMLCode;

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Dokument";

require(TP_PAGES.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);



?>

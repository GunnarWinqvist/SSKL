<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDocuments.php
// Visar en lista med de dokument som ligger under foldern 'documents'. Dokumenten i listan är klickbara.
// 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();   // Måste vara inloggad för att nå sidan.
$intFilter->UserIsAuthorisedOrDie('fnk');         // Måste vara minst funktionär för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera huvudkolumnen.

$mainTextHTML = <<<HTMLCode
<h2>Dokumentdatabas</h2>
<ul>
HTMLCode;

$dh = opendir(TP_DOCUMENTSPATH);
while ($file = readdir($dh)) {
    if ($file !="." AND $file !="..") {
        $mainTextHTML .= "<li> <a href='../documents/{$file}'>{$file}</a> </li>";
    }
}
closedir($dh);
$mainTextHTML .= "</ul>";

$mainTextHTML .= <<<HTMLCode
<h2>Ladda upp dokument till servern</h2>
<form action='?p=doc_upload' enctype='multipart/form-data' method='post'>
<p>Vilket dokument vill du ladda upp?</p>
<input type='file' name='file' value='' />
<p>Vad ska dokumentet heta på servern? Skriv namnet utan extension (.pdf, .doc, etc)</p>
<input type='text' name='filename' value='' />

<input type='submit' name='submit' value='Ladda upp' />
</form>
HTMLCode;

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Dokument";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);



?>


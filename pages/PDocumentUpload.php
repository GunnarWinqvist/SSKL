<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDocumentUpload.php
// Anropas med 'doc_upload' fr�n index.php.
// Sidan laddar upp ett dokument till servern.
// Input: 'filename' $_POST 'file' hanterat av $_FILES
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();   // M�ste vara inloggad f�r att n� sidan.
$intFilter->UserIsAuthorisedOrDie('fnk');         // M�ste vara minst funktion�r f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan.

$filename = isset($_POST['filename']) ? $_POST['filename'] : NULL;
if ($debugEnable) $debug .= "filename: " . $filename . "<br /> \n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Ladda upp filen och kontrollera att det har g�tt r�tt till.

$mainTextHTML = "";
$extension = "";

if (is_uploaded_file( $_FILES['file']['tmp_name'] )) { 

    switch ($_FILES['file']['type']) {
        case 'application/pdf':
            $extension = '.pdf';
            break;
        case 'application/msword':
            $extension = '.doc';
            break;
        case 'application/excel':
            $extension = '.xls';
            break;
        case 'application/vnd.ms-excel':
            $extension = '.xls';
            break;
        case 'application/x-excel':
            $extension = '.xls';
            break;
        case 'application/x-msexcel':
            $extension = '.xls';
            break;
    }
    if (!$extension)
        $mainTextHTML .= "<p>Du kan bara ladda upp filer av typerna .pdf, .doc eller .xls</p>";
    else {
        $result = move_uploaded_file($_FILES['file']['tmp_name'], TP_DOCUMENTSPATH."/".$filename.$extension);
        
        if ($result == 1) {
            // Om det gick bra s� hoppa tillbaka till doc.
            header('Location: ' . WS_SITELINK . "?p=doc");
            exit;
        } else $mainTextHTML .= "<p>Det gick inte att ladda upp file.</p>";
        
    }
}

$mainTextHTML .= "<a href='?p=doc'>Tillbaka</a>";



///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Template";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML

$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);



?>


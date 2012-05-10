<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PContact.php
// Anropas med 'contact' från index.php.
// Sidan visar kontaktinformation.
// Input:  
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Lista alla funktionärer.

$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableBostad        = DB_PREFIX . 'Bostad';

$query = <<<QUERY
SELECT idPerson, fornamnPerson, efternamnPerson, funktionFunktionar, mobilPerson
    FROM ({$tablePerson} JOIN {$tableFunktionar} ON funktionar_idPerson = idPerson)
    ORDER BY funktionFunktionar;
QUERY;
$result=$dbAccess->SingleQuery($query);

$mainTextHTML = <<<HTMLCode
<h2>Funktionärer i svenska skolföreningen</h2>
<p>Vi som jobbar i svenska skolföreningen i Kuala Lumpur är:</p>
<table>
HTMLCode;

while($row = $result->fetch_row()) {
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
    list($idPerson, $fornamnPerson, $efternamnPerson, $funktionFunktionar, $mobilPerson) = $row;
    $mainTextHTML .= <<<HTMLCode
<tr><td>{$fornamnPerson} {$efternamnPerson}</td><td>{$funktionFunktionar}</td><td>{$mobilPerson}</td></tr>
HTMLCode;
}
$result->close();
$mainTextHTML .= "</table>\n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Kontakt";

$anmalningsblankett = WS_SITELINK . "documents/anmalningsblankett.doc";
$mainTextHTML .= <<<HTMLCode
<p>För mer information om svenska skolan eller kompletterande svenskundervisning, var vänlig 
kontakta någon av oss.</p>
<p>Du kan även skicka mejl till: info@svenskaskolankualalumpur.com</p>

HTMLCode;


require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PContact.php
// Anropas med 'contact' fr�n index.php.
// Sidan visar kontaktinformation.
// Input:  
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Lista alla funktion�rer.

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
<h2>Funktion�rer i svenska skolf�reningen</h2>
<p>Vi som jobbar i svenska skolf�reningen i Kuala Lumpur �r:</p>
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
<p>F�r mer information om svenska skolan eller kompletterande svenskundervisning, var v�nlig 
kontakta n�gon av oss.</p>
<p>Du kan �ven skicka mejl till: info@svenskaskolankualalumpur.com</p>

HTMLCode;


require(TP_PAGES.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


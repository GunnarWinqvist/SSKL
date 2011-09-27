<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PApplication.php
// Anropas med 'appl' från index.php.
// Sidan visar kontaktinformation.
// Input:  
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
//Uppgifter för ordförande

$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableBostad        = DB_PREFIX . 'Bostad';

$query = <<<QUERY
SELECT fornamnPerson, efternamnPerson, adressBostad, stadsdelBostad, postnummerBostad, statBostad
    FROM ({$tablePerson} JOIN {$tableFunktionar} ON funktionar_idPerson = idPerson)
    LEFT OUTER JOIN {$tableBostad} ON person_idBostad = idBostad
    WHERE funktionFunktionar LIKE '%Ordförande%';
QUERY;
$result=$dbAccess->SingleQuery($query);
$row = $result->fetch_object();
$result->close();

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Anmälan";

$anmalningsblankett = WS_SITELINK . "documents/anmalningsblankett.doc";
$mainTextHTML = <<<HTMLCode
<h3>Anmälan</h3>
<p>För att anmäla ditt barn till svenska skolan, ladda ner anmälningsblanketten, fyll i och skicka 
den med mejl till info@svenskaskolankualalumpur.com eller med snigelpost till:</p>

<p><b>Svenska Skolan i Kuala Lumpur <br />
{$row->fornamnPerson} {$row->efternamnPerson}<br />
{$row->adressBostad}<br />
{$row->stadsdelBostad}<br />
{$row->postnummerBostad} {$row->statBostad}<br /></b></p>

<p><a title='Anmälan' href='../documents/anmalningsblankett.doc'><img src='../images/b_application.gif' alt='Anmälan' /></a></p>

HTMLCode;

// <p><a title='Anmälan' href='?p=appl'><img src='../images/b_application.gif' alt='Anmälan' /></a></p>


require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


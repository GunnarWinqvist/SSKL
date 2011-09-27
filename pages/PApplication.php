<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PApplication.php
// Anropas med 'appl' fr�n index.php.
// Sidan visar kontaktinformation.
// Input:  
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
//Uppgifter f�r ordf�rande

$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableBostad        = DB_PREFIX . 'Bostad';

$query = <<<QUERY
SELECT fornamnPerson, efternamnPerson, adressBostad, stadsdelBostad, postnummerBostad, statBostad
    FROM ({$tablePerson} JOIN {$tableFunktionar} ON funktionar_idPerson = idPerson)
    LEFT OUTER JOIN {$tableBostad} ON person_idBostad = idBostad
    WHERE funktionFunktionar LIKE '%Ordf�rande%';
QUERY;
$result=$dbAccess->SingleQuery($query);
$row = $result->fetch_object();
$result->close();

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Anm�lan";

$anmalningsblankett = WS_SITELINK . "documents/anmalningsblankett.doc";
$mainTextHTML = <<<HTMLCode
<h3>Anm�lan</h3>
<p>F�r att anm�la ditt barn till svenska skolan, ladda ner anm�lningsblanketten, fyll i och skicka 
den med mejl till info@svenskaskolankualalumpur.com eller med snigelpost till:</p>

<p><b>Svenska Skolan i Kuala Lumpur <br />
{$row->fornamnPerson} {$row->efternamnPerson}<br />
{$row->adressBostad}<br />
{$row->stadsdelBostad}<br />
{$row->postnummerBostad} {$row->statBostad}<br /></b></p>

<p><a title='Anm�lan' href='../documents/anmalningsblankett.doc'><img src='../images/b_application.gif' alt='Anm�lan' /></a></p>

HTMLCode;

// <p><a title='Anm�lan' href='?p=appl'><img src='../images/b_application.gif' alt='Anm�lan' /></a></p>


require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


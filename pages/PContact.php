<?php

/**
 * Kontaktsida (contact)
 *
 * Sidan visar kontaktinformation.
 *
 */


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


/*
 * Prepare DB.
 */
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableBostad        = DB_PREFIX . 'Bostad';


/*
 * Lista alla funktionärer.
 */
$query = "
SELECT idPerson, fornamnPerson, efternamnPerson, funktionFunktionar, mobilPerson
    FROM ({$tablePerson} JOIN {$tableFunktionar} ON funktionar_idPerson = idPerson)
    ORDER BY funktionFunktionar;";
$result=$dbAccess->SingleQuery($query);

$mainTextHTML = <<<HTMLCode
<h2>Funktionärer i svenska skolföreningen</h2>
<p>Vi som jobbar i svenska skolföreningen i Kuala Lumpur är:</p>
<table>
HTMLCode;

while($row = $result->fetch_row()) {
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br />\r\n";
    list(
        $idPerson, 
        $fornamnPerson, 
        $efternamnPerson, 
        $funktionFunktionar, 
        $mobilPerson
    ) = $row;
    $mainTextHTML .= <<<HTMLCode
<tr><td>{$fornamnPerson} {$efternamnPerson}</td>
    <td>{$funktionFunktionar}</td>
    <td>{$mobilPerson}</td></tr>
    
HTMLCode;
}
$result->close();

$mainTextHTML .= <<<HTMLCode
</table>
<p>För mer information om svenska skolan eller kompletterande 
svenskundervisning, var vänlig kontakta någon av oss.</p>
<p>Du kan även skicka mejl till: info@svenskaskolankualalumpur.com</p>

HTMLCode;


/*
 * Tag reda på ordförandes adress.
 */
$query = "
SELECT * FROM (
    ({$tablePerson} JOIN {$tableFunktionar} ON funktionar_idPerson = idPerson)
                    JOIN {$tableBostad}     ON person_idBostad     = idBostad)
    WHERE funktionFunktionar LIKE '%Ordförande%';
";
if ($result=$dbAccess->SingleQuery($query)) {
    $row = $result->fetch_object();
    $mainTextHTML .= <<<HTMLCode
<p>Eller snigelpost till:</p>
<p>Svenska Skolföreningen i Kuala Lumpur<br />
c/o {$row->fornamnPerson} {$row->efternamnPerson}<br />
{$row->adressBostad}<br />
{$row->stadsdelBostad}<br />
{$row->postnummerBostad} Kuala Lumpur<br />
MALAYSIA</p>

HTMLCode;
    $result->close();
}


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Kontakt";

require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


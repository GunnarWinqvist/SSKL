<?php

/**
 * Kalender (cal)
 *
 * Skapar en sida med en frame d�r dokumentet $calendar visas. 
 * 
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


/*
 * Definition av kalenderfilen. Om filen �ndrar namn eller typ s� m�ste 
 * raderna nedan ocks� �ndras.
 */
$calendar = WS_DOCLINK . "Evighetskalender.xls";
$docType = "application/excel";

if ($debugEnable) $debug .= "calendar=".$calendar." docType=".$docType.
    "<br />\r\n";
    

/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page         = new CHTMLPage(); 
$pageTitle    = "Kalender";

$mainTextHTML = <<<HTMLCode
<h1>{$pageTitle}</h1>
<iframe src='{$calendar}'>H�r skulle kalendern ha visats, men p� grund av 
n�got fel s� g�r den inte det.</iframe>

HTMLCode;

require(TP_PAGES.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


<?php

/**
 * Kalender (cal)
 *
 * Skapar en sida med en frame där dokumentet $calendar visas. 
 * 
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


/*
 * Definition av kalenderfilen. Om filen ändrar namn eller typ så måste 
 * raderna nedan också ändras.
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
<iframe src='{$calendar}'>Här skulle kalendern ha visats, men på grund av 
något fel så gör den inte det.</iframe>

HTMLCode;

require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


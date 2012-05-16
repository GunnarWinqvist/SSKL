<?php

/**
 * Template page.
 *
 * The template page is a fully functional displayable page on the UBE site. 
 * It is used for creating new pages.
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();
//$intFilter->UserIsAuthorisedOrDie('adm'); //Must be adm to access the page.

if ($debugEnable) $debug .= "This is a template page.<br />\r\n";

/*
 * Define everything that shall be on the page, generate the right column
 * and then display the page.
 */
$page         = new CHTMLPage(); 
$pageTitle    = "Template";

$mainTextHTML = <<<HTMLCode
<h1>Mallsida</h1>
<p>Detta är en mallsida för att skapa nya sidor</p>

HTMLCode;

require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


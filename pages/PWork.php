<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PWork.php
// Ändra WS_WORK i config.php till TRUE när du arbeter med siten.
// 



///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Omarbete pågår";

$mainTextHTML = <<<HTMLCode
<h1>Omarbete pågår!</h1>
<h2>Kom tillbaka lite senare, tack.</h2>
<img src='../images/vagarbete.gif' />
HTMLCode;


$page->printPage($pageTitle, $mainTextHTML, "", "");


?>


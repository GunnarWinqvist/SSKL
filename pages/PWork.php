<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PWork.php
// �ndra WS_WORK i config.php till TRUE n�r du arbeter med siten.
// 



///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Omarbete p�g�r";

$mainTextHTML = <<<HTMLCode
<h1>Omarbete p�g�r!</h1>
<h2>Kom tillbaka lite senare, tack.</h2>
<img src='../images/vagarbete.gif' />
HTMLCode;


$page->printPage($pageTitle, $mainTextHTML, "", "");


?>


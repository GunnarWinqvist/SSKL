<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLists.php
// Anropas med 'lists' fr�n index.php.
// Sidan ger ett antal val f�r att generera adresslistor, s�ndlistor etc.
// 
// Input: 
// Output: 'lista'
//


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();
$intFilter->UserIsAuthorisedOrDie('fnk'); 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Listor";

// L�gg in din text f�r huvudkolumnen h�r nedan i HTML-kod.
$mainTextHTML = <<<HTMLCode
<form name='lists' action='?p=lists_ex' method='post'>
<h3>V�lj vilken typ av lista du vill ha.</h3>
<input type='radio' name='lista' value='1'  /> Funktion�rer telefon och adress. <br />
<input type='radio' name='lista' value='2'  /> Funktion�rer telefon och e-post. <br />
<input type='radio' name='lista' value='3'  /> Elever kontaktuppgifter och personnummer. <br />
<input type='radio' name='lista' value='4'  /> Klasslista med l�rare. <br />
<input type='radio' name='lista' value='5'  /> S�ndlista f�r styrelsen. <br />
<input type='radio' name='lista' value='6'  /> S�ndlista f�r l�rare. <br />
<input type='radio' name='lista' value='7'  /> S�ndlista f�r alla funktion�rer. <br />
<input type='radio' name='lista' value='8'  /> S�ndlista f�r elever per grupp. <br />
<input type='radio' name='lista' value='9'  /> S�ndlista f�r samtliga elever. <br />
<input type='radio' name='lista' value='10'  /> S�ndlista f�r samtliga medlemmar. <br />
<input type='radio' name='lista' value='11'  /> Senast betalt terminsavgiften. <br />
<input type='radio' name='lista' value='12'  /> Lista till skolverket.<br />
<br />
<input type='image' title='Skapa lista' src='../images/b_enter.gif' alt='Skapa lista' />
</form>
HTMLCode;



require(TP_PAGES.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


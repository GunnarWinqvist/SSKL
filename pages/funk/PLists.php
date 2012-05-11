<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLists.php
// Anropas med 'lists' från index.php.
// Sidan ger ett antal val för att generera adresslistor, sändlistor etc.
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

// Lägg in din text för huvudkolumnen här nedan i HTML-kod.
$mainTextHTML = <<<HTMLCode
<form name='lists' action='?p=lists_ex' method='post'>
<h3>Välj vilken typ av lista du vill ha.</h3>
<input type='radio' name='lista' value='1'  /> Funktionärer telefon och adress. <br />
<input type='radio' name='lista' value='2'  /> Funktionärer telefon och e-post. <br />
<input type='radio' name='lista' value='3'  /> Elever kontaktuppgifter och personnummer. <br />
<input type='radio' name='lista' value='4'  /> Klasslista med lärare. <br />
<input type='radio' name='lista' value='5'  /> Sändlista för styrelsen. <br />
<input type='radio' name='lista' value='6'  /> Sändlista för lärare. <br />
<input type='radio' name='lista' value='7'  /> Sändlista för alla funktionärer. <br />
<input type='radio' name='lista' value='8'  /> Sändlista för elever per grupp. <br />
<input type='radio' name='lista' value='9'  /> Sändlista för samtliga elever. <br />
<input type='radio' name='lista' value='10'  /> Sändlista för samtliga medlemmar. <br />
<input type='radio' name='lista' value='11'  /> Senast betalt terminsavgiften. <br />
<input type='radio' name='lista' value='12'  /> Lista till skolverket.<br />
<br />
<input type='image' title='Skapa lista' src='../images/b_enter.gif' alt='Skapa lista' />
</form>
HTMLCode;



require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


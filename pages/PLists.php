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


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();   // Måste vara inloggad för att nå sidan.
$intFilter->UserIsAuthorisedOrDie('fnk');         // Måste vara minst funktionär för att nå sidan.


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
<input type='radio' name='lista' value='8'  /> Sändlista för elever. <br />
<input type='radio' name='lista' value='9'  /> Sändlista för samtliga medlemmar. <br />
<input type='radio' name='lista' value='10'  /> Senast betalt terminsavgiften. <br />
<input type='radio' name='lista' value='11'  /> Lista till skolverket. (Inte färdig än.)<br />
<br />
<input type='image' title='Skapa lista' src='../images/b_enter.gif' alt='Skapa lista' />
</form>
HTMLCode;



require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


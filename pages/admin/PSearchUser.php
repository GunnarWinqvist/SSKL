<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PSearchUser.php
// Anropas med 'search' fr�n index.php.
// P� den h�r sidan kan du s�ka efter en anv�ndare p� f�rnamn, efternamn eller account.
// Input:  
// Output:  'fornamn', 'efternamn', 'account'. 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn(); // M�ste vara inloggad f�r att n� sidan.
$intFilter->UserIsAuthorisedOrDie('adm');       // M�ste vara administrat�r f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// S�tt upp s�kformul�r.

$mainTextHTML = <<<HTMLCode
<form name='search' class='admin' action='?p=list_user' method='post'>
<h2>Skriv in det du vill s�ka p�. Del av namn eller hela namnet.</h2>
<p>Fyll inte i n�got om du vill lista alla i registret.</p>
<table class='formated'>
<tr><td>Anv�ndarnamn</td>
<td><input type='text' name='account' size='40' maxlength='20' value='' /></td></tr>
<tr><td>F�rnamn</td>
<td><input type='text' name='fornamn' size='40' maxlength='50' value='' /></td></tr>
<tr><td>Efternamn</td>
<td><input type='text' name='efternamn' size='40' maxlength='50' value='' /></td></tr>
<tr><td></td><td><input type='image' title='S�k' src='../images/b_enter.gif' alt='S�k' /></td></tr>
</table>
</form>
HTMLCode;

/*
<tr><td><div class='clear_button'>
    <a class='button' tabindex='1' href='javascript:document.search.submit();' onclick="this.blur();">
    <span>S�k</span></a></div>
</td></tr>
*/

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "S�k person";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


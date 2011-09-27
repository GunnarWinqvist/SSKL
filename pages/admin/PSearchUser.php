<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PSearchUser.php
// Anropas med 'search' från index.php.
// På den här sidan kan du söka efter en användare på förnamn, efternamn eller account.
// Input:  
// Output:  'fornamn', 'efternamn', 'account'. 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn(); // Måste vara inloggad för att nå sidan.
$intFilter->UserIsAuthorisedOrDie('adm');       // Måste vara administratör för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Sätt upp sökformulär.

$mainTextHTML = <<<HTMLCode
<form name='search' class='admin' action='?p=list_user' method='post'>
<h2>Skriv in det du vill söka på. Del av namn eller hela namnet.</h2>
<p>Fyll inte i något om du vill lista alla i registret.</p>
<table class='formated'>
<tr><td>Användarnamn</td>
<td><input type='text' name='account' size='40' maxlength='20' value='' /></td></tr>
<tr><td>Förnamn</td>
<td><input type='text' name='fornamn' size='40' maxlength='50' value='' /></td></tr>
<tr><td>Efternamn</td>
<td><input type='text' name='efternamn' size='40' maxlength='50' value='' /></td></tr>
<tr><td></td><td><input type='image' title='Sök' src='../images/b_enter.gif' alt='Sök' /></td></tr>
</table>
</form>
HTMLCode;

/*
<tr><td><div class='clear_button'>
    <a class='button' tabindex='1' href='javascript:document.search.submit();' onclick="this.blur();">
    <span>Sök</span></a></div>
</td></tr>
*/

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Sök person";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


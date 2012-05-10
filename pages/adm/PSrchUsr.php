<?php

/**
 * Search user (srch_usr)
 *
 * På den här sidan kan du söka efter en användare på förnamn, efternamn eller 
 * account.
 *
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


///////////////////////////////////////////////////////////////////////////////
// Sätt upp sökformulär.

$mainTextHTML = <<<HTMLCode
<form name='search' class='admin' action='?p=list_usr' method='post'>
<h2>Skriv in det du vill söka på. Del av namn eller hela namnet.</h2>
<p>Fyll inte i något om du vill lista alla i registret.</p>
<table class='formated'>
<tr><td>Användarnamn</td>
<td><input type='text' name='account' size='40' maxlength='20' value='' />
    </td></tr>
<tr><td>Förnamn</td>
<td><input type='text' name='fornamn' size='40' maxlength='50' value='' />
    </td></tr>
<tr><td>Efternamn</td>
<td><input type='text' name='efternamn' size='40' maxlength='50' value='' />
    </td></tr>
<tr><td></td><td><input type='image' title='Sök' src='../images/b_enter.gif' 
    alt='Sök' /></td></tr>
</table>
</form>
HTMLCode;


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Sök person";

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


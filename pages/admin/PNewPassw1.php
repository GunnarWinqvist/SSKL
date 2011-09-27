<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNewPassw1.php
// Anropas med 'new_passw1' från index.php.
// På sidan kan du ange en epostadress till vilken du vill ha ett nytt lösenord skickat. 
// Detta verkställs på PNewPassw2
// Input: -
// Output: 'ePost' som POST's.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för ePost.

$mainTextHTML = <<<HTMLCode
<form class=admin action='?p=new_passw2' method='post'>
<p>Fyll i din epostadress.</p>
<p><input type='text' name='ePost' size='40' maxlength='50' /></p>
<p><input type='image' title='Skicka' src='../images/b_enter.gif' alt='Skicka' /></p>
</form>
HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Nytt lösenord 1";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


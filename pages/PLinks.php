<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLinks.php
// Anropas med 'links' från index.php.
// Sidan innehåller en massa länkar.
// Input: 
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Länkar";

$mainTextHTML = <<<HTMLCode
<h2>{$pageTitle}</h2>
<h3>Sofia Distans</h3>
<p>Sofia Distansundervisning bedriver undervisning för årskurserna 6-9. Undervisningen sker via 
dator och de erbjuder undervisning i samtliga ämnen. Eftersom svenska skolföreningen inte ger några
betyg i svenska är Sofia Distans ett alternativ för de äldre barnen som behöver ett svenskabetyg för
att få gymnasiebehörighet.</p>
<a href='http://www.sofiadistans.nu/'>www.sofiadistans.nu</a>

<h3>Värmdö Distans</h3>
<p>Värmdö Distans har sedan 1999 haft Skolverkets uppdrag att anordna distansutbildning för svenska 
gymnasieungdomar utomlands.</p>
<a href='http://www.varmdodistans.se/'>www.varmdodistans.se</a>

<h3>Skolverket</h3>
<p>Skolverkets förordning för kompletterande svenskaundervisning i utlandet.</p>
<a href='http://www.skolverket.se/skolfs?id=929'>www.skolverket.se/skolfs?id=929</a>

<h3>Svenska Utlandsskolors Förening (SUF)</h3>
<p>SUF - är en intresseorganisation för föräldraföreningar eller andra huvudmän, som anordnar 
svensk, statsunderstödd, undervisning utomlands för närmare 6000 elever.</p>
<a href='http://www.suf.c.se'>www.suf.c.se</a>

HTMLCode;


require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


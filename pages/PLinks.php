<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLinks.php
// Anropas med 'links' fr�n index.php.
// Sidan inneh�ller en massa l�nkar.
// Input: 
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "L�nkar";

$mainTextHTML = <<<HTMLCode
<h2>{$pageTitle}</h2>
<h3>Sofia Distans</h3>
<p>Sofia Distansundervisning bedriver undervisning f�r �rskurserna 6-9. Undervisningen sker via 
dator och de erbjuder undervisning i samtliga �mnen. Eftersom svenska skolf�reningen inte ger n�gra
betyg i svenska �r Sofia Distans ett alternativ f�r de �ldre barnen som beh�ver ett svenskabetyg f�r
att f� gymnasiebeh�righet.</p>
<a href='http://www.sofiadistans.nu/'>www.sofiadistans.nu</a>

<h3>V�rmd� Distans</h3>
<p>V�rmd� Distans har sedan 1999 haft Skolverkets uppdrag att anordna distansutbildning f�r svenska 
gymnasieungdomar utomlands.</p>
<a href='http://www.varmdodistans.se/'>www.varmdodistans.se</a>

<h3>Skolverket</h3>
<p>Skolverkets f�rordning f�r kompletterande svenskaundervisning i utlandet.</p>
<a href='http://www.skolverket.se/skolfs?id=929'>www.skolverket.se/skolfs?id=929</a>

<h3>Svenska Utlandsskolors F�rening (SUF)</h3>
<p>SUF - �r en intresseorganisation f�r f�r�ldraf�reningar eller andra huvudm�n, som anordnar 
svensk, statsunderst�dd, undervisning utomlands f�r n�rmare 6000 elever.</p>
<a href='http://www.suf.c.se'>www.suf.c.se</a>

HTMLCode;


require(TP_PAGES.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


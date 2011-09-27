<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PMain.php
// Anropas med 'main' fr�n index.php.
// Sidan �r den f�rsta sida man kommer till p� webplatsen.
// 
// Input: 
// Output: 
//


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Framsidan";

// L�gg in din text f�r huvudkolumnen h�r nedan i HTML-kod.
$mainTextHTML = <<<HTMLCode
<h2>V�lkommen till Svenska Skolan i Kuala Lumpur!</h2>
<img class='floatRight' src='../images/klassrum.jpg' alt='Bild p� klassrum' />
<p>Svenska skolf�reningen i Kuala Lumpur erbjuder kompletterande undervisning i svenska spr�ket 
f�r barn i klass F - 6.</p>
<p>Spr�k �r inte bara ett s�tt att kommunicera, spr�k �r en l�nk till v�r kultur och identitet. 
Svenska skolans verksamhet har som syfte att uppr�tth�lla och utveckla barnens kunskaper inom svenska 
spr�ket, svenskt samh�lle och kultur samt att st�rka barnens identitet. </p>
<p>Skolan bedriver verksamhet i enlighet med skolverkets kursplan f�r kompletterade svensk 
undervisning.</p>
<p>Vi str�var efter att den svenska skolan ska vara b�de rolig och l�rorikt samt en m�tesplats f�r 
svensktalade barn. Svenska skolan i Kuala Lumpur v�lkomnar alla barn som lever i familjer d�r svenska 
�r ett levande spr�k och d�r minst en f�r�lder �r svensk medborgare.</p>
<p><i>"Undervisningen i svenska syftar till att st�rka elevernas uppfattning om sin identitet och 
vidmakth�lla och vidareutveckla s�dana kunskaper som underl�ttar f�r eleverna att komma tillbaka till 
Sverige."</i></p>
<p><small>(Ur Skolverkets information om Svensk utbildning av utlandssvenska barn och ungdomar och 
kompletterande svensk undervisning.)</small></p>

<img class='floatLeft' src='../images/rast.jpg' alt='Bild p� rast' />
<h3>Om skolan</h3>
<p>Svenska skolan i Kuala Lumpur drivs av Svenska Skolf�reningen i Kuala Lumpur, bildad 1979. Verksamheten 
finansieras med statsbidrag fr�n skolverket samt med f�r�ldrarnas bidrag i form av terminsavgifter.</p>
<p>F�reningens h�gsta beslutande organ �r �rsm�tet som h�lls under februari m�nad. Den l�pande verksamheten 
sk�ts av l�rarna och en styrelse som best�r av sex till �tta ledam�ter. Under �ret h�lls 9-10 styrelsem�ten 
och l�rarm�ten.</p>

<div class='clear'></div>
<h3>Undervisningen</h3>
<p>Svenska skolan erbjuder ett tv�-timmars lektionstillf�lle per vecka. Undervisningen sker p� onsdagar 
mellan kl 15,30 och 17,00. Vi startar varje lektionstillf�lle med att tr�ffas kl 15.00 och �ta mellanm�l 
tillsammans i kafeterian. Det �r en bra m�jlighet f�r barnen att l�ra k�nna varandra och knyta kontakter. 
De f�r tillf�lle att anv�nda svenska spr�ket �ven i andra situationer �n i klassrummet och att det ger 
barnen en bra �verg�ng fr�n undervisning p� engelska till svenska.</p>
<p>Lektionernas inneh�ll och uppl�gg f�r de olika grupperna varierar beroende p� �ldersgrupp och planeras 
i enlighet med Skolverkets kursplan f�r kompletterande svenskaundervisning. F�rutom den planlagda 
undervisningen s� anordnar f�reningen aktiviteter utanf�r skolan t ex br�nnboll, simdagar etc. 
Luciafirande/julavslutning p� skolan och traditionell skolavslutning �r andra uppskattade arrangemang.</p>

<img class='floatRight' src='../images/fika.jpg' alt='Bild p� fika' />
<h3>Lektioner och kursplaner</h3>
<p>Svenska skolan i Kuala Lumpur driver verksamhet i enlighet med Skolverkets riktlinjer f�r 
kompletterande svenskundervisning. V�ra byggstenar �r Skolverkets kursplan som vi konkretiserar i 
v�r �rsplanering utifr�n barnens behov och intresse.</p>
HTMLCode;


require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


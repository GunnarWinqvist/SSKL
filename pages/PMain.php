<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PMain.php
// Anropas med 'main' från index.php.
// Sidan är den första sida man kommer till på webplatsen.
// 
// Input: 
// Output: 
//


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Framsidan";

// Lägg in din text för huvudkolumnen här nedan i HTML-kod.
$mainTextHTML = <<<HTMLCode
<h2>Välkommen till Svenska Skolan i Kuala Lumpur!</h2>
<img class='floatRight' src='../images/klassrum.jpg' alt='Bild på klassrum' />
<p>Svenska skolföreningen i Kuala Lumpur erbjuder kompletterande undervisning i svenska språket 
för barn i klass F - 6.</p>
<p>Språk är inte bara ett sätt att kommunicera, språk är en länk till vår kultur och identitet. 
Svenska skolans verksamhet har som syfte att upprätthålla och utveckla barnens kunskaper inom svenska 
språket, svenskt samhälle och kultur samt att stärka barnens identitet. </p>
<p>Skolan bedriver verksamhet i enlighet med skolverkets kursplan för kompletterade svensk 
undervisning.</p>
<p>Vi strävar efter att den svenska skolan ska vara både rolig och lärorikt samt en mötesplats för 
svensktalade barn. Svenska skolan i Kuala Lumpur välkomnar alla barn som lever i familjer där svenska 
är ett levande språk och där minst en förälder är svensk medborgare.</p>
<p><i>"Undervisningen i svenska syftar till att stärka elevernas uppfattning om sin identitet och 
vidmakthålla och vidareutveckla sådana kunskaper som underlättar för eleverna att komma tillbaka till 
Sverige."</i></p>
<p><small>(Ur Skolverkets information om Svensk utbildning av utlandssvenska barn och ungdomar och 
kompletterande svensk undervisning.)</small></p>

<img class='floatLeft' src='../images/rast.jpg' alt='Bild på rast' />
<h3>Om skolan</h3>
<p>Svenska skolan i Kuala Lumpur drivs av Svenska Skolföreningen i Kuala Lumpur, bildad 1979. Verksamheten 
finansieras med statsbidrag från skolverket samt med föräldrarnas bidrag i form av terminsavgifter.</p>
<p>Föreningens högsta beslutande organ är årsmötet som hålls under februari månad. Den löpande verksamheten 
sköts av lärarna och en styrelse som består av sex till åtta ledamöter. Under året hålls 9-10 styrelsemöten 
och lärarmöten.</p>

<div class='clear'></div>
<h3>Undervisningen</h3>
<p>Svenska skolan erbjuder ett två-timmars lektionstillfälle per vecka. Undervisningen sker på onsdagar 
mellan kl 15,30 och 17,00. Vi startar varje lektionstillfälle med att träffas kl 15.00 och äta mellanmål 
tillsammans i kafeterian. Det är en bra möjlighet för barnen att lära känna varandra och knyta kontakter. 
De får tillfälle att använda svenska språket även i andra situationer än i klassrummet och att det ger 
barnen en bra övergång från undervisning på engelska till svenska.</p>
<p>Lektionernas innehåll och upplägg för de olika grupperna varierar beroende på åldersgrupp och planeras 
i enlighet med Skolverkets kursplan för kompletterande svenskaundervisning. Förutom den planlagda 
undervisningen så anordnar föreningen aktiviteter utanför skolan t ex brännboll, simdagar etc. 
Luciafirande/julavslutning på skolan och traditionell skolavslutning är andra uppskattade arrangemang.</p>

<img class='floatRight' src='../images/fika.jpg' alt='Bild på fika' />
<h3>Lektioner och kursplaner</h3>
<p>Svenska skolan i Kuala Lumpur driver verksamhet i enlighet med Skolverkets riktlinjer för 
kompletterande svenskundervisning. Våra byggstenar är Skolverkets kursplan som vi konkretiserar i 
vår årsplanering utifrån barnens behov och intresse.</p>
HTMLCode;


require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


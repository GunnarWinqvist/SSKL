<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PMap.php
// Anropas med 'map' fr�n index.php.
// Sidan inneh�ller adress och karta till f�reningens lokaler.
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
$pageTitle = "Karta";

$mainTextHTML = <<<HTMLCode
<img class='floatRight' src='../images/mkis.jpg' alt='Bild p� MKIS' />
<h2>Lokaler</h2>
<p>V�ra lektioner �ger rum p� Mont Kiara International School i Mont Kiara. Vi samlas i kafeterian 
p� nedersta v�ningen och d�rifr�n g�r vi tillsammans till v�ra klassrum. </p>
<p>Barn som kommer fr�n andra skolor m�ste ha p� sig sin skoluniform f�r att komma in p� MKIS 
f�r svenskalektionerna och beh�ver inte skaffa passerkort. F�r f�r�ldrar som vill komma in p� 
skolan kr�vs det dock passerkort. Detta g�rs p� MKISs expedition som ligger i skolans entr�plan.</p>
<p><b>Mont'Kiara International School</b><br />
Address: 22 Jalan Kiara, Mont'Kiara, Kuala Lumpur 50480, Malaysia<br />
Tel : +60 3 2093 8604<br />
Fax: +60 3 2093 6045<br />
Email: info@mkis.edu.my
</p>
<p>Karta �ver MKIS</p>
<img src='../images/mkiskarta.jpg' alt='MKIS karta' />
HTMLCode;


require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


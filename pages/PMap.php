<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PMap.php
// Anropas med 'map' från index.php.
// Sidan innehåller adress och karta till föreningens lokaler.
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
$pageTitle = "Karta";

$mainTextHTML = <<<HTMLCode
<img class='floatRight' src='../images/mkis.jpg' alt='Bild på MKIS' />
<h2>Lokaler</h2>
<p>Våra lektioner äger rum på Mont Kiara International School i Mont Kiara. Vi samlas i kafeterian 
på nedersta våningen och därifrån går vi tillsammans till våra klassrum. </p>
<p>Barn som kommer från andra skolor måste ha på sig sin skoluniform för att komma in på MKIS 
för svenskalektionerna och behöver inte skaffa passerkort. För föräldrar som vill komma in på 
skolan krävs det dock passerkort. Detta görs på MKISs expedition som ligger i skolans entréplan.</p>
<p><b>Mont'Kiara International School</b><br />
Address: 22 Jalan Kiara, Mont'Kiara, Kuala Lumpur 50480, Malaysia<br />
Tel : +60 3 2093 8604<br />
Fax: +60 3 2093 6045<br />
Email: info@mkis.edu.my
</p>
<p>Karta över MKIS</p>
<img src='../images/mkiskarta.jpg' alt='MKIS karta' />
HTMLCode;


require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


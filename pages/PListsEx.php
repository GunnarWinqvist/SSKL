<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PListsEx.php
// Anropas med 'lists_ex' från index.php.
// Genererar listor efter önskemål från sidan PLists.php.
// Input: 'lista'
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('fnk');         // Måste vara minst funktionär för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Input till sidan.
//

$lista    = isset($_POST['lista'])    ? $_POST['lista']     : NULL;

if ($debugEnable) $debug .= "Lista: ".$lista."<br /> \n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Definiera query utifrån vilken typ av lista man vill ha.

$dbAccess          = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';
$viewMalsman            = DB_PREFIX . 'ListaMalsman';

switch($lista) {

    case '1': //Funktionärer telefon och adress.
        $query = <<<Query
SELECT idPerson, fornamnPerson, efternamnPerson, funktionFunktionar, mobilPerson, telefonBostad, adressBostad, 
        stadsdelBostad FROM 
    (({$tablePerson} JOIN {$tableFunktionar} ON idPerson = funktionar_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    ORDER BY efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<tr><th>Namn</th><th></th><th>Funktion</th><th>Mobil</th><th>Hemtelefon</th><th>Adress</th><th>Stadsdel</th></tr>
HTMLCode;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td><td>{$row[2]}</td><td>{$row[3]}</td>
                <td>{$row[4]}</td><td>{$row[5]}</td><td>{$row[6]}</td><td>{$row[7]}</td></tr> \n";
        }
        $mainTextHTML .= "</table> \n";

    break;
    
    case '2': //Funktionärer telefon och e-post.
        $query = <<<Query
SELECT idPerson, fornamnPerson, efternamnPerson, funktionFunktionar, mobilPerson, telefonBostad, ePostPerson FROM 
    (({$tablePerson} JOIN {$tableFunktionar} ON idPerson = funktionar_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    ORDER BY efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<tr><th>Namn</th><th></th><th>Funktion</th><th>Mobil</th><th>Telefon</th><th>e-Post</th></tr>
HTMLCode;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td><td>{$row[2]}</td><td>{$row[3]}</td>
                <td>{$row[4]}</td><td>{$row[5]}</td><td>{$row[6]}</td></tr> \n";
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '3': //Elever Kontaktuppgifter och personnummer.
        $query = <<<Query
SELECT idPerson, gruppElev, fornamnPerson, efternamnPerson, arskursElev, skolaElev, personnummerElev, telefonBostad, 
        fornamnMalsman, efternamnMalsman, mobilMalsman, ePostMalsman  FROM 
    ((({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    JOIN {$viewMalsman} ON idPerson = idElev)
    ORDER BY gruppElev, efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<tr><th>Grupp</th><th>Elev</th><th></th><th>ÅK</th><th>Skola</th><th>Personnummer</th><th>Hemtelefon</th><th>Målsman</th><th></th>
        <th>Mobil</th><th>e-postadress</th></tr>
HTMLCode;
        $lastId = 0;
        $lastGroup = "";
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'>";
            if ($row[1] == $lastGroup) $mainTextHTML .= "<td></td>";
            else $mainTextHTML .= "<td>{$row[1]}</td>";
            if ($row[0] == $lastId) $mainTextHTML .= "<td></td><td></td><td></td><td></td><td></td><td></td>";
            else $mainTextHTML .= "<td>{$row[2]}</td><td>{$row[3]}</td><td>{$row[4]}</td><td>{$row[5]}</td><td>{$row[6]}</td><td>{$row[7]}</td>";
            $mainTextHTML .= "<td>{$row[8]}</td><td>{$row[9]}</td><td>{$row[10]}</td><td>{$row[11]}</td>";
            $mainTextHTML .= "</tr> \n";
            $lastId = $row[0];
            $lastGroup = $row[1];
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '4': //Klasslista med lärare
        // Lista eleverna
        $query = <<<Query
SELECT idElev, gruppElev, fornamnPerson, efternamnPerson, telefonBostad,  
        adressBostad, stadsdelBostad, mobilMalsman, ePostMalsman FROM 
    ((({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    JOIN {$viewMalsman} ON idPerson = idElev)
    ORDER BY gruppElev, efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<b>Elever</b>
<table>
<tr><th>Grupp</th><th>Elev</th><th></th><th>Hemtelefon</th><th>Adress</th><th></th><th>Mobil</th><th>e-postadress</th></tr>
HTMLCode;
        $lastId = 0;
        $lastGroup = "";
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'>";
            if ($row[1] == $lastGroup) $mainTextHTML .= "<td></td>";
            else $mainTextHTML .= "<td>{$row[1]}</td>";
            if ($row[0] == $lastId) $mainTextHTML .= "<td></td><td></td><td></td><td></td><td></td>";
            else $mainTextHTML .= "<td>{$row[2]}</td><td>{$row[3]}</td><td>{$row[4]}</td><td>{$row[5]}</td><td>{$row[6]}</td>";
            $mainTextHTML .= "<td>{$row[7]}</td><td>{$row[8]}</td>";
            $mainTextHTML .= "</tr> \n";
            $lastId = $row[0];
            $lastGroup = $row[1];
        }
        $mainTextHTML .= "</table> \n";
        // Lista lärarna
        $query = <<<Query
SELECT idPerson, funktionFunktionar, fornamnPerson, efternamnPerson, telefonBostad, mobilPerson, ePostPerson
    FROM (({$tablePerson} JOIN {$tableFunktionar} ON idPerson = funktionar_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    WHERE funktionFunktionar LIKE '%Lärare%'
    ORDER BY funktionFunktionar, efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML .= <<<HTMLCode
<b>Lärare</b>
<table>
<tr><th>Grupp</th><th>Namn</th><th></th><th>Hemtelefon</th><th>Mobil</th><th>e-postadress</th></tr>
HTMLCode;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td><td>{$row[2]}</td><td>{$row[3]}</td>
                <td>{$row[4]}</td><td>{$row[5]}</td><td>{$row[6]}</td></tr> \n";
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '5': //Sändlista för styrelsen.
        $query = <<<Query
SELECT idPerson, ePostPerson FROM ({$tablePerson} JOIN {$tableFunktionar} ON idPerson = funktionar_idPerson)
    WHERE funktionFunktionar LIKE '%Styrelse%' AND ePostPerson LIKE '%@%'
    ORDER BY efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<b>Sändlista styrelsen</b>
HTMLCode;
        $lastId = 0;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            if ($row[0] != $lastId) $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td></tr> \n";
            $lastId = $row[0];
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '6': //Sändlista för lärare.
        $query = <<<Query
SELECT idPerson, ePostPerson FROM ({$tablePerson} JOIN {$tableFunktionar} ON idPerson = funktionar_idPerson)
    WHERE funktionFunktionar LIKE '%Lärare%' AND ePostPerson LIKE '%@%'
    ORDER BY efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<b>Sändlista lärare</b>
HTMLCode;
        $lastId = 0;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            if ($row[0] != $lastId) $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td></tr> \n";
            $lastId = $row[0];
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '7': //Sändlista för funktionärer.
        $query = <<<Query
SELECT idPerson, ePostPerson FROM 
    (({$tablePerson} JOIN {$tableFunktionar} ON idPerson = funktionar_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    ORDER BY ePostPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<b>Sändlista funktionärer</b>
HTMLCode;
        $lastId = 0;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            if ($row[0] != $lastId) $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td></tr> \n";
            $lastId = $row[0];
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '8': //Sändlista för elever per grupp.
        $query = <<<Query
SELECT idElev, ePostMalsman, gruppElev FROM 
    (({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$viewMalsman} ON idPerson = idElev)
    WHERE ePostMalsman LIKE '%@%'
    ORDER BY gruppElev, ePostMalsman;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<b>Sändlista elever</b>
HTMLCode;
        $lastGroup = "";
        $lastEpost = "";
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            if ($row[2] != $lastGroup) $mainTextHTML .= "<tr style='font-size:12pt;'><td><b>{$row[2]}</b></td></tr> \n";
            if ($row[1] != $lastEpost) $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td></tr> \n";
            $lastEpost = $row[1];
            $lastGroup = $row[2];
        }
        $mainTextHTML .= "</table> <p>Markera de du vill skicka till och kopiera med ctrl+c. 
                            Lägg sedan in dem i adressfältet på ditt mejl med ctrl+v. </p> \n";
    break;
    
    case '9': //Sändlista för samtliga elever.
        $query = <<<Query
SELECT idElev, ePostMalsman FROM 
    (({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$viewMalsman} ON idPerson = idElev)
    WHERE ePostMalsman LIKE '%@%'
    ORDER BY ePostMalsman;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<b>Sändlista elever</b>
HTMLCode;
        $lastEpost = "";
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            if ($row[1] != $lastEpost) $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td></tr> \n";
            $lastEpost = $row[1];
        }
        $mainTextHTML .= "</table> <p>Markera de du vill skicka till och kopiera med ctrl+c. 
                            Lägg sedan in dem i adressfältet på ditt mejl med ctrl+v. </p> \n";
    break;
    
    case '10': //Sändlista för samtliga medlemmar.
        $query = <<<Query
SELECT idPerson, ePostPerson FROM {$tablePerson}
    WHERE ePostPerson LIKE '%@%'
    ORDER BY ePostPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<b>Sändlista samtliga medlemmar</b>
HTMLCode;
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'><td>{$row[1]}</td></tr> \n";
        }
        $mainTextHTML .= "</table> \n";
    break;

    case '11': //Senast betalt.
        $query = <<<Query
SELECT idPerson, gruppElev, fornamnPerson, efternamnPerson, betaltElev, telefonBostad, 
        fornamnMalsman, efternamnMalsman, mobilMalsman, ePostMalsman  FROM 
    ((({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    JOIN {$viewMalsman} ON idPerson = idElev)
    ORDER BY gruppElev, efternamnPerson;
Query;
        $result = $dbAccess->SingleQuery($query); 
        $mainTextHTML = <<<HTMLCode
<table>
<tr><th>Grupp</th><th>Elev</th><th></th><th>Betalt</th><th>Hemtelefon</th><th>Målsman</th><th></th>
        <th>Mobil</th><th>e-postadress</th></tr>
HTMLCode;
        $lastId = 0;
        $lastGroup = "";
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            $mainTextHTML .= "<tr style='font-size:10pt;'>";
            if ($row[1] == $lastGroup) $mainTextHTML .= "<td></td>";
            else $mainTextHTML .= "<td>{$row[1]}</td>";
            if ($row[0] == $lastId) $mainTextHTML .= "<td></td><td></td><td></td><td></td>";
            else $mainTextHTML .= "<td>{$row[2]}</td><td>{$row[3]}</td><td>{$row[4]}</td><td>{$row[5]}</td>";
            $mainTextHTML .= "<td>{$row[6]}</td><td>{$row[7]}</td><td>{$row[8]}</td><td>{$row[9]}</td>";
            $mainTextHTML .= "</tr> \n";
            $lastId = $row[0];
            $lastGroup = $row[1];
        }
        $mainTextHTML .= "</table> \n";
    break;
    
    case '12': //Lista till skolverket.
        $query = <<<Query
SELECT fornamnPerson, efternamnPerson, gruppElev, fornamnMalsman, efternamnMalsman, ePostMalsman, mobilMalsman,
        telefonBostad, adressBostad, stadsdelBostad FROM 
    ((({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$tableBostad} ON person_idBostad = idBostad)
    JOIN {$viewMalsman} ON idPerson = idElev)
    ORDER BY gruppElev, efternamnPerson;
Query;
        $header = "<th>Namn</th><th>Grupp</th><th>Målsman</th><th>e-postadress</th><th>Mobil</th><th>Telefon</th>
                  <th>Adress</th><th>Stadsdel</th>";
    break;
}    
/*
///////////////////////////////////////////////////////////////////////////////////////////////////
// Sök i databasen och lista resultatet i en tabell.
// Multiquery som returnerar en array med resultatset.

$statements = $dbAccess->MultiQuery($query, $arrayResultSets); 
if ($debugEnable) $debug .= "{$statements} querys kördes.<br /> \n"; 

// Förbered tabellen med rubriker.
$mainTextHTML = <<<HTMLCode
<table>
<tr>{$header}</tr>

HTMLCode;
$j = 0;
while ( isset($arrayResultSets[$j]) ) {
    $lastRow = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
    while($row = $arrayResultSets[$j]->fetch_row()) {
        $mainTextHTML .= "<tr>";
        if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
        for ( $i=0; $i<count($row); $i++ ) { 
            if ($row[$i] === $lastRow[$i]) {
                $mainTextHTML .= "<td style='font-size:10pt;'> '</td>";
            }
            else
                $mainTextHTML .= "<td style='font-size:10pt;'>{$row[$i]}</td>";
        }
        $mainTextHTML .= "</tr> \n";
        $lastRow = $row;
    }
    $arrayResultSets[$j]->close();
    $j++;
}

// Avsluta tabellen
$mainTextHTML .= <<<HTMLCode
</table>
HTMLCode;
*/
// Lägg till knappar för utskrift och close.
$mainTextHTML .= <<<HTMLCode
<a href='javascript:window.print()'>Skriv ut</a>
<a href='?p=lists'>Tillbaka</a>
HTMLCode;


/*
///////////////////////////////////////////////////////////////////////////////////////////////////
// Lägg till javascript för att göra en utskrift.
$printPage = $mainTextHTML;


// Öppna ett nytt fönster för utskrift.
$mainTextHTML .= <<<HTMLCode
<script language="JavaScript">
    function printList() {
        var w = window.open("");
        var d = w.document;
        d.open();
        d.write("{$printPage}");
        d.close();
    }
</script>
<button onclick="printList();">Skriv ut</button>
HTMLCode;
*/

/*
<A HREF="" onClick="window.open('windows.html', 'newWnd', 'width=500,height=400'); return false;">
Try this</A> 
*/


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Skriv ut sidan.
//

/*
$page = new CHTMLPage(); 
$pageTitle = "Lista användare";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);
*/

echo $mainTextHTML;
if (WS_DEBUG) echo $debug;

?>


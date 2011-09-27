<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PListUser.php
// Anropas med 'topics' från index.php.
// Sidan listar alla personer som stämmer med sökkriteriet.
// Input: 'fornamn', 'efternamn', 'account'.
// Output:  'id' om man har klickat på en person. 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn(); // Måste vara inloggad för att nå sidan.
$intFilter->UserIsAuthorisedOrDie('adm');       // Måste vara administratör för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Input till sidan.
//


$accountPerson    = isset($_POST['account'])    ? $_POST['account']     : NULL;
$fornamnPerson    = isset($_POST['fornamn'])    ? $_POST['fornamn']     : NULL;
$efternamnPerson  = isset($_POST['efternamn'])  ? $_POST['efternamn']   : NULL;

if ($debugEnable) $debug .= $accountPerson . $fornamnPerson . $efternamnPerson . "<br /> \n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Definiera query utifrån sökkriterie.

$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$accountPerson 		    = $dbAccess->WashParameter($accountPerson);
$fornamnPerson 		    = $dbAccess->WashParameter($fornamnPerson);
$efternamnPerson 		= $dbAccess->WashParameter($efternamnPerson);

$query = "SELECT * FROM {$tablePerson} ";
if      ($accountPerson)    $query .= "WHERE accountPerson   LIKE '%{$accountPerson}%'";
elseif  ($efternamnPerson)  $query .= "WHERE efternamnPerson LIKE '%{$efternamnPerson}%'";
elseif  ($fornamnPerson)    $query .= "WHERE fornamnPerson   LIKE '%{$fornamnPerson}%'";
$query .= " ORDER BY efternamnPerson;";

$result=$dbAccess->SingleQuery($query);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Sök i databasen och lista resultatet i en tabell.

// Förbered tabellen med rubriker.
$mainTextHTML = <<<HTMLCode
<div class='admin'>
<table>
<tr>
    <th>Id</th>
    <th>Användarnamn</th>
    <th>Behörighet</th>
    <th>Förnamn</th>
    <th>Efternamn</th>
</tr>
HTMLCode;

if ($result->num_rows) {
    while($row = $result->fetch_row()) {
        if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
        $mainTextHTML .= <<<HTMLCode
<tr>
    <td>{$row[0]}</td>
    <td>{$row[1]}</td>
    <td>{$row[3]}</td>
    <td>{$row[4]}</td>
    <td>{$row[5]}</td>
    <td><a title='Visa' href='?p=show_user&amp;id={$row[0]}' ><img src='../images/page.png' alt='Visa' /></a></td>
    <td><a title='Editera' href='?p=edit_user&amp;id={$row[0]}'><img src='../images/page_edit.png' alt='Ändra' /></a></td>
    <td><a title='Konto' href='?p=edit_account&amp;id={$row[0]}'><img src='../images/page_key.png' alt='Konto' /></a></td>
    <td><a title='Radera' href='?p=del_account&amp;id={$row[0]}' onclick="return confirm('Vill du radera användaren ur databasen?');">
            <img src='../images/page_delete.png' alt='Radera' /></a></td>
</tr>

HTMLCode;
    }
} else {
    $mainTextHTML .= <<<HTMLCode
<tr>
    <td></td><td>Inga sökresultat</td>
</tr>
HTMLCode;
}

// Avsluta tabellen
$mainTextHTML .= <<<HTMLCode
</table>
</div>
HTMLCode;

$result->close();

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Skriv ut sidan.
//
$page = new CHTMLPage(); 
$pageTitle = "Lista användare";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


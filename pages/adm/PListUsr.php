<?php

/**
 * List users (list_usr)
 *
 * Sidan listar alla personer som stämmer med sökkriteriet.
 * Input: 'fornamn', 'efternamn', 'account'.
 * Output:  'id' om man har klickat på en person. 
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
$intFilter->UserIsAuthorisedOrDie('adm'); //Must be adm to access the page.


/*
 * Prepare the data base.
 */
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';


/*
 * Handle input to the page.
 */
$accountPerson    = isset($_POST['account'])    ? $_POST['account']     : NULL;
$fornamnPerson    = isset($_POST['fornamn'])    ? $_POST['fornamn']     : NULL;
$efternamnPerson  = isset($_POST['efternamn'])  ? $_POST['efternamn']   : NULL;
$accountPerson 	  = $dbAccess->WashParameter($accountPerson);
$fornamnPerson 	  = $dbAccess->WashParameter($fornamnPerson);
$efternamnPerson  = $dbAccess->WashParameter($efternamnPerson);

if ($debugEnable) 
    $debug.=$accountPerson.$fornamnPerson.$efternamnPerson."<br />\r\n";


/*
 * Definiera query utifrån sökkriterie.
 */
$query = "SELECT * FROM {$tablePerson} ";
if      ($accountPerson)
    $query .= "WHERE accountPerson   LIKE '%{$accountPerson}%'";
elseif  ($efternamnPerson)  
    $query .= "WHERE efternamnPerson LIKE '%{$efternamnPerson}%'";
elseif  ($fornamnPerson)    
    $query .= "WHERE fornamnPerson   LIKE '%{$fornamnPerson}%'";
$query .= " ORDER BY efternamnPerson;";

$result=$dbAccess->SingleQuery($query);


/*
 * Sök i databasen och lista resultatet i en tabell.
 */

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
    <th>Senast inloggad</th>
</tr>
HTMLCode;

if ($result) {
    while($row = $result->fetch_row()) {
        if ($debugEnable) 
            $debug.="Query result: ".print_r($row, TRUE)."<br />\r\n";
        date_default_timezone_set(WS_TIMEZONE);
        $fTidPost = date("Y-m-d G:i", $row[9]);
        $mainTextHTML .= <<<HTMLCode
<tr>
    <td>{$row[0]}</td>
    <td>{$row[1]}</td>
    <td>{$row[3]}</td>
    <td>{$row[4]}</td>
    <td>{$row[5]}</td>
    <td>{$fTidPost}</td>
    <td><a title='Visa' href='?p=show_usr&amp;id={$row[0]}' >
        <img src='../images/page.png' alt='Visa' /></a></td>
    <td><a title='Editera' href='?p=edit_usr&amp;id={$row[0]}'>
        <img src='../images/page_edit.png' alt='Ändra' /></a></td>
    <td><a title='Konto' href='?p=edit_acnt&amp;id={$row[0]}'>
        <img src='../images/page_key.png' alt='Konto' /></a></td>
    <td><a title='Radera' href='?p=del_acnt&amp;id={$row[0]}' 
        onclick="return confirm('Vill du radera användaren ur databasen?');">
        <img src='../images/page_delete.png' alt='Radera' /></a></td>
</tr>

HTMLCode;
    }
    $result->close();
    
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


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Lista användare";

require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


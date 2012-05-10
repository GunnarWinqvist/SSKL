<?php

/**
 * Delete Account (del_acnt)
 *
 * Sidan raderar ett användarkonto ur alla tabeller i databasen inklusive 
 * blogginlägg.
 * Input: 'idPerson'
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
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';
$tableBlogg             = DB_PREFIX . 'Blogg';


/*
 * Handle input to the page.
 */
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL ;
$idPerson = $dbAccess->WashParameter($idPerson);
if ($debugEnable) $debug.="Input: idPerson=".$idPerson.
    " Authority = ".$_SESSION['authorityUser']."<br />\r\n";


/*
 * Radera idPerson från databasens alla tabeller.
 * Undersök först hur många fler användare som bor i personens bostad. 
 * Vänta med att ta bort bostaden till efter att personen är borttagen p g a 
 * 'foreign key constraint'.
 */
$query = "
    SELECT idPerson, person_idBostad 
    FROM {$tablePerson} 
    WHERE person_idBostad = (
        SELECT person_idBostad 
        FROM {$tablePerson} 
        WHERE idPerson = {$idPerson}
    );";

if ($result = $dbAccess->SingleQuery($query)) {
    $boende = $result->num_rows;
    $row = $result->fetch_row();
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br />\r\n";
    $idBostad = $row[1];
    $result->close();
} else $boende = 0;

// Ta bort användaren i resten av tabellerna. 
$totalStatements = 7; //Måste uppdateras manuellt om antalet statements ändras.
$query = <<<QUERY
DELETE FROM {$tableFunktionar} WHERE funktionar_idPerson = '{$idPerson}';
DELETE FROM {$tableMalsman} WHERE malsman_idPerson = '{$idPerson}';
DELETE FROM {$tableElev} WHERE elev_idPerson = '{$idPerson}';
DELETE FROM {$tableBlogg} WHERE post_idPerson = '{$idPerson}';
DELETE FROM {$tableRelation} WHERE relation_idElev = '{$idPerson}';
DELETE FROM {$tableRelation} WHERE relation_idMalsman = '{$idPerson}';
DELETE FROM {$tablePerson} WHERE idPerson = '{$idPerson}';
QUERY;

$statements = $dbAccess->MultiQueryNoResultSet($query);
if ($debugEnable) $debug.=$statements." statements av ".$totalStatements.
    " kördes.<br />\r\n"; 

// Om det bara är en användare som bor i bostaden så ta bort bostaden också.
if ($boende == 1) {
    $query = "DELETE FROM {$tableBostad} WHERE idBostad = '{$idBostad}';";
    $dbAccess->SingleQuery($query);
}

/**
 * Redirect to another page.
 */
$redirect = "srch_usr";

// Om i debugmode så visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    echo "<a title='Vidare' href='?p={$redirect}'>Vidare</a> <br />\n";
    exit();
}

header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


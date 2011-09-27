<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDelAccount.php
// Anropas med 'del_account' från index.php.
// Sidan raderar ett användarkonto ur alla tabeller i databasen inklusive blogginlägg.
// Input: 'idPerson'
// Output:
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // Måste vara minst adm för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Input till sidan.
//

$idPerson      = isset($_GET['id']) ? $_GET['id'] : NULL ;

if ($debugEnable) {
    $debug .= "Input: idPerson=" . $idPerson ."<br /> \n";
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Radera idPerson från databasens alla tabeller.

$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';
$tableBlogg             = DB_PREFIX . 'Blogg';
$idPerson 		        = $dbAccess->WashParameter($idPerson);

// Undersök först hur många fler användare som bor i personens bostad. Vänta med att ta bort
// bostaden till efter att personen är borttagen p g a 'foreign key constraint'.
$query = "SELECT idPerson, person_idBostad FROM {$tablePerson} WHERE person_idBostad = 
    (SELECT person_idBostad FROM {$tablePerson} WHERE idPerson = {$idPerson});";
$result = $dbAccess->SingleQuery($query);
$boende = $result->num_rows;
$row = $result->fetch_row();
if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
$idBostad = $row[1];
$result->close();

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
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} kördes.<br /> \n"; 

// Om det bara är en användare som bor i bostaden så ta bort bostaden också.
if ($boende == 1) {
    $query = "DELETE FROM {$tableBostad} WHERE idBostad = '{$idBostad}';";
    $dbAccess->SingleQuery($query);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page
//

// Om i debugmode så visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    exit();
}

$redirect = "list_user";
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


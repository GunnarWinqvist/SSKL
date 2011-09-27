<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDelAccount.php
// Anropas med 'del_account' fr�n index.php.
// Sidan raderar ett anv�ndarkonto ur alla tabeller i databasen inklusive blogginl�gg.
// Input: 'idPerson'
// Output:
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // M�ste vara minst adm f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Input till sidan.
//

$idPerson      = isset($_GET['id']) ? $_GET['id'] : NULL ;

if ($debugEnable) {
    $debug .= "Input: idPerson=" . $idPerson ."<br /> \n";
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Radera idPerson fr�n databasens alla tabeller.

$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';
$tableBlogg             = DB_PREFIX . 'Blogg';
$idPerson 		        = $dbAccess->WashParameter($idPerson);

// Unders�k f�rst hur m�nga fler anv�ndare som bor i personens bostad. V�nta med att ta bort
// bostaden till efter att personen �r borttagen p g a 'foreign key constraint'.
$query = "SELECT idPerson, person_idBostad FROM {$tablePerson} WHERE person_idBostad = 
    (SELECT person_idBostad FROM {$tablePerson} WHERE idPerson = {$idPerson});";
$result = $dbAccess->SingleQuery($query);
$boende = $result->num_rows;
$row = $result->fetch_row();
if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
$idBostad = $row[1];
$result->close();

// Ta bort anv�ndaren i resten av tabellerna. 
$totalStatements = 7; //M�ste uppdateras manuellt om antalet statements �ndras.
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
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} k�rdes.<br /> \n"; 

// Om det bara �r en anv�ndare som bor i bostaden s� ta bort bostaden ocks�.
if ($boende == 1) {
    $query = "DELETE FROM {$tableBostad} WHERE idBostad = '{$idBostad}';";
    $dbAccess->SingleQuery($query);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page
//

// Om i debugmode s� visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    exit();
}

$redirect = "list_user";
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


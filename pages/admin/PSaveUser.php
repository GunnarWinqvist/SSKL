<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PSaveUser.php
// Anropas med 'save_user' från index.php.
// Sidan sparar grunddata för en användare och skickar vidare till PEditUser2.
// Input: 'fornamn', 'efternamn', 'epost', 'mobil', 'idBostad', 'kopplabostad', 'editbostad', 'telefon', 
// 'adress', 'stadsdel', 'postnummer', 'stat', 'funk', 'funktion', 'malsman', 'natmalsman', 'pnmalsman', 
// 'elev', 'personnummer', 'grupp', 'nat', 'relation', 'id', som POST.
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen.
//
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta input om personen och uppdatera databasen.
//
$idPerson         = isset($_POST['id'])         ? $_POST['id']          : NULL;
$fornamnPerson    = isset($_POST['fornamn'])    ? $_POST['fornamn']     : NULL;
$efternamnPerson  = isset($_POST['efternamn'])  ? $_POST['efternamn']   : NULL;
$epostPerson      = isset($_POST['epost'])      ? $_POST['epost']       : NULL;
$mobilPerson      = isset($_POST['mobil'])      ? $_POST['mobil']       : NULL;

//Tvätta inparametrarna.
$idPerson 		  = $dbAccess->WashParameter($idPerson);
$fornamnPerson 	  = $dbAccess->WashParameter(strip_tags($fornamnPerson));
$efternamnPerson  = $dbAccess->WashParameter(strip_tags($efternamnPerson));
$epostPerson 	  = $dbAccess->WashParameter(strip_tags($epostPerson));
$mobilPerson      = $dbAccess->WashParameter(strip_tags($mobilPerson));

$query = <<<QUERY
UPDATE {$tablePerson} SET 
    fornamnPerson   = '{$fornamnPerson}',
    efternamnPerson = '{$efternamnPerson}',
    epostPerson     = '{$epostPerson}',
    mobilPerson     = '{$mobilPerson}'
    WHERE idPerson = '{$idPerson}';
QUERY;

$dbAccess->SingleQuery($query);


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta input för bostad om den finns, kolla om personen redan har en bostad och uppdatera databasen.
//
$editbostad           = isset($_POST['editbostad'])      ? $_POST['editbostad']      : NULL;

if ($editbostad) {
    // Hämta resten av inparametrarna. 
    $idBostad         = isset($_POST['idbostad'])    ? $_POST['idbostad']    : NULL;
    $telefonBostad    = isset($_POST['telefon'])     ? $_POST['telefon']     : NULL;
    $adressBostad     = isset($_POST['adress'])      ? $_POST['adress']      : NULL;
    $stadsdelBostad   = isset($_POST['stadsdel'])    ? $_POST['stadsdel']    : NULL;
    $postnummerBostad = isset($_POST['postnummer'])  ? $_POST['postnummer']  : NULL;
    $statBostad       = isset($_POST['stat'])        ? $_POST['stat']        : NULL;

    //Tvätta inparametrarna.
    $idBostad         = $dbAccess->WashParameter($idBostad);
    $telefonBostad    = $dbAccess->WashParameter(strip_tags($telefonBostad));
    $adressBostad     = $dbAccess->WashParameter(strip_tags($adressBostad));
    $stadsdelBostad   = $dbAccess->WashParameter(strip_tags($stadsdelBostad));
    $postnummerBostad = $dbAccess->WashParameter(strip_tags($postnummerBostad));
    $statBostad       = $dbAccess->WashParameter(strip_tags($statBostad));

    if ($idBostad) { //Om den finns så uppdatera.
        $query = <<<QUERY
UPDATE {$tableBostad} SET 
    telefonBostad    = '{$telefonBostad}',
    adressBostad     = '{$adressBostad}',
    stadsdelBostad   = '{$stadsdelBostad}',
    postnummerBostad = '{$postnummerBostad}',
    statBostad       = '{$statBostad}'
    WHERE idBostad   = '{$idBostad}';
QUERY;
        $dbAccess->SingleQuery($query);
    } else { //Annars läggs en ny bostad in.
        $query = <<<QUERY
INSERT INTO {$tableBostad} (telefonBostad, adressBostad, stadsdelBostad, postnummerBostad, statBostad)
    VALUES ('{$telefonBostad}', '{$adressBostad}', '{$stadsdelBostad}', '{$postnummerBostad}', 
        '{$statBostad}');
QUERY;
        $dbAccess->SingleQuery($query);
        // Koppla bostaden till personen.
        $idBostad = $dbAccess->LastId();
        $query = "UPDATE {$tablePerson} SET person_idBostad = '{$idBostad}' WHERE idPerson = '{$idPerson}';";
        $dbAccess->SingleQuery($query);
    }    
}

  
///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta input för bostad om den finns, kolla om personen redan har en bostad och uppdatera databasen.
//
$kopplaBostad = isset($_POST['kopplabostad'])      ? $_POST['kopplabostad']      : NULL;
if ($kopplaBostad) {
    $idBostad     = isset($_POST['idnewbostad'])    ? $_POST['idnewbostad']    : NULL;
    $idBostad     = $dbAccess->WashParameter(strip_tags($idBostad));
    $kopplaBostad = $dbAccess->WashParameter(strip_tags($kopplaBostad));
    if ($debugEnable) $debug .= "idBostad: ".$idBostad."<br /> \n";
    $query = "UPDATE {$tablePerson} SET person_idBostad = '{$idBostad}' WHERE idPerson  = '{$idPerson}';";
    $dbAccess->SingleQuery($query);
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta input för funktionär om den finns, kolla om personen är funktionär redan och uppdatera 
// databasen.
//

$addfunk  = isset($_POST['addfunk'])     ? $_POST['addfunk']     : NULL;
$editfunk = isset($_POST['editfunk'])    ? $_POST['editfunk']    : NULL;
$delfunk  = isset($_POST['delfunk'])     ? $_POST['delfunk']     : NULL;

if ($addfunk) {
    //Hämta och tvätta inparametrarna.
    $funktion = isset($_POST['funktion']) ? $_POST['funktion'] : NULL;
    $funktion = $dbAccess->WashParameter(strip_tags($funktion));

    $query = <<<QUERY
INSERT INTO {$tableFunktionar} (funktionar_idPerson, funktionFunktionar)
    VALUES ('{$idPerson}', '{$funktion}');
QUERY;
    $dbAccess->SingleQuery($query);
}

if ($editfunk) {
    $idFunktion = $dbAccess->WashParameter($editfunk);
    $funktion = "funk".$idFunktion;
    $funktion = isset($_POST[$funktion]) ? $_POST[$funktion] : NULL;
    $funktion = $dbAccess->WashParameter(strip_tags($funktion));
    if ($debugEnable) $debug .= "idFunktion=".$idFunktion." funktion=".$funktion."<br /> \n";
    $query = <<<QUERY
UPDATE {$tableFunktionar} SET 
    funktionFunktionar = '{$funktion}'
    WHERE idFunktion = '{$idFunktion}';
QUERY;
    $dbAccess->SingleQuery($query);
}

if ($delfunk) {
    $idFunktion = $dbAccess->WashParameter($delfunk);
    $query = "DELETE FROM {$tableFunktionar} WHERE idFunktion = '{$idFunktion}';";
    $dbAccess->SingleQuery($query);
}



///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta input för målsman om den finns, kolla om personen är målsman redan och uppdatera 
// databasen.
//

$malsman             = isset($_POST['malsman'])   ? $_POST['malsman']   : NULL;

if ($malsman) {
    //Hämta och tvätta inparametrarna.
    $nationalitetMalsman = isset($_POST['natmalsman'])? $_POST['natmalsman']: NULL;
    $personnummerMalsman = isset($_POST['pnmalsman']) ? $_POST['pnmalsman'] : NULL;
    $nationalitetMalsman = $dbAccess->WashParameter(strip_tags($nationalitetMalsman));
    $personnummerMalsman = $dbAccess->WashParameter(strip_tags($personnummerMalsman));
    
    // Kolla om personen redan finns som målsman.
    $query = "SELECT * FROM {$tableMalsman} WHERE malsman_idPerson = '{$idPerson}';";
    if ($dbAccess->SingleQuery($query)) { //Om den finns så uppdatera.
        $query = <<<QUERY
UPDATE {$tableMalsman} SET 
    nationalitetMalsman       = '{$nationalitetMalsman}',
    personnummerMalsman = '{$personnummerMalsman}'
    WHERE malsman_idPerson = '{$idPerson}';
QUERY;

    } else { //Annars läggs en ny målsman in.
        $query = <<<QUERY
INSERT INTO {$tableMalsman} (malsman_idPerson, nationalitetMalsman, personnummerMalsman)
    VALUES ('{$idPerson}', '{$nationalitetMalsman}', '{$personnummerMalsman}');
QUERY;
    }
    $dbAccess->SingleQuery($query);
}
    

///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta input för elev om den finns, kolla om personen är elev redan och uppdatera databasen.
//
$elev       = isset($_POST['elev'])       ? $_POST['elev']       : NULL;
$addMalsman = isset($_POST['addmalsman']) ? $_POST['addmalsman'] : NULL;
$delMalsman = isset($_POST['delmalsman']) ? $_POST['delmalsman'] : NULL;
    
if ($elev) {
    //Hämta och tvätta inparametrarna.
    $personnummerElev = isset($_POST['personnummer']) ? $_POST['personnummer'] : NULL;
    $gruppElev        = isset($_POST['grupp'])        ? $_POST['grupp']        : NULL;
    $nationalitetElev = isset($_POST['nat'])          ? $_POST['nat']          : NULL;
    $arskursElev      = isset($_POST['grade'])        ? $_POST['grade']        : NULL;
    $betaltElev       = isset($_POST['pay'])          ? $_POST['pay']          : NULL;
    $personnummerElev = $dbAccess->WashParameter(strip_tags($personnummerElev));
    $gruppElev        = $dbAccess->WashParameter(strip_tags($gruppElev));
    $nationalitetElev = $dbAccess->WashParameter(strip_tags($nationalitetElev));
    $arskursElev      = $dbAccess->WashParameter(strip_tags($arskursElev));
    $betaltElev       = $dbAccess->WashParameter(strip_tags($betaltElev));

    // Kolla om personen redan finns som elev.
    $query = "SELECT * FROM {$tableElev} WHERE elev_idPerson = '{$idPerson}';";
    if ($dbAccess->SingleQuery($query)) { //Om den finns så uppdatera.
        $query = <<<QUERY
UPDATE {$tableElev} SET 
    personnummerElev = '{$personnummerElev}',
    gruppElev = '{$gruppElev}',
    nationalitetElev = '{$nationalitetElev}',
    arskursElev = '{$arskursElev}',
    betaltElev = '{$betaltElev}'
    WHERE elev_idPerson = '{$idPerson}';
QUERY;

    } else { //Annars läggs en ny elev in.
        $query = <<<QUERY
INSERT INTO {$tableElev} (elev_idPerson, personnummerElev, gruppElev, nationalitetElev, arskursElev, betaltElev)
    VALUES ('{$idPerson}', '{$personnummerElev}', '{$gruppElev}', '{$nationalitetElev}', '{$arskursElev}', '{$betaltElev}');
QUERY;
    }
    $dbAccess->SingleQuery($query);
}

if ($addMalsman) { //Lägg till en ny målsman.
    //Hämta och tvätta inparametrarna.
    $newMalsman = isset($_POST['newmalsman']) ? $_POST['newmalsman'] : NULL;
    $newMalsman = $dbAccess->WashParameter($newMalsman);

    // Kolla om relationen med målsman redan finns.
    $query = "SELECT * FROM {$tableRelation} WHERE relation_idElev = '{$idPerson}' 
        AND relation_idMalsman = '{$newMalsman}';";
    $result = $dbAccess->SingleQuery($query);
    if (!$result->num_rows) { //Om den inte finns så lägg till.
        $query = <<<QUERY
INSERT INTO {$tableRelation} (relation_idElev, relation_idMalsman)
    VALUES ('{$idPerson}', '{$newMalsman}');
QUERY;
        $dbAccess->SingleQuery($query);
        
        // Ge eleven samma bostad som målsmanen som default.
        $query = "SELECT person_idBostad FROM {$tablePerson} WHERE idPerson = {$newMalsman};";
        $result = $dbAccess->SingleQuery($query);
        $array = $result->fetch_row();
        $idBostad = $array[0];
        $query = "UPDATE {$tablePerson} SET person_idBostad = '{$idBostad}' WHERE idPerson = '{$idPerson}';";
        $dbAccess->SingleQuery($query);
    }
    $result->close();
}
   
if ($delMalsman) { //Lägg till en ny målsman.
    //Hämta och tvätta inparametrarna.
    $delMalsman = $dbAccess->WashParameter($delMalsman);
    $query = "DELETE FROM {$tableRelation} WHERE relation_idMalsman = '{$delMalsman}';";
    $dbAccess->SingleQuery($query);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Redirect to another page
//

// Om i debugmode så visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    exit();
}

// $redirect sätts i PEditUser.php.
$redirect = "show_user&id=".$idPerson;
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


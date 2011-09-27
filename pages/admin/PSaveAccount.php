<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PSaveAccount.php
// Anropas med 'save_account' fr�n index.php.
// Sidan sparar grunddata f�r ett account och skickar vidare till PShowUser.
// Input: 'id', 'account', 'password1', 'password2', 'behorighet', 'send', 'redirect' som POSTs.
// Output: 'id'
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Kolla beh�righet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen.
//
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableElev              = DB_PREFIX . 'Elev';
$viewMalsman            = DB_PREFIX . 'ListaMalsman';


///////////////////////////////////////////////////////////////////////////////////////////////////
// H�mta account-input f�r personen, tv�tta parametrarna, kolla om personen finns ($idPerson �r 
// satt) och uppdatera databasen.
//
$idPerson         = isset($_POST['id'])         ? $_POST['id']          : NULL;
$accountPerson    = isset($_POST['account'])    ? $_POST['account']     : NULL;
$behorighetPerson = isset($_POST['behorighet']) ? $_POST['behorighet']  : NULL;
$password1Person  = isset($_POST['password1'])  ? $_POST['password1']   : NULL;
$password2Person  = isset($_POST['password2'])  ? $_POST['password2']   : NULL;
$redirect         = isset($_POST['redirect'])   ? $_POST['redirect']    : NULL;
$send             = isset($_POST['send'])       ? $_POST['send']        : NULL;

//Om l�senorden inte �r ifyllda eller �r olika s� avbryt och g� till edit_account.
if (!$password1Person || ($password1Person != $password2Person)) { 
    $_SESSION['errorMessage'] = "Fel p� l�senordet!";
    header('Location: ' . WS_SITELINK . $redirect);
    exit;
}

//Tv�tta inparametrarna.
$idPerson 		  = $dbAccess->WashParameter($idPerson);
$accountPerson 	  = $dbAccess->WashParameter(strip_tags($accountPerson));
$behorighetPerson = $dbAccess->WashParameter(strip_tags($behorighetPerson));
$password1Person  = $dbAccess->WashParameter(strip_tags($password1Person));

if ($idPerson) { //Om anv�ndaren redan finns s� uppdateras databasen.
    $query = <<<QUERY
UPDATE {$tablePerson} SET 
    accountPerson = '{$accountPerson}',
    passwordPerson = md5('{$password1Person}'),
    behorighetPerson = '{$behorighetPerson}'
    WHERE idPerson = '{$idPerson}';
QUERY;
} else { //Annars l�ggs en ny anv�ndare in.
    $query = <<<QUERY
INSERT INTO {$tablePerson} (accountPerson, passwordPerson, behorighetPerson)
    VALUES ('{$accountPerson}', md5('{$password1Person}'), '{$behorighetPerson}');
QUERY;
}
$dbAccess->SingleQuery($query);

// Om $idPerson inte inneh�ller n�got �r det en ny anv�ndare. H�mta d� dennes id.
if (!$idPerson)  $idPerson = $dbAccess->LastId();
if ($debugEnable) $debug .= "idPerson: " . $idPerson . "<br /> \n";

// Skicka l�senordet i mejl om detta �r beg�rt.
if ($send) {
    // H�mta mejladress. fr�n personen eller dess m�lsman.
    $query = "SELECT ePostPerson FROM {$tablePerson} WHERE idPerson = '{$idPerson}';";
    $result = $dbAccess->SingleQuery($query);
    $row = $result->fetch_object();
    $result->close();
    if ($row->ePostPerson) {
        $eMailAdr = $row->ePostPerson;
    } else {
        $query = <<<QUERY
SELECT ePostMalsman FROM 
    (({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$viewMalsman} ON idPerson = idElev)
    WHERE idElev = '{$idPerson}';
QUERY;
        $result = $dbAccess->SingleQuery($query);
        $row = $result->fetch_object();
        $result->close();
        if (isset($row->ePostMalsman)) {
            $eMailAdr = $row->ePostMalsman;
        } else {
            $eMailAdr = "";
        }
    }
    if ($eMailAdr) {
        $subject = "Nytt l�senord";
        $text = <<<Text
Din anv�ndarinformation till Svenska skolf�reningens hemsida.
Anv�ndarnamn: {$accountPerson}
L�senord: {$password1Person}

Du kan sj�lv logga in p� sidan och �ndra ditt l�senord.
Text;
        mail( $eMailAdr, $subject, $text);
    } else {
        $_SESSION['errorMessage'] = "Ingen mejladress att skicka l�senordet till!";
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Redirect to another page
//

// Om i debugmode s� visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    exit();
}

// $redirect s�tts i PEditAccount.php.
$redirect = "show_user&id=".$idPerson;
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


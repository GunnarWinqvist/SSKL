<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PSaveAccount.php
// Anropas med 'save_account' från index.php.
// Sidan sparar grunddata för ett account och skickar vidare till PShowUser.
// Input: 'id', 'account', 'password1', 'password2', 'behorighet', 'send', 'redirect' som POSTs.
// Output: 'id'
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
$tableElev              = DB_PREFIX . 'Elev';
$viewMalsman            = DB_PREFIX . 'ListaMalsman';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta account-input för personen, tvätta parametrarna, kolla om personen finns ($idPerson är 
// satt) och uppdatera databasen.
//
$idPerson         = isset($_POST['id'])         ? $_POST['id']          : NULL;
$accountPerson    = isset($_POST['account'])    ? $_POST['account']     : NULL;
$behorighetPerson = isset($_POST['behorighet']) ? $_POST['behorighet']  : NULL;
$password1Person  = isset($_POST['password1'])  ? $_POST['password1']   : NULL;
$password2Person  = isset($_POST['password2'])  ? $_POST['password2']   : NULL;
$redirect         = isset($_POST['redirect'])   ? $_POST['redirect']    : NULL;
$send             = isset($_POST['send'])       ? $_POST['send']        : NULL;

//Om lösenorden inte är ifyllda eller är olika så avbryt och gå till edit_account.
if (!$password1Person || ($password1Person != $password2Person)) { 
    $_SESSION['errorMessage'] = "Fel på lösenordet!";
    header('Location: ' . WS_SITELINK . $redirect);
    exit;
}

//Tvätta inparametrarna.
$idPerson 		  = $dbAccess->WashParameter($idPerson);
$accountPerson 	  = $dbAccess->WashParameter(strip_tags($accountPerson));
$behorighetPerson = $dbAccess->WashParameter(strip_tags($behorighetPerson));
$password1Person  = $dbAccess->WashParameter(strip_tags($password1Person));

if ($idPerson) { //Om användaren redan finns så uppdateras databasen.
    $query = <<<QUERY
UPDATE {$tablePerson} SET 
    accountPerson = '{$accountPerson}',
    passwordPerson = md5('{$password1Person}'),
    behorighetPerson = '{$behorighetPerson}'
    WHERE idPerson = '{$idPerson}';
QUERY;
} else { //Annars läggs en ny användare in.
    $query = <<<QUERY
INSERT INTO {$tablePerson} (accountPerson, passwordPerson, behorighetPerson)
    VALUES ('{$accountPerson}', md5('{$password1Person}'), '{$behorighetPerson}');
QUERY;
}
$dbAccess->SingleQuery($query);

// Om $idPerson inte innehåller något är det en ny användare. Hämta då dennes id.
if (!$idPerson)  $idPerson = $dbAccess->LastId();
if ($debugEnable) $debug .= "idPerson: " . $idPerson . "<br /> \n";

// Skicka lösenordet i mejl om detta är begärt.
if ($send) {
    // Hämta mejladress. från personen eller dess målsman.
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
        $subject = "Nytt lösenord";
        $text = <<<Text
Din användarinformation till Svenska skolföreningens hemsida.
Användarnamn: {$accountPerson}
Lösenord: {$password1Person}

Du kan själv logga in på sidan och ändra ditt lösenord.
Text;
        mail( $eMailAdr, $subject, $text);
    } else {
        $_SESSION['errorMessage'] = "Ingen mejladress att skicka lösenordet till!";
    }
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

// $redirect sätts i PEditAccount.php.
$redirect = "show_user&id=".$idPerson;
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


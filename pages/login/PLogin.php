<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLoginEx.php
// Anropas med 'login_ex' från index.php.
// Sidan genomför en inloggning.
// Input är 'account', 'password', 'redirect.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Döda eventuella gamla sessioner.
$hitCounter = $_SESSION["hitCounter"]; //Spara hitCounter innan vi dödar sessionen.
require_once(TP_SOURCE . 'FDestroySession.php');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Ta hand om inparametrar till sidan.

$accountPerson = isset($_POST['account']) ? $_POST['account'] : NULL;
$passwordPerson = isset($_POST['password']) ? $_POST['password'] : NULL;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'main';

if ($debugEnable) $debug.="Input: account={$accountPerson} password={$passwordPerson} redirect={$redirect}<br /> \n";


// Förbered databasen.

$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableFunktionar    = DB_PREFIX . 'Funktionar';

// Tvätta inparametrarna.
$accountPerson 		= $dbAccess->WashParameter($accountPerson);
$passwordPerson 	= $dbAccess->WashParameter($passwordPerson);


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla om account med det lösenordet finns i databasen och använd resultatet för att skapa en 
// session med userId, userPassword och behörighet.

$query = <<<Query
SELECT * FROM {$tablePerson}
WHERE
	accountPerson   = '{$accountPerson}' AND
	passwordPerson 	= md5('{$passwordPerson}')
;
Query;

session_start(); // Återstartar efter stängningen ovan.
session_regenerate_id();
$_SESSION["hitCounter"] = $hitCounter; //Återställ hitCounter så besökaren inte räknas dubbelt.

if ($result=$dbAccess->SingleQuery($query)) {
    $row = $result->fetch_object();
    if ($debugEnable) $debug .= print_r($row, TRUE);
    $_SESSION['idUser']            = $row->idPerson;
    $idPerson                      = $row->idPerson;
    $_SESSION['accountUser']       = $row->accountPerson;  
    $_SESSION['nameUser']          = $row->fornamnPerson;
    $_SESSION['authorityUser']     = $row->behorighetPerson;
    if ($_SESSION['authorityUser'] != 'adm') {
        // Kolla om personen är funktionär. I så fall sätt authority till fnk.
        $query = "SELECT * FROM {$tableFunktionar} WHERE funktionar_idPerson = '{$idPerson}' ;";
        if ($dbAccess->SingleQuery($query)) $_SESSION['authorityUser'] = 'fnk';
    }
    $result->close();
    
    // Skriv in senast inloggad i databasen. 
    $time = time();
    $query = "UPDATE {$tablePerson} SET senastInloggadPerson = '{$time}' WHERE idPerson  = '{$idPerson}';";
    $dbAccess->SingleQuery($query);

} else {
    $_SESSION['errorMessage']      = "Inloggningen misslyckades";
    $_POST['redirect']             = $redirect;
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page

// Om i debugmode så visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    echo "<a title='Vidare' href='?p={$redirect}'>Vidare</a> <br />\n";
    exit();
}

header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;

?>


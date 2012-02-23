<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLogout.php
// Anropas med 'logout' fr�n index.php.
// Sidan genomf�r en utloggning.
// Fr�n denna sida kommer man till PLogin.php.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// D�da sessionen.
//
$hitCounter = $_SESSION["hitCounter"]; //Spara hitCounter innan vi d�dar sessionen.
require_once(TP_SOURCEPATH . 'FDestroySession.php');

// Starta en session och �terst�ll hitCounter s� att inte bes�karen r�knas dubbelt.
session_start();
session_regenerate_id();
$_SESSION["hitCounter"] = $hitCounter;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page
//

// Testar en ny variant. $redirect s�tts till den sidan som skickade till logout.
//$redirect = $_SERVER['HTTP_REFERER'];
$redirect = "main";

// Om i debugmode s� visa och avbryt innan redirect.
if ($debugEnable) {
    echo "<p>Logout genomf�rd.</p>";
    echo "<a title='Vidare' href='?p={$redirect}'>Vidare</a> <br />\n";
    exit();
}

// $redirect s�tts i .
//$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'login';
header('Location: ' . WS_SITELINK . "?p={$redirect}");
//header('Location: ' . $redirect);
exit;

?>


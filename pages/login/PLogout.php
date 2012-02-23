<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLogout.php
// Anropas med 'logout' från index.php.
// Sidan genomför en utloggning.
// Från denna sida kommer man till PLogin.php.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Döda sessionen.
//
$hitCounter = $_SESSION["hitCounter"]; //Spara hitCounter innan vi dödar sessionen.
require_once(TP_SOURCEPATH . 'FDestroySession.php');

// Starta en session och återställ hitCounter så att inte besökaren räknas dubbelt.
session_start();
session_regenerate_id();
$_SESSION["hitCounter"] = $hitCounter;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page
//

// Testar en ny variant. $redirect sätts till den sidan som skickade till logout.
//$redirect = $_SERVER['HTTP_REFERER'];
$redirect = "main";

// Om i debugmode så visa och avbryt innan redirect.
if ($debugEnable) {
    echo "<p>Logout genomförd.</p>";
    echo "<a title='Vidare' href='?p={$redirect}'>Vidare</a> <br />\n";
    exit();
}

// $redirect sätts i .
//$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'login';
header('Location: ' . WS_SITELINK . "?p={$redirect}");
//header('Location: ' . $redirect);
exit;

?>


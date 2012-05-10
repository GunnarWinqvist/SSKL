<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PLogout.php (logout)
// 
// This page performes a logout from the session, regenerates a session and send you to redirect.
// Input: 'redirect'
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Check that the page is reached from the front controller.
//
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kill the session.

// Save hitCounter before we kill the session.
if (WS_HITCOUNTER) $hitCounter = $_SESSION["hitCounter"]; 
require_once(TP_SOURCE . 'FDestroySession.php');

// Start a new session and reinitiate hitCounter so that a visitor isn't counted doubble.
session_start();
session_regenerate_id();
if (WS_HITCOUNTER) $_SESSION["hitCounter"] = $hitCounter;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page if set.
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'main';

// If in debug mode show debug and then exit before redirect.
if ($debugEnable) {
    echo "<p>Logout performed.</p>";
    echo $debug;
    echo "<a title='Continue' href='?p={$redirect}'>Continue</a> <br />\n";
    exit();
}

header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;

?>


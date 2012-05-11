<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDelPost.php
// Anropas med 'del_post' från index.php.
// Sidan raderar ett blogginlägg i databasen.
// Input: 'idPost'
// Output:
// 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();
$intFilter->UserIsAuthorisedOrDie('fnk');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Input till sidan plus rensa bort HTML-taggar.
//

$idPost      = isset($_GET['idPost']) ? $_GET['idPost'] : NULL ;
$post_idPerson = $_SESSION['idUser'];

if ($debugEnable) {
    $debug .= "Input: idPost=" . $idPost . ", post_idPerson=" . $post_idPerson."<br /> \n";
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Radera idPost från databasen om du är ägare eller adm.

$dbAccess       = new CdbAccess();
$tableBlogg	    = DB_PREFIX . 'Blogg';
$idPost 		= $dbAccess->WashParameter($idPost);

// Kolla först om du är ägare till posten.
$query = "SELECT post_idPerson FROM {$tableBlogg} WHERE idPost = '{$idPost}'";
$result = $dbAccess->SingleQuery($query);
$row = $result->fetch_object();
$postOwner = $row->post_idPerson;
$result->close();

if (($_SESSION['idUser'] == $postOwner) || ($_SESSION['authorityUser'] == "adm")) {
    $query = "DELETE FROM {$tableBlogg} WHERE idPost  = '{$idPost}'";
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

// $redirect sätts i PNewTopic.php.
$redirect = "topics";
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


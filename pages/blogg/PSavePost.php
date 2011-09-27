<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PSavePost.php
// Anropas med 'save_post' från index.php.
// Sidan sparar ett nytt eller uppdaterat inlägg i databasen.
// Input: 'idPost', 'titelPost', 'textPost', 'internPost' som POST.
// Output:
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('fnk');         // Måste vara minst funktionär för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Input till sidan.
//

$idPost      = isset($_POST['idPost']) ? $_POST['idPost'] : NULL ;
$titelPost   = isset($_POST['titelPost']) ? $_POST['titelPost'] : NULL ;
$textPost    = isset($_POST['textPost']) ? $_POST['textPost'] : NULL ;
$internPost  = isset($_POST['internPost']) ? $_POST['internPost'] : 0 ;
$post_idPerson = $_SESSION['idUser'];

if ($debugEnable)
    $debug .= "idPost=".$idPost." idPerson=".$post_idPerson." titelPost=".$titelPost." textPost=".$textPost
                ." internPost=".$internPost."<br /> \n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Uppdatera idPost om den är satt annars skapa ett nytt inlägg.

$dbAccess       = new CdbAccess();
$tableBlogg	    = DB_PREFIX . 'Blogg';
$tidPost        = time();

//Tvätta inparametrarna.
$idPost 	 = $dbAccess->WashParameter($idPost);
$internPost  = $dbAccess->WashParameter($internPost);
$tagsAllowed = '<h1><h2><h3><h4><h5><h6><p><a><br><i><em><b><strong><li><ol><ul><a><style><font><span>';
$titelPost   = $dbAccess->WashParameter(strip_tags($titelPost));
$textPost 	 = $dbAccess->WashParameter(strip_tags($textPost, $tagsAllowed));

if ($idPost) {
    $query = <<<QUERY
UPDATE {$tableBlogg} SET
    post_idPerson = '{$post_idPerson}',
    titelPost     = '{$titelPost}',
    textPost      = '{$textPost}',
    tidPost       = '{$tidPost}',
    internPost    = '{$internPost}'
    WHERE idPost  = '{$idPost}';
QUERY;
} else {
    $query = <<<QUERY
INSERT INTO {$tableBlogg} (post_idPerson, titelPost, textPost, tidPost, internPost)
    VALUES ('{$post_idPerson}', '{$titelPost}', '{$textPost}', '{$tidPost}', '{$internPost}');
QUERY;
}

$dbAccess->SingleQuery($query);


///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect to another page
//

// Om i debugmode så visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    exit();
}

// $redirect sätts i PNewTopic.php.
$redirect = "news";
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;


?>


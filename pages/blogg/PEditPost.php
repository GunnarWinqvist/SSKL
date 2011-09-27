<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditPost.php
// Anropas med 'edit_post' från index.php.
// Skapar ett nytt inlägg till ett ämne eller editerar ett gammalt. 
// Från sidan skickas man till PSavePost.
// Input: 'idPost'
// Output: 'idPost', 'titelPost', 'textPost', 'internPost' som POST.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('fnk');         // Måste vara minst funktionär för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om input.

$idPost = isset($_GET['idPost']) ? $_GET['idPost'] : NULL;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen.
//
$dbAccess       = new CdbAccess();
$tableBlogg	    = DB_PREFIX . 'Blogg';
$idPost 		= $dbAccess->WashParameter($idPost);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Om idPost finns så hämta inläggets gamla information.
//
if ($idPost) {
    $query = <<<QUERY
SELECT * FROM {$tableBlogg} 
    WHERE idPost = {$idPost}
QUERY;

    $result=$dbAccess->SingleQuery($query);
    $row = $result->fetch_row(); // Hämta den enda resultatraden från singelqueryn.
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
    list($idPost, $post_idPerson, $titelPost, $textPost, $tidPost, $internPost) = $row;

    $result->close();
} else {
    $post_idPerson=""; $titelPost=""; $textPost=""; $tidPost=""; $internPost=0;
}    


///////////////////////////////////////////////////////////////////////////////////////////////////
// Ladda javascript för NicEdit.
//
$mainTextHTML = <<<HTMLCode
<script src="./src/nicEdit.js" type="text/javascript"></script>
<script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>

HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för nytt inlägg.
//
//$redirectTo   = "topic&amp;idTopic=".$idTopic; //Hit kommer man efter att databasen är uppdaterad.

if ($internPost) $internPost = "checked = 'checked'";

$mainTextHTML .= <<<HTMLCode
<h2>Skriv in eller uppdatera ett inlägg.</h2>
<form name='blogg' action='?p=save_post' method='post'>
<p>Titel</p>
<input type='text' name='titelPost' size='60' maxlength='100' value='{$titelPost}' />
<p>Text</p>
<textarea name='textPost' rows='20' cols='50' maxlengt='65535'>{$textPost}</textarea><br />
<input type='checkbox' name='internPost' value='1' {$internPost} />Ska endast kunna läsas av inloggade.<br /><br />
<input type='image' title='Spara' src='../images/b_enter.gif' alt='Spara' />
<a title='Cancel' href='?p=news' ><img src='../images/b_cancel.gif' alt='Cancel' /></a>
<input type='hidden' name='idPost' value='{$idPost}' />
</form>

HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Skriv ut sidan.
//
$page = new CHTMLPage(); 
$pageTitle = "Blogginlägg";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PTemplate.php
// Anropas med 'template' fr�n index.php.
// Sidan inneh�ller en massa bra ha saker f�r att kunna skapa en databasbaserad sida.
// Input: 'id' 
// Output:  'title', 'text', 'tags', 'idPost' och 'redirect'. 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
//$intFilter->UserIsSignedInOrRedirectToSignIn();   // M�ste vara inloggad f�r att n� sidan.
//$intFilter->UserIsAuthorisedOrDie('fnk');         // M�ste vara minst funktion�r f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan.

/*
$id = isset($_GET['id']) ? $_GET['id'] : NULL;
$titlePost = isset($_POST['title']) ? $_POST['title'] : NULL;
if ($debugEnable) $debug .= "Input: id=" . $id . "<br /> \n";
*/

///////////////////////////////////////////////////////////////////////////////////////////////////
// Tv�tta parametrar.
/*
$tagsAllowed = '<h1><h2><h3><h4><h5><h6><p><a><br><i><em><li><ol><ul>';
$title         = strip_tags($title, $tagsAllowed);
$idPerson 	   = $dbAccess->WashParameter($idPerson);
*/

/*
///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered och genomf�r en SQL query.

$dbAccess       = new CdbAccess();
$database       = DB_DATABASE;
$tableUser      = DB_PREFIX . 'User';
$tablePost	    = DB_PREFIX . 'Post';
$tableTag	    = DB_PREFIX . 'Tag';
$tableBelong    = DB_PREFIX . 'Belong';


// Skriv in din query h�r.
$totalStatements = 21; // �ndra manuellt s� att det st�mmer med antalet statements i queryn nedan.
$query = <<<QUERY
SELECT titlePost, textPost, post_idUser
    FROM {$tablePost} JOIN {$tableUser} ON post_idUser = idUser
    WHERE idPost = {$idPost}
QUERY;

// Singelquery returnerar ett resultset.
$result=$dbAccess->SingleQuery($query);

// H�mta resultatet fr�n singelqueryn.
while($row = $result->fetch_row()) {
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
    //G�r vad du vill med resultatet h�r. T ex
    list($apor, $gnuer, $girafer) = $row;
    echo $apor . $gnuer . $girafer;
}
$result->close();




// Multiquery returnerar antal lyckade statements.
$statements = $dbAccess->MultiQueryNoResultSet($query);
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} lyckades.<br /> \n"; 




// Kort multiquery.
$result = $mysqli->multi_query($query) 
    or die("Could not query database, query =<br/><pre>{$query}</pre><br/>{$mysqli->error}" . $debug);

    
// Alternativ mall f�r multiquery. L�ser problem med att k�ra multiquery i gamla versioner.
// L�ser �ven problemet med FOREIGN KEY, felrapport http://bugs.mysql.com/bug.php?id=40877
$statements = 0;
foreach (explode(';', $query) as $singleQuery) {
  if (strlen(trim($singleQuery)) > 0) {
       $statements++;
       $mysqli->query($singleQuery) or die("Could not query database." . $debug);
   }
}

// H�mta resultat fr�n de tv� alternativen f�r multiquery ovan.
$statements = 0;
do {
    $result = $mysqli->store_result();
    $statements++;
} while($mysqli->next_result());
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} lyckades.<br /> \n"; 


*/


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Template";

$mainTextHTML = <<<HTMLCode
<p>Detta �r en mallsida f�r att skapa databasbaserade hemsidor</p>
HTMLCode;

$rightColumnHTML = <<<HTMLCode
<p>H�ger Column</p>
HTMLCode;

$leftColumnHTML = <<<HTMLCode
<p>V�nster Column</p>
HTMLCode;

$page->printPage($pageTitle, $mainTextHTML, $leftColumnHTML, $rightColumnHTML);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Redirect till annan sida.

/*
// Om i debugmode s� visa och avbryt innan redirect.
if ($debugEnable) {
    echo $debug;
    exit();
}

// $redirect s�tts i PNewPost.php.
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'main';
header('Location: ' . WS_SITELINK . "?p={$redirect}");
exit;
*/

?>


<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNews.php
// Anropas med 'news' från index.php.
// Sidan visar ett ämne med alla tillhörande poster..
// Input:  
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen.

$dbAccess       = new CdbAccess();
$tableBlogg	    = DB_PREFIX . 'Blogg';
$tablePerson    = DB_PREFIX . 'Person';



///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta alla blogginlägg och lägg i huvudkolumnen.

$onlyPublic = "WHERE internPost = 'FALSE'"; //Om inte inloggad så visa bara ickeinterna inlägg.
if (isset($_SESSION['idUser'])) $onlyPublic = ""; //Om inloggad så visa alla.

$orderBy = "ORDER BY tidPost DESC";
$query = <<<QUERY
SELECT idPost, idPerson, fornamnPerson, efternamnPerson, titelPost, textPost, tidPost
    FROM ({$tableBlogg} JOIN {$tablePerson} ON post_idPerson = idPerson)
    {$onlyPublic}
    {$orderBy}
QUERY;
$result=$dbAccess->SingleQuery($query);

$mainTextHTML = "";
while($row = $result->fetch_row()) {
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
    list($idPost, $idPerson, $fornamnPerson, $efternamnPerson, $titelPost, $textPost, $tidPost) = $row;
    $fTidPost = date("l jS F Y G:i", $tidPost);
    $mainTextHTML .= <<<HTMLCode
<div class='post'>
<a id='news{$idPost}'><h2>{$titelPost}</h2></a>
<p>{$textPost}</p>
<div class='time'>Inlagd {$fTidPost}</div>
<div class='author'>Av {$fornamnPerson} {$efternamnPerson}</div>
HTMLCode;


// Lägg till knappar om det är ägaren som är inlogad.
$idSession        = isset($_SESSION['idUser'])        ? $_SESSION['idUser']        : NULL;
$authoritySession = isset($_SESSION['authorityUser']) ? $_SESSION['authorityUser'] : NULL;

    if (($idSession == $idPerson) || ($authoritySession == "adm")) {
    $mainTextHTML .= <<<HTMLCode
<br />
<a title='Editera' href='?p=edit_post&amp;idPost={$idPost}'><img src='../images/b_edit.gif' alt='Editera' /></a>
<a title='Radera' href='?p=del_post&amp;idPost={$idPost}' onclick="return confirm('Vill du radera artikeln?');">
    <img src='../images/b_delete.gif' alt='Radera' /></a>

HTMLCode;
    }
    $mainTextHTML .= "</div>";
}

$result->close();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Ämnesområde";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


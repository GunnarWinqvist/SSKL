<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNews.php
// Anropas med 'news' fr�n index.php.
// Sidan visar ett �mne med alla tillh�rande poster..
// Input:  
// Output:  
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen.

$dbAccess       = new CdbAccess();
$tableBlogg	    = DB_PREFIX . 'Blogg';
$tablePerson    = DB_PREFIX . 'Person';



///////////////////////////////////////////////////////////////////////////////////////////////////
// H�mta alla blogginl�gg och l�gg i huvudkolumnen.

$onlyPublic = "WHERE internPost = 'FALSE'"; //Om inte inloggad s� visa bara ickeinterna inl�gg.
if (isset($_SESSION['idUser'])) $onlyPublic = ""; //Om inloggad s� visa alla.

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


// L�gg till knappar om det �r �garen som �r inlogad.
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
$pageTitle = "�mnesomr�de";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


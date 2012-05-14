<?php

/**
 * Aktuellt (topics)
 *
 * Sidan listar alla artiklar ur bloggen i databasen.
 * H�gerkolumnen toppas av en kalender som akn editeras.
 *
 */
 

/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


/*
 * F�rbered databasen.
 */
$dbAccess       = new CdbAccess();
$tableBlogg	    = DB_PREFIX . 'Blogg';
$tablePerson    = DB_PREFIX . 'Person';


/*
 * H�mta alla blogginl�gg och l�gg i huvudkolumnen.
 */
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
    date_default_timezone_set(WS_TIMEZONE);
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


/*
 * Skapa h�gerkolumnen.
 */
// H�mta f�rst standardkolumnen och stoppa i $rightColumnHTML.
require(TP_PAGES.'rightColumn.php');

// �ppna filen kalender.txt och l�s in den i $calendar.
$calendarPath = TP_DOCUMENTS . "Kalender.txt";
$calendar = file_get_contents($calendarPath);
if ($debugEnable) $debug .= "calendarPath = ".$calendarPath.
    " calendar=".$calendar."<br />\r\n";

// L�gg till kalendern �verst i h�gerkolumnen.
$calendar = "<div class='calendar'><h3>Kalender</h3>" . $calendar;

// Och en editknapp om man �r minst funk.
if ($_SESSION['authorityUser'] == "fnk" OR $_SESSION['authorityUser'] == "adm")
    $calendar = $calendar . <<<HTMLCode
<div class='clear_button'>
<a class='button' href='?p=edit_cal' onclick="this.blur();">
    <span>Editera kalender</span></a></div>
</div>
HTMLCode;

else 
    $calendar = $calendar . "</div>";

// L�gg kalendern f�rst i h�gerkolumnen.
$rightColumnHTML = $calendar . $rightColumnHTML;


/*
 * Spapa sidan och visa den.
 */
$page = new CHTMLPage(); 
$pageTitle = "Aktuellt";
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


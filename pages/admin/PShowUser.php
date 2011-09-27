<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PShowUser.php
// Anropas med 'show_user' från index.php.
// Sidan visar all information från registret om en person utom lösenordet.
// Input: id
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan.

$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
if ($debugEnable) $debug .= "Input: id=" . $idPerson . "<br /> \n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har behörighet till sidan, d v s är personen på sidan, målsman till
// personen på sidan eller adm.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;
// Målsman kontrolleras på elevdelen längre ner.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Visa all information om personen 'idPerson'.
//
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBostad        = DB_PREFIX . 'Bostad';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableElev          = DB_PREFIX . 'Elev';
$tableMalsman       = DB_PREFIX . 'Malsman';
$tableRelation      = DB_PREFIX . 'Relation';
$idPerson 		    = $dbAccess->WashParameter($idPerson);
    
// Query för all information i alla aktuella tabeller.
$totalStatements = 4;
$query = <<<QUERY
SELECT * FROM ({$tablePerson} LEFT OUTER JOIN {$tableBostad} ON person_idBostad = idBostad)
   WHERE idPerson = {$idPerson};
SELECT * FROM {$tableFunktionar} WHERE funktionar_idPerson = {$idPerson};
SELECT * FROM {$tableElev}       WHERE elev_idPerson       = {$idPerson};
SELECT * FROM {$tableMalsman}    WHERE malsman_idPerson    = {$idPerson};
QUERY;

// Multiquery som returnerar en array med resultatset.
$statements = $dbAccess->MultiQuery($query, $arrayResult); 
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} kördes.<br /> \n"; 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Visa all information om användaren.

$arrayPerson     = $arrayResult[0]->fetch_row(); $arrayResult[0]->close();
if ($debugEnable) $debug .= "Person = ".print_r($arrayPerson, TRUE)."<br /> \n";
$mainTextHTML = <<<HTMLCode
<div class='name'>{$arrayPerson[4]} {$arrayPerson[5]}</div>
<div class='admin'>
<h3>Användarinformation</h3>
<table class='formated'>
<tr><td>Användarnamn</td><td>{$arrayPerson[1]}</td></tr>
<tr><td>Behörighetsgrupp</td><td>{$arrayPerson[3]}</td></tr>
<tr><td>Förnamn</td><td>{$arrayPerson[4]}</td></tr>
<tr><td>Efternamn</td><td>{$arrayPerson[5]}</td></tr>
<tr><td>e-postadress</td><td>{$arrayPerson[6]}</td></tr>
<tr><td>Mobilnummer</td><td>{$arrayPerson[7]}</td></tr>
</table>
HTMLCode;

///////////////////////////////////////////////////////////////////////////////////////////////////
// Om personen är knuten till en bostad.
if ($arrayPerson[8]) { 
    $mainTextHTML .= <<<HTMLCode
<h3>Bostad</h3>
<table class='formated'>
<tr><td>Telefonnummer</td><td>{$arrayPerson[10]}</td></tr>
<tr><td>Adress</td><td>{$arrayPerson[11]}</td></tr>
<tr><td>Stadsdel</td><td>{$arrayPerson[12]}</td></tr>
<tr><td>Postnummer</td><td>{$arrayPerson[13]}</td></tr>
<tr><td>Stat</td><td>{$arrayPerson[14]}</td></tr>
</table>

HTMLCode;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Om personen är elev lägger vi till elevinformation.
$arrayElev = $arrayResult[2]->fetch_row(); $arrayResult[2]->close();
if ($debugEnable) $debug .= "Elev = ".print_r($arrayElev, TRUE)."<br /> \n";
if ($arrayElev[0]) { 
    $mainTextHTML .= <<<HTMLCode
<h3>Elev</h3>
<table class='formated'>
<tr><td>Personnummer</td><td>{$arrayElev[1]}</td></tr>
<tr><td>Grupp</td><td>{$arrayElev[2]}</td></tr>
<tr><td>Nationalitet</td><td>{$arrayElev[3]}</td></tr>
<tr><td>Årskurs</td><td>{$arrayElev[4]}</td></tr>
<tr><td>Senast betalt</td><td>{$arrayElev[5]}</td></tr>
HTMLCode;

    //Kolla vem/vilka som är målsman för eleven.
    $query = <<<QUERY
SELECT idPerson, fornamnPerson, efternamnPerson
FROM {$tablePerson}
	INNER JOIN {$tableRelation}
		ON {$tablePerson}.idPerson = {$tableRelation}.relation_idMalsman
WHERE relation_idElev = {$idPerson};
QUERY;
    if ($result = $dbAccess->SingleQuery($query)) {
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            list($idMalsman, $fornamnMalsman, $efternamnMalsman) = $row;
            if ($idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Behörighet till sidan som målsman.
            $mainTextHTML .= <<<HTMLCode
<tr><td>Målsman</td><td><a href='?p=show_user&amp;id={$idMalsman}'>{$fornamnMalsman} {$efternamnMalsman}</a></td></tr>
HTMLCode;
        }
        $result->close();
    }
    $mainTextHTML .= "</table>";
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Om personen är funktionär lägger vi till detta.

if ($row = $arrayResult[1]->fetch_object()) { 
    $mainTextHTML .= <<<HTMLCode
<h3>Funktionär</h3>
<table class='formated'>
HTMLCode;
    do {
        $mainTextHTML .= <<<HTMLCode
<tr><td>Funktion</td><td>{$row->funktionFunktionar}</td></tr>
HTMLCode;
    } while($row = $arrayResult[1]->fetch_object());
    $mainTextHTML .= "</table>";
}
$arrayResult[1]->close();

///////////////////////////////////////////////////////////////////////////////////////////////////
// Om personen är målsman lägger vi till detta.
$arrayMalsman    = $arrayResult[3]->fetch_row(); $arrayResult[3]->close();

if ($arrayMalsman[0]) { 
    $mainTextHTML .= <<<HTMLCode
<h3>Målsman</h3>
<table class='formated'>
<tr><td>Nationalitet</td><td>{$arrayMalsman[1]}</td></tr>
HTMLCode;

    //Kolla vilken/vilka elever personen är målsman för.
    $query = <<<QUERY
SELECT idPerson, fornamnPerson, efternamnPerson
FROM {$tablePerson}
	INNER JOIN {$tableRelation}
		ON {$tablePerson}.idPerson = {$tableRelation}.relation_idElev
WHERE relation_idMalsman = {$idPerson};
QUERY;
    if ($result = $dbAccess->SingleQuery($query)) {
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            list($idElev, $fornamnElev, $efternamnElev) = $row;
            $mainTextHTML .= <<<HTMLCode
<tr><td>Målsman för</td><td><a href='?p=show_user&amp;id={$idElev}'>{$fornamnElev} {$efternamnElev}</a></td></tr>
HTMLCode;
        }
        $result->close();
    }
    $mainTextHTML .= <<<HTMLCode
</table>
<br />
HTMLCode;
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Lägg till knappar för editering och ändra lösenord. Olika för admin.
if ($_SESSION['authorityUser'] == "adm") {
    $mainTextHTML .= <<<HTMLCode
<a title='Editera' href='?p=edit_user&amp;id={$idPerson}' tabindex='1'><img src='../images/b_edit.gif' alt='Editera' /></a>
<a title='Ändra lösenord' href='?p=edit_account&amp;id={$idPerson}'><img src='../images/b_password.gif' alt='Ändra lösenord' /></a>
<a title='Radera' href='?p=del_account&amp;id={$idPerson}' onclick="return confirm('Vill du radera användaren ur databasen?');">
            <img src='../images/b_delete.gif' alt='Radera' /></a>
HTMLCode;

} else {
    $mainTextHTML .= <<<HTMLCode
<a title='Editera' href='?p=edit_user&amp;id={$idPerson}' tabindex='1'><img src='../images/b_edit.gif' alt='Editera' /></a>
<a title='Ändra lösenord' href='?p=edit_passw&amp;id={$idPerson}'><img src='../images/b_password.gif' alt='Ändra lösenord' /></a>
HTMLCode;
}

$mainTextHTML .= "</div> <!-- End Admin --> \n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Om sidan inte får visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Får att få se personuppgifter måste det vara för dig själv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Visa sidan

$page = new CHTMLPage(); 
$pageTitle = "Visa person";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


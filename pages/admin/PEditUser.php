<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditUser.php
// Anropas med 'edit_user' från index.php.
// Sidan presenterar ett formulär för alla uppgifter om en användare utom kontouppgifter. 
// Från sidan skickas man till PSaveUser och därefter till PShowUser.
// Input: 'id'
// Output: 'fornamn', 'efternamn', 'epost', 'mobil', 'idBostad', 'kopplabostad', 'editbostad', 'telefon', 
// 'adress', 'stadsdel', 'postnummer', 'stat', 'funk', 'funktion', 'malsman', 'natmalsman', 'pnmalsman', 
// 'elev', 'personnummer', 'grupp', 'nat', 'relation', 'id', som POST.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen.
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBostad        = DB_PREFIX . 'Bostad';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableElev          = DB_PREFIX . 'Elev';
$tableMalsman       = DB_PREFIX . 'Malsman';
$tableRelation      = DB_PREFIX . 'Relation';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan.

$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
$idPerson = $dbAccess->WashParameter($idPerson);
if ($debugEnable) $debug .= "Input: id=" . $idPerson . "<br /> \n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har behörighet till sidan, d v s är personen på sidan, målsman till
// personen på sidan eller adm.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;
// Målsman kontrolleras på elevdelen längre ner.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta den nuvarande informationen om personen ur databasen.

$totalStatements = 5;
$query = <<<QUERY
SELECT * FROM ({$tablePerson} LEFT OUTER JOIN {$tableBostad} ON person_idBostad = idBostad)
   WHERE idPerson = {$idPerson};
SELECT * FROM {$tableFunktionar} WHERE funktionar_idPerson = {$idPerson};
SELECT * FROM {$tableElev}       WHERE elev_idPerson       = {$idPerson};
SELECT * FROM {$tableMalsman}    WHERE malsman_idPerson    = {$idPerson};
SELECT idPerson, fornamnPerson, efternamnPerson
FROM {$tablePerson}	INNER JOIN {$tableRelation}
		ON {$tablePerson}.idPerson = {$tableRelation}.relation_idMalsman
WHERE relation_idElev = {$idPerson};
QUERY;

// Multiquery som returnerar en array med resultatset.
$statements = $dbAccess->MultiQuery($query, $arrayResult); 
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} kördes.<br /> \n"; 


// Som vi stoppar in i en array för respektive tabell och stänger.
$arrayPerson     = $arrayResult[0]->fetch_row(); $arrayResult[0]->close();
$arrayElev       = $arrayResult[2]->fetch_row(); $arrayResult[2]->close();
$arrayMalsman    = $arrayResult[3]->fetch_row(); $arrayResult[3]->close();
$arrayRelation   = $arrayResult[4]->fetch_row(); $arrayResult[4]->close();

if ($debugEnable) $debug .= "Person = ".print_r($arrayPerson, TRUE)."<br /> \n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för användare.

$mainTextHTML = <<<HTMLCode
<div class='name'>{$arrayPerson[4]} {$arrayPerson[5]}</div>
<form name='user' class=admin action='?p=save_user' method='post'>

<h3>Användarinformation för användaren - {$arrayPerson[1]}</h3>
<h3>Ska fyllas i för samtliga i registret</h3>
<table class='formated'>
<tr><td>Förnamn</td>
<td><input type='text' name='fornamn' size='40' maxlength='50' value='{$arrayPerson[4]}' /></td></tr>
<tr><td>Efternamn</td>
<td><input type='text' name='efternamn' size='40' maxlength='50' value='{$arrayPerson[5]}' /></td></tr>
<tr><td>e-postadress</td>
<td><input type='text' name='epost' size='40' maxlength='50' value='{$arrayPerson[6]}' /></td></tr>
<tr><td>Mobilnummer</td>
<td><input type='text' name='mobil' size='40' maxlength='20' value='{$arrayPerson[7]}' /></td></tr>
<input type='hidden' name='idbostad' value='{$arrayPerson[8]}' />
</table>

HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för bostad.

$mainTextHTML .= <<<HTMLCode
<h3>Bostad</h3>
<p>Elev får automatiskt samma adress som målsman när man väljer målsman nedan. 
Se vid <b>Elev</b> nedan.</p>

<p><b>Uppdatera en gammal eller skapa en ny adress här.</b></p>
<table class='formated'>
<tr><td>Telefonnummer</td>
<td><input type='text' name='telefon' size='40' maxlength='20' value='{$arrayPerson[10]}' /></td>
<td class='td3'><input type='checkbox' name='editbostad' value='true' />Lägg till / Ändra (hela adressen)</td></tr>
<tr><td>Adress</td>
<td><input type='text' name='adress' size='40' maxlength='100' value='{$arrayPerson[11]}' /></td></tr>
<tr><td>Stadsdel</td>
<td><input type='text' name='stadsdel' size='40' maxlength='20' value='{$arrayPerson[12]}' /></td></tr>
<tr><td>Postnummer</td>
<td><input type='text' name='postnummer' size='40' maxlength='10' value='{$arrayPerson[13]}' /></td></tr>
<tr><td>Stat</td>
<td><input type='text' name='stat' size='40' maxlength='20' value='{$arrayPerson[14]}' /></td></tr>
<tr><td></td><td><i><small>(Kuala Lumpur, Selangor, ...)</small></i></td></tr>
</table>
HTMLCode;

if ($_SESSION['authorityUser'] == "adm") {
    $mainTextHTML .= <<<HTMLCode
<p><b>Välj adress som redan finns i registret här.</b></p>
<table class='formated'>
<tr><td>Adress</td><td><select name='idnewbostad'>
HTMLCode;

    //Visa alla bostäder i registret som options.
    $query = "SELECT idBostad, adressBostad FROM {$tableBostad};";
    $result = $dbAccess->SingleQuery($query);
    while($row = $result->fetch_object()) {
        $mainTextHTML .= <<<HTMLCode
    <option value='{$row->idBostad}'>{$row->adressBostad}</option>
HTMLCode;
        }
    $result->close();
    $mainTextHTML .= <<<HTMLCode
</select></td>
<td class='td3'><input type='checkbox' name='kopplabostad' value='true' />Välj</td></tr>
</table>

HTMLCode;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för funktionär.

if (strcmp("fnk", $_SESSION['authorityUser']) > 0 ) { // Visa bara om man är funktionär. Annars kan 
                    // man sätta sig själv till funk och få tillgång till mer än man ska.
    $mainTextHTML .= <<<HTMLCode
<h3>Ska fyllas i för funktionärer</h3>
<table class='formated'>
HTMLCode;

    if (isset($arrayResult[1])) { //Resultat från queryn från början av sidan.
        while($row = $arrayResult[1]->fetch_object()) {
            $mainTextHTML .= <<<HTMLCode
<tr><td>Funktion</td><td><input type='text' name='funk{$row->idFunktion}' size='40' maxlength='20' 
    value='{$row->funktionFunktionar}' /></td>
    <td class='td3'><input type='checkbox' name='editfunk' value='{$row->idFunktion}' />Ändra</td>
    <td class='td3'><input type='checkbox' name='delfunk' value='{$row->idFunktion}' />Radera</td></tr>

HTMLCode;
        }
        $arrayResult[1]->close();
    }

    $mainTextHTML .= <<<HTMLCode
<tr><td>Lägg till funktion</td>
<td><input type='text' name='funktion' size='40' maxlength='50' /></td>
<td class='td3'><input type='checkbox' name='addfunk' value='true' />Lägg till</td></tr>
<tr><td></td><td><i><small>(sekreterare, lärare, mupp, ...)</small></i></td></tr>
</table>

HTMLCode;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för målsman.

$mainTextHTML .= <<<HTMLCode
<h3>Ska fyllas i för målsman</h3>
<table class='formated'>
<tr><td>Nationalitet</td>
<td><input type='text' name='natmalsman' size='40' maxlength='2' value='{$arrayMalsman[1]}' /></td>
<td class='td3'><input type='checkbox' name='malsman' value='true' />Lägg till / Ändra</td></tr>
<tr><td></td><td><i><small>(se för svensk)</small></i></td></tr>
</table>

HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för elev.

$mainTextHTML .= <<<HTMLCode
<h3>Ska fyllas i för elever</h3>
<table class='formated'>
<tr><td>Personnummer</td>
<td><input type='text' name='personnummer' size='40' maxlength='13' value='{$arrayElev[1]}' /></td>
<td class='td3'><input type='checkbox' name='elev' value='true' />Lägg till / Ändra</td></tr>
<tr><td></td><td><i><small>(ååååmmdd-nnnn) (Om eleven saknar svenskt</small></i></td></tr>
<tr><td></td><td><i><small>personnummer fyll i födelsedatum samt 0010 om</small></i></td></tr>
<tr><td></td><td><i><small>det är en pojke eller 0020 om det är en flicka.)</small></i></td></tr>
<tr><td>Vilken grupp är eleven i</td>
<td><input type='text' name='grupp' size='40' maxlength='10' value='{$arrayElev[2]}' /></td></tr>
<tr><td>Nationalitet</td>
<td><input type='text' name='nat' size='40' maxlength='2' value='{$arrayElev[3]}' /></td></tr>
<tr><td></td><td><i><small>(se för svensk)</small></i></td></tr>
<tr><td>Årskurs</td>
<td><input type='text' name='grade' size='40' maxlength='2' value='{$arrayElev[4]}' /></td></tr>
<tr><td></td><td><i><small>(Årskurs i sin ordinarie skola.)</small></i></td></tr>
HTMLCode;

if (strcmp("fnk", $_SESSION['authorityUser']) > 0 ) {
    $mainTextHTML .= <<<HTMLCode
<tr><td>Senast betalt</td>
<td><input type='text' name='pay' size='40' maxlength='10' value='{$arrayElev[5]}' /></td></tr>
HTMLCode;
} else {
    $mainTextHTML .= <<<HTMLCode
<input type='hidden' name='pay' value='{$arrayElev[5]}' />
HTMLCode;
}
    
//Kolla vem/vilka som är målsman för eleven.
$query = <<<QUERY
SELECT idPerson, fornamnPerson, efternamnPerson
FROM {$tablePerson}
	INNER JOIN {$tableRelation}
		ON {$tablePerson}.idPerson = {$tableRelation}.relation_idMalsman
WHERE relation_idElev = {$idPerson};
QUERY;
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->idPerson == $_SESSION['idUser']) $showPage = TRUE; //Behörighet till sidan som målsman.
        $mainTextHTML .= <<<HTMLCode
<tr><td>Målsman</td><td>{$row->fornamnPerson} {$row->efternamnPerson}</td>
    <td class='td3'><input type='checkbox' name='delmalsman' value='{$row->idPerson}' />Radera</td>
</tr>
HTMLCode;
    }
    $result->close();
}
//Lista alla registrerade målsmän som options.
$mainTextHTML .= <<<HTMLCode
<tr><td>Lägg till målsman</td>
<td><select name='newmalsman'><option value=''></option>
HTMLCode;

$query = <<<QUERY
SELECT idPerson, fornamnPerson, efternamnPerson FROM {$tablePerson}	INNER JOIN {$tableMalsman}
		ON {$tablePerson}.idPerson = {$tableMalsman}.malsman_idPerson;
QUERY;
$result = $dbAccess->SingleQuery($query);
while($row = $result->fetch_row()) {
    if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
    list($idMalsman, $fornamnMalsman, $efternamnMalsman) = $row;
    $mainTextHTML .= <<<HTMLCode
    <option value='{$idMalsman}'>{$fornamnMalsman} {$efternamnMalsman}</option>
HTMLCode;
}
$result->close();
$mainTextHTML .= <<<HTMLCode
</select></td>
<td class='td3'><input type='checkbox' name='addmalsman' value='true' />Välj</td></tr>
</tr>
<tr><td></td><td><i><small>(Måste finnas i registret för att synas i listan.)</small></i></td></tr>
</table>
<input type='image' title='Spara' src='../images/b_enter.gif' alt='Spara' />
<a title='Cancel' href='?p=show_user&amp;id={$idPerson}' ><img src='../images/b_cancel.gif' alt='Cancel' /></a>
<input type='hidden' name='id' value='{$idPerson}' />
</form>
HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om sidan inte får visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Får att få se personuppgifter måste det vara för dig själv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Editera användare";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


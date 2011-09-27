<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditPassword.php
// Anropas med 'edit_passw' från index.php.
// Sidan presenterar ett formulär för att ändra password. Från sidan skickas man till PSaveAccount.
// Input: 'id'
// Output: 'account', 'password1', 'password2', 'behorighet', 'redirect' som POST's.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns.
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;

// Nollställ alla parametrar om vi ska skapa en ny person.
$arrayPerson     = array("","","","","","");


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har behörighet till sidan, d v s är personen på sidan, målsman till
// personen på sidan eller adm.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;
// Målsman kontrolleras längre ner.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta den nuvarande informationen ur databasen.
$dbAccess           = new CdbAccess();
$idPerson 		    = $dbAccess->WashParameter($idPerson);
$tablePerson        = DB_PREFIX . 'Person';
$tableRelationon    = DB_PREFIX . 'Relation';

$query = "SELECT * FROM {$tablePerson} WHERE idPerson = {$idPerson};";
$result = $dbAccess->SingleQuery($query); 
$arrayPerson = $result->fetch_row();
$result->close();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om SESSION idUser är målsman till idPerson.

$query = "SELECT * FROM {$tableRelationon} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Behörighet till sidan som målsman.
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formulär för användare.

$redirect = "?p=edit_passw&id=".$idPerson;
$mainTextHTML = <<<HTMLCode
<form name='password' class=admin action='?p=save_account' method='post'>
<h3>Fyll i ett nytt lösenord.</h3>
<table>
<tr><td>Lösenord</td>
<td><input type='password' name='password1' size='20' maxlength='20' /></td></tr>
<tr><td>Lösenord igen</td>
<td><input type='password' name='password2' size='20' maxlength='20' /></td></tr>
<tr><td></td><td>
<input type='image' title='Spara' src='../images/b_enter.gif' alt='Spara' />
<a title='Cancel' href='?p=show_user&amp;id={$idPerson}' ><img src='../images/b_cancel.gif' alt='Cancel' /></a>
</td></tr></table>
<input type='hidden' name='id' value='{$idPerson}' />
<input type='hidden' name='account' value='{$arrayPerson[1]}' />
<input type='hidden' name='behorighet' value='{$arrayPerson[3]}' />
<input type='hidden' name='redirect' value='{$redirect}' />
</form>
HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om sidan inte får visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Du kan bara ändra lösenord på dig själv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Editera användarkonto";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditPassword.php
// Anropas med 'edit_passw' fr�n index.php.
// Sidan presenterar ett formul�r f�r att �ndra password. Fr�n sidan skickas man till PSaveAccount.
// Input: 'id'
// Output: 'account', 'password1', 'password2', 'behorighet', 'redirect' som POST's.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns.
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;

// Nollst�ll alla parametrar om vi ska skapa en ny person.
$arrayPerson     = array("","","","","","");


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har beh�righet till sidan, d v s �r personen p� sidan, m�lsman till
// personen p� sidan eller adm.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;
// M�lsman kontrolleras l�ngre ner.


///////////////////////////////////////////////////////////////////////////////////////////////////
// H�mta den nuvarande informationen ur databasen.
$dbAccess           = new CdbAccess();
$idPerson 		    = $dbAccess->WashParameter($idPerson);
$tablePerson        = DB_PREFIX . 'Person';
$tableRelationon    = DB_PREFIX . 'Relation';

$query = "SELECT * FROM {$tablePerson} WHERE idPerson = {$idPerson};";
$result = $dbAccess->SingleQuery($query); 
$arrayPerson = $result->fetch_row();
$result->close();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om SESSION idUser �r m�lsman till idPerson.

$query = "SELECT * FROM {$tableRelationon} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Beh�righet till sidan som m�lsman.
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formul�r f�r anv�ndare.

$redirect = "?p=edit_passw&id=".$idPerson;
$mainTextHTML = <<<HTMLCode
<form name='password' class=admin action='?p=save_account' method='post'>
<h3>Fyll i ett nytt l�senord.</h3>
<table>
<tr><td>L�senord</td>
<td><input type='password' name='password1' size='20' maxlength='20' /></td></tr>
<tr><td>L�senord igen</td>
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
// Om sidan inte f�r visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Du kan bara �ndra l�senord p� dig sj�lv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Editera anv�ndarkonto";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


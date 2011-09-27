<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNewUser.php
// Anropas med 'new_user' fr�n index.php.
// Sidan presenterar ett formul�r f�r en ny anv�ndare. Fr�n sidan skickas man till PShowPerson.
// Input: -
// Output: 'account', 'password1', 'password2', 'behorighet', 'send', 'redirect' som POST's.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // M�ste vara minst adm f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns.
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;

// Nollst�ll alla parametrar om vi ska skapa en ny person.
$arrayPerson     = array("","","","","","");


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om $idPerson har ett v�rde s� ska en anv�ndare editeras. H�mta d� den nuvarande informationen ur 
// databasen.
if ($idPerson) {
    $dbAccess           = new CdbAccess();
    $idPerson 		    = $dbAccess->WashParameter($idPerson);
    $tablePerson        = DB_PREFIX . 'Person';
    $query = "SELECT * FROM {$tablePerson} WHERE idPerson = {$idPerson};";
    $result = $dbAccess->SingleQuery($query); 
    $arrayPerson = $result->fetch_row();
    $result->close();
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skapa ett slumpl�senord.

$min=5; // minimum length of password
$max=10; // maximum length of password
$pwd=""; // to store generated password

for ( $i=0; $i<rand($min,$max); $i++ ) {
    $num=rand(48,122);
    if(($num > 97 && $num < 122))     $pwd.=chr($num);
    else if(($num > 65 && $num < 90)) $pwd.=chr($num);
    else if(($num >48 && $num < 57))  $pwd.=chr($num);
    else if($num==95)                 $pwd.=chr($num);
    else $i--;
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Formul�r f�r anv�ndare.

$redirect = "?p=edit_account&id=".$idPerson;
$mainTextHTML = <<<HTMLCode
<form class=admin action='?p=save_account' method='post'>
<h3>Fyll i anv�ndarinformation.</h3>
<p>Skapa ett nytt anv�ndarkonto eller �ndra uppgifter f�r ett redan existerande anv�ndarkonto.</p>
<table>
<tr><td>Anv�ndarnamn</td>
<td><input type='text' name='account' size='20' maxlength='20' value='{$arrayPerson[1]}' /></td></tr>
<tr><td>L�senord</td>
<td><input type='password' name='password1' size='20' maxlength='20' value='{$pwd}' /></td></tr>
<tr><td>L�senord igen</td>
<td><input type='password' name='password2' size='20' maxlength='20' value='{$pwd}' /></td></tr>
<tr><td>Beh�righetsgrupp</td>
HTMLCode;

if ($arrayPerson[3] == "adm") {
    $mainTextHTML .= <<<HTMLCode
<td><input type='radio' name='behorighet' value='usr'  /> Vanlig anv�ndare 
<input type='radio' name='behorighet' value='adm' checked='checked' /> Administrat�r </td></tr>
HTMLCode;
} else {
    $mainTextHTML .= <<<HTMLCode
<td><input type='radio' name='behorighet' value='usr' checked='checked' /> Vanlig anv�ndare 
<input type='radio' name='behorighet' value='adm' /> Administrat�r </td></tr>
HTMLCode;
}
$mainTextHTML .= <<<HTMLCode
<tr><td>Skicka l�senordet med mejl till anv�ndaren</td>
<td><input type='checkbox' name='send' value='1' /></td></tr>
<tr><td>
<input type='image' title='Spara' src='../images/b_enter.gif' alt='Spara' />
<a title='Cancel' href='?p=search_user' ><img src='../images/b_cancel.gif' alt='Cancel' /></a>
</td></tr>
</table>
<input type='hidden' name='id' value='{$idPerson}' />
<input type='hidden' name='redirect' value='{$redirect}' />
</form>
HTMLCode;


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Editera anv�ndarkonto";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


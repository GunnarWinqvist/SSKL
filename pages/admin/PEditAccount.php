<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNewUser.php
// Anropas med 'new_user' från index.php.
// Sidan presenterar ett formulär för en ny användare. Från sidan skickas man till PShowPerson.
// Input: -
// Output: 'account', 'password1', 'password2', 'behorighet', 'send', 'redirect' som POST's.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // Måste vara minst adm för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns.
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;

// Nollställ alla parametrar om vi ska skapa en ny person.
$arrayPerson     = array("","","","","","");


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om $idPerson har ett värde så ska en användare editeras. Hämta då den nuvarande informationen ur 
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
// Skapa ett slumplösenord.

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
// Formulär för användare.

$redirect = "?p=edit_account&id=".$idPerson;
$mainTextHTML = <<<HTMLCode
<form class=admin action='?p=save_account' method='post'>
<h3>Fyll i användarinformation.</h3>
<p>Skapa ett nytt användarkonto eller ändra uppgifter för ett redan existerande användarkonto.</p>
<table>
<tr><td>Användarnamn</td>
<td><input type='text' name='account' size='20' maxlength='20' value='{$arrayPerson[1]}' /></td></tr>
<tr><td>Lösenord</td>
<td><input type='password' name='password1' size='20' maxlength='20' value='{$pwd}' /></td></tr>
<tr><td>Lösenord igen</td>
<td><input type='password' name='password2' size='20' maxlength='20' value='{$pwd}' /></td></tr>
<tr><td>Behörighetsgrupp</td>
HTMLCode;

if ($arrayPerson[3] == "adm") {
    $mainTextHTML .= <<<HTMLCode
<td><input type='radio' name='behorighet' value='usr'  /> Vanlig användare 
<input type='radio' name='behorighet' value='adm' checked='checked' /> Administratör </td></tr>
HTMLCode;
} else {
    $mainTextHTML .= <<<HTMLCode
<td><input type='radio' name='behorighet' value='usr' checked='checked' /> Vanlig användare 
<input type='radio' name='behorighet' value='adm' /> Administratör </td></tr>
HTMLCode;
}
$mainTextHTML .= <<<HTMLCode
<tr><td>Skicka lösenordet med mejl till användaren</td>
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
$pageTitle = "Editera användarkonto";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


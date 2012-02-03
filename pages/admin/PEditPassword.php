<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditPassword.php
// Anropas med 'edit_passw' från index.php.
// Sidan presenterar ett formulär för att ändra password. 
// Input: 'id'
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns.
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har behörighet till sidan, d v s är personen på sidan, målsman till
// personen på sidan eller adm.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;
// Målsman kontrolleras längre ner.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen.
$dbAccess           = new CdbAccess();
$idPerson 		    = $dbAccess->WashParameter($idPerson);
$tablePerson        = DB_PREFIX . 'Person';
$tableRelationon    = DB_PREFIX . 'Relation';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om SESSION idUser är målsman till idPerson.

$query = "SELECT * FROM {$tableRelationon} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Behörighet till sidan som målsman.
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om sidan inte får visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Du kan bara ändra lösenord på dig själv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formuläret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

$formAction = WS_SITELINK . "?p=edit_passw&id=".$idPerson; // Pekar tillbaka på samma sida igen.
$form       = new HTML_QuickForm2('account', 'post', array('action' => $formAction), array('name' => 'account'));

$fsAccount = $form->addElement('fieldset')->setLabel('Fyll i ett nytt lösenord');

// Lösenord
$oldPasswordPerson = $fsAccount->addElement('password', 'oldPassword', array('style' => 'width: 300px;'),
                                array('label' => 'Ditt gamla lösenord:'));
$newPasswordPerson = $fsAccount->addElement('password', 'password', array('style' => 'width: 300px;'),
                                array('label' => 'Lösenord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', array('style' => 'width: 300px;'),
                                array('label' => 'Lösenord igen:'));

$oldPasswordPerson->addRule('required', 'Du måste ange ditt gamla lösenord.');
$newPasswordPerson->addRule('required', 'Du måste ange ett lösenord.');
$passwordRep      ->addRule('required', 'Du måste upprepa lösenordet.');
$newPasswordPerson->addRule('minlength', 'Lösenordet måste innehålla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 'Lösenordet får inte vara längre än 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 'Du har angett två olika lösenord.', $passwordRep);

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="Återställ" href="?p=edit_passw&amp;id='.$idPerson.'" ><img src="../images/b_undo.gif" alt="Återställ" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=show_user&amp;id='.$idPerson.'" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formuläret.

// Ta bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

//Om sidan är riktigt ifylld så uppdatera databasen.
$mainTextHTML = "";
if ($form->validate()) {

    //Tvätta inparametrarna.
    $formValues       = $form->getValue();
    $oldPassword 	  = $dbAccess->WashParameter(strip_tags($formValues['oldPassword']));
    $passwordPerson   = $dbAccess->WashParameter(strip_tags($formValues['password']));

    // Kolla om oldPassword stämmer med det i registret.
    $query = <<<Query
SELECT * FROM {$tablePerson}
WHERE
	idPerson        = '{$idPerson}' AND
	passwordPerson 	= md5('{$oldPassword}')
;
Query;
    
    if ($dbAccess->SingleQuery($query)) { //Om det gamla lösenordet stämmer så uppdateras databasen.
        $query = <<<QUERY
UPDATE {$tablePerson} SET 
    passwordPerson = md5('{$passwordPerson}')
    WHERE idPerson = '{$idPerson}';
QUERY;
        $dbAccess->SingleQuery($query);
        
        $mainTextHTML .= "<h2>Ditt lösenord är ändrat.</h2>";
        $form->removeChild($buttons); // Tag bort knapparna.
        $form->toggleFrozen(true); // Frys formuläret inför ny visning.
        
    } else { //Annars blir det felmeddelande.
        $_SESSION['errorMessage'] = "Ditt gamla lösenord är felaktigt!";
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om formuläret inte är riktigt ifyllt så skrivs det ut igen med kommentarer.

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med en (<em>*</em>).'
    ))
    ->setTemplateForId('submit', '<div class="element">{element} or <a href="/">Cancel</a></div>')
    ->setTemplateForClass(
        'HTML_QuickForm2_Element_Input',
        '<div class="element<qf:error> error</qf:error>"><qf:error>{error}</qf:error>' .
        '<label for="{id}" class="qf-label<qf:required> required</qf:required>">{label}</label>' .
        '{element}' .
        '<qf:label_2><div class="qf-label-1">{label_2}</div></qf:label_2></div>' 
    );

$form->render($renderer);
$mainTextHTML .= $renderer;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Editera användarkonto";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


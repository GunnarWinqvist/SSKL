<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditPassword.php
// Anropas med 'edit_passw' fr�n index.php.
// Sidan presenterar ett formul�r f�r att �ndra password. 
// Input: 'id'
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns.
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har beh�righet till sidan, d v s �r personen p� sidan, m�lsman till
// personen p� sidan eller adm.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;
// M�lsman kontrolleras l�ngre ner.


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen.
$dbAccess           = new CdbAccess();
$idPerson 		    = $dbAccess->WashParameter($idPerson);
$tablePerson        = DB_PREFIX . 'Person';
$tableRelationon    = DB_PREFIX . 'Relation';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om SESSION idUser �r m�lsman till idPerson.

$query = "SELECT * FROM {$tableRelationon} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Beh�righet till sidan som m�lsman.
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om sidan inte f�r visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Du kan bara �ndra l�senord p� dig sj�lv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formul�ret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

$formAction = WS_SITELINK . "?p=edit_passw&id=".$idPerson; // Pekar tillbaka p� samma sida igen.
$form       = new HTML_QuickForm2('account', 'post', array('action' => $formAction), array('name' => 'account'));

$fsAccount = $form->addElement('fieldset')->setLabel('Fyll i ett nytt l�senord');

// L�senord
$oldPasswordPerson = $fsAccount->addElement('password', 'oldPassword', array('style' => 'width: 300px;'),
                                array('label' => 'Ditt gamla l�senord:'));
$newPasswordPerson = $fsAccount->addElement('password', 'password', array('style' => 'width: 300px;'),
                                array('label' => 'L�senord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', array('style' => 'width: 300px;'),
                                array('label' => 'L�senord igen:'));

$oldPasswordPerson->addRule('required', 'Du m�ste ange ditt gamla l�senord.');
$newPasswordPerson->addRule('required', 'Du m�ste ange ett l�senord.');
$passwordRep      ->addRule('required', 'Du m�ste upprepa l�senordet.');
$newPasswordPerson->addRule('minlength', 'L�senordet m�ste inneh�lla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 'L�senordet f�r inte vara l�ngre �n 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 'Du har angett tv� olika l�senord.', $passwordRep);

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="�terst�ll" href="?p=edit_passw&amp;id='.$idPerson.'" ><img src="../images/b_undo.gif" alt="�terst�ll" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=show_user&amp;id='.$idPerson.'" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formul�ret.

// Ta bort 'space' f�rst och sist p� alla v�rden.
$form->addRecursiveFilter('trim'); 

//Om sidan �r riktigt ifylld s� uppdatera databasen.
$mainTextHTML = "";
if ($form->validate()) {

    //Tv�tta inparametrarna.
    $formValues       = $form->getValue();
    $oldPassword 	  = $dbAccess->WashParameter(strip_tags($formValues['oldPassword']));
    $passwordPerson   = $dbAccess->WashParameter(strip_tags($formValues['password']));

    // Kolla om oldPassword st�mmer med det i registret.
    $query = <<<Query
SELECT * FROM {$tablePerson}
WHERE
	idPerson        = '{$idPerson}' AND
	passwordPerson 	= md5('{$oldPassword}')
;
Query;
    
    if ($dbAccess->SingleQuery($query)) { //Om det gamla l�senordet st�mmer s� uppdateras databasen.
        $query = <<<QUERY
UPDATE {$tablePerson} SET 
    passwordPerson = md5('{$passwordPerson}')
    WHERE idPerson = '{$idPerson}';
QUERY;
        $dbAccess->SingleQuery($query);
        
        $mainTextHTML .= "<h2>Ditt l�senord �r �ndrat.</h2>";
        $form->removeChild($buttons); // Tag bort knapparna.
        $form->toggleFrozen(true); // Frys formul�ret inf�r ny visning.
        
    } else { //Annars blir det felmeddelande.
        $_SESSION['errorMessage'] = "Ditt gamla l�senord �r felaktigt!";
    }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om formul�ret inte �r riktigt ifyllt s� skrivs det ut igen med kommentarer.

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'F�ljand information saknas eller �r felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska f�lt �r markerade med en (<em>*</em>).'
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
$pageTitle = "Editera anv�ndarkonto";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


<?php

/**
 * Edit password (edit_pwd)
 *
 * Sidan presenterar ett formul�r f�r att �ndra password.
 * Input: 'id'
 */


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();


/*
 * Prepare the data base.
 */
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableRelationon    = DB_PREFIX . 'Relation';


/*
 * Handle input to the page.
 */
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
$idPerson = $dbAccess->WashParameter($idPerson);

if ($debugEnable) $debug.="Input: id=".$idPerson." Authority = ".
    $_SESSION['authorityUser']."<br />\r\n";


/*
 * Kontrollera om personen har beh�righet till sidan, d v s �r personen p� 
 * sidan, m�lsman till personen p� sidan eller adm.
 */

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;

// Kontrollera om SESSION idUser �r m�lsman till idPerson.
$query = "SELECT * FROM {$tableRelationon} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE;
    }
}


////////////////////////////////////////////////////////////////////////////////
// Om sidan inte f�r visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Du kan bara �ndra l�senord p� dig sj�lv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


////////////////////////////////////////////////////////////////////////////////
// Generera formul�ret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Peka tillbaka p� samma sida igen.
$formAction = WS_SITELINK . "?p=edit_pwd&id=".$idPerson; 
$form       = new HTML_QuickForm2('account', 'post', 
    array('action' => $formAction), 
    array('name' => 'account'));

$fsAccount = $form->addElement('fieldset')->setLabel('Fyll i ett nytt l�senord');

// L�senord
$oldPasswordPerson = $fsAccount->addElement('password', 'oldPassword', 
    array('style' => 'width: 300px;'),
    array('label' => 'Ditt gamla l�senord:'));
$newPasswordPerson = $fsAccount->addElement('password', 'password', 
    array('style' => 'width: 300px;'),
    array('label' => 'L�senord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', 
    array('style' => 'width: 300px;'),
    array('label' => 'L�senord igen:'));

$oldPasswordPerson->addRule('required', 'Du m�ste ange ditt gamla l�senord.');
$newPasswordPerson->addRule('required', 'Du m�ste ange ett l�senord.');
$passwordRep      ->addRule('required', 'Du m�ste upprepa l�senordet.');
$newPasswordPerson->addRule('minlength', 
    'L�senordet m�ste inneh�lla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 
    'L�senordet f�r inte vara l�ngre �n 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 
    'Du har angett tv� olika l�senord.', $passwordRep);

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', 
    array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="�terst�ll" href="?p=edit_pwd&amp;id='.$idPerson.'" >
    <img src="../images/b_undo.gif" alt="�terst�ll" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=show_usr&amp;id='.$idPerson.'" >
    <img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formul�ret.

// Ta bort 'space' f�rst och sist p� alla v�rden.
$form->addRecursiveFilter('trim'); 

//Om sidan �r riktigt ifylld s� uppdatera databasen.
$mainTextHTML = "";
if ($form->validate()) {

    //Tv�tta inparametrarna.
    $formValues       = $form->getValue();
    $oldPassword 	  = $dbAccess->WashParameter(
        strip_tags($formValues['oldPassword']));
    $passwordPerson   = $dbAccess->WashParameter(
        strip_tags($formValues['password']));

    // Kolla om oldPassword st�mmer med det i registret.
    $query = <<<Query
SELECT * FROM {$tablePerson}
WHERE
	idPerson        = '{$idPerson}' AND
	passwordPerson 	= md5('{$oldPassword}')
;
Query;
    
    if ($dbAccess->SingleQuery($query)) { 
        //Om det gamla l�senordet st�mmer s� uppdateras databasen.
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


///////////////////////////////////////////////////////////////////////////////
// Om formul�ret inte �r riktigt ifyllt s� skrivs det ut igen med kommentarer.

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'F�ljand information saknas eller �r felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska f�lt �r markerade med en <em>*</em>'
    ))
    ->setTemplateForId('submit', '<div class="element">{element} or 
        <a href="/">Cancel</a></div>')
;

$form->render($renderer);
$mainTextHTML .= $renderer;


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */

$page = new CHTMLPage(); 
$pageTitle = "Editera anv�ndarkonto";

require(TP_PAGES.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


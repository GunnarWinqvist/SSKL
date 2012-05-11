<?php

/**
 * Edit password (edit_pwd)
 *
 * Sidan presenterar ett formulär för att ändra password.
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
 * Kontrollera om personen har behörighet till sidan, d v s är personen på 
 * sidan, målsman till personen på sidan eller adm.
 */

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;

// Kontrollera om SESSION idUser är målsman till idPerson.
$query = "SELECT * FROM {$tableRelationon} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE;
    }
}


////////////////////////////////////////////////////////////////////////////////
// Om sidan inte får visas avbryt och visa felmeddelande.
if (!$showPage) {
    $message = "Du kan bara ändra lösenord på dig själv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


////////////////////////////////////////////////////////////////////////////////
// Generera formuläret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Peka tillbaka på samma sida igen.
$formAction = WS_SITELINK . "?p=edit_pwd&id=".$idPerson; 
$form       = new HTML_QuickForm2('account', 'post', 
    array('action' => $formAction), 
    array('name' => 'account'));

$fsAccount = $form->addElement('fieldset')->setLabel('Fyll i ett nytt lösenord');

// Lösenord
$oldPasswordPerson = $fsAccount->addElement('password', 'oldPassword', 
    array('style' => 'width: 300px;'),
    array('label' => 'Ditt gamla lösenord:'));
$newPasswordPerson = $fsAccount->addElement('password', 'password', 
    array('style' => 'width: 300px;'),
    array('label' => 'Lösenord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', 
    array('style' => 'width: 300px;'),
    array('label' => 'Lösenord igen:'));

$oldPasswordPerson->addRule('required', 'Du måste ange ditt gamla lösenord.');
$newPasswordPerson->addRule('required', 'Du måste ange ett lösenord.');
$passwordRep      ->addRule('required', 'Du måste upprepa lösenordet.');
$newPasswordPerson->addRule('minlength', 
    'Lösenordet måste innehålla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 
    'Lösenordet får inte vara längre än 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 
    'Du har angett två olika lösenord.', $passwordRep);

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', 
    array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="Återställ" href="?p=edit_pwd&amp;id='.$idPerson.'" >
    <img src="../images/b_undo.gif" alt="Återställ" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=show_usr&amp;id='.$idPerson.'" >
    <img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formuläret.

// Ta bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

//Om sidan är riktigt ifylld så uppdatera databasen.
$mainTextHTML = "";
if ($form->validate()) {

    //Tvätta inparametrarna.
    $formValues       = $form->getValue();
    $oldPassword 	  = $dbAccess->WashParameter(
        strip_tags($formValues['oldPassword']));
    $passwordPerson   = $dbAccess->WashParameter(
        strip_tags($formValues['password']));

    // Kolla om oldPassword stämmer med det i registret.
    $query = <<<Query
SELECT * FROM {$tablePerson}
WHERE
	idPerson        = '{$idPerson}' AND
	passwordPerson 	= md5('{$oldPassword}')
;
Query;
    
    if ($dbAccess->SingleQuery($query)) { 
        //Om det gamla lösenordet stämmer så uppdateras databasen.
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


///////////////////////////////////////////////////////////////////////////////
// Om formuläret inte är riktigt ifyllt så skrivs det ut igen med kommentarer.

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med en <em>*</em>'
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
$pageTitle = "Editera användarkonto";

require(TP_PAGES.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


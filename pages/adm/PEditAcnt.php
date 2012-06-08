<?php

/**
 * Edit account (edit_acnt)
 *
 * Visar ett formulär för kontoinformation med användarnamn, lösenord och 
 * behörighet. Formuläret genereras med QuickForm2, kontrollerar att allt är 
 * riktigt ifyllt och uppdaterar databasen.
 * Input: 'id' eller NULL
 */


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();
$intFilter->UserIsAuthorisedOrDie('adm'); //Must be adm to access the page.


/*
 * Handle input to the page.
 */
$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
if ($idPerson) $redirect = "show_usr&id=".$idPerson;
else           $redirect = "srch_usr";

if ($debugEnable) $debug.="Input: id=".$idPerson." Authority = ".
    $_SESSION['authorityUser']."<br />\r\n";


/*
 * Prepare the data base.
 */
$dbAccess       = new CdbAccess();
$tablePerson    = DB_PREFIX . 'Person';
$tableElev      = DB_PREFIX . 'Elev';
$viewMalsman    = DB_PREFIX . 'ListaMalsman';


/*
 * Om $idPerson har ett värde så ska en användare editeras. Hämta då den 
 * nuvarande informationen ur databasen.
 */

if ($idPerson) {
    $idPerson 		= $dbAccess->WashParameter($idPerson);
    $query          = "SELECT * FROM {$tablePerson} WHERE idPerson = {$idPerson};";
    $result         = $dbAccess->SingleQuery($query); 
    $arrayPerson    = $result->fetch_row();
    $result->close();
} else {
    // Nollställ alla parametrar om vi ska skapa en ny person.
    $arrayPerson     = array("","","","","","","","","","");
}


/*
 * Skapa ett slumplösenord.
 */

$min = 5;   // minimum length of password
$max = 10;  // maximum length of password
$pwd = "";  // to store generated password

for ( $i=0; $i<rand($min,$max); $i++ ) {
    $num=rand(48,122);
    if(($num > 97 && $num < 122))     $pwd.=chr($num);
    else if(($num > 65 && $num < 90)) $pwd.=chr($num);
    else if(($num >48 && $num < 57))  $pwd.=chr($num);
    else if($num==95)                 $pwd.=chr($num);
    else $i--;
}


/*
 * Generera formuläret med QuickForm2.
 */

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Peka tillbaka på samma sida igen.
$formAction = WS_SITELINK . "?p=edit_acnt&id=".$idPerson; 
$form       = new HTML_QuickForm2('account', 'post', 
                array('action' => $formAction), 
                array('name' => 'account'));

// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'account'     => $arrayPerson[1],
    'password'    => $pwd,
    'passwordRep' => $pwd,
    'send'        => '0'
)));

$fsAccount = $form->addElement('fieldset')->setLabel('Användarkonto');

// Användarnamn
$accountPerson = $fsAccount->addElement( 'text', 'account', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Användarnamn:') );
$accountPerson->addRule('required', 'Du måste ange ett användarnamn');
$accountPerson->addRule('maxlength', 
    'Användarnamnet får inte vara längre än 20 tecken.', 20);
$accountPerson->addRule('regex', 
    'Användarnamnet får bara innehålla bokstäver a-z, A-Z.', '/^[a-zA-Z]+$/');

// Lösenord
$newPasswordPerson = $fsAccount->addElement('password', 'password', 
    array('style' => 'width: 300px;'),
    array('label' => 'Lösenord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', 
    array('style' => 'width: 300px;'),
    array('label' => 'Lösenord igen:'));

$newPasswordPerson->addRule('required', 'Du måste ange ett lösenord.');
$passwordRep      ->addRule('required', 'Du måste upprepa lösenordet.');
$newPasswordPerson->addRule('minlength', 
    'Lösenordet måste innehålla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 
    'Lösenordet får inte vara längre än 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 'Du har angett två olika lösenord.', 
    $passwordRep);


// Skicka lösenord?
$sendPassword = $fsAccount->addElement('checkbox', 'send', array('value' => '1'))
    ->setContent('Skicka lösenordet med mejl till användaren');


// Behörighetsgrupp
$fsAuthority = $form->addElement('fieldset')->setLabel('Behörighet');
if ($arrayPerson[3] == "adm") {
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', 
        array('value' => 'usr'))
        ->setContent('Vanlig användare');
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', 
        array('value' => 'adm', 'checked' => 'checked'))
        ->setContent('Administratör');
} else {
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', 
        array('value' => 'usr', 'checked' => 'checked'))
        ->setContent('Vanlig användare');
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', 
        array('value' => 'adm'))
        ->setContent('Administratör');
}


// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', 
    array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="Återställ" href="?p=edit_acnt&amp;id='.$idPerson.'" >
    <img src="../images/b_undo.gif" alt="Återställ" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p='.$redirect.'" >
    <img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


/*
 * Behandla informationen i formuläret.
 */

// Ta bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

$mainTextHTML = "";

//Om sidan är riktigt ifylld så uppdatera databasen.
if ($form->validate()) {
    
    //Tvätta inparametrarna.
    $formValues       = $form->getValue();
    $accountPerson 	  = $dbAccess->WashParameter(
        strip_tags($formValues['account']));
    $behorighetPerson = $dbAccess->WashParameter(
        strip_tags($formValues['authority']));
    $passwordPerson   = $dbAccess->WashParameter(
        strip_tags($formValues['password']));

    if ($idPerson) { //Om användaren redan finns så uppdateras databasen.
        $query = <<<QUERY
UPDATE {$tablePerson} SET 
    accountPerson = '{$accountPerson}',
    passwordPerson = md5('{$passwordPerson}'),
    behorighetPerson = '{$behorighetPerson}'
    WHERE idPerson = '{$idPerson}';
QUERY;
    } else { //Annars läggs en ny användare in.
        $query = <<<QUERY
INSERT INTO {$tablePerson} (accountPerson, passwordPerson, behorighetPerson)
    VALUES ('{$accountPerson}', md5('{$passwordPerson}'), '{$behorighetPerson}');
QUERY;
    }
    $dbAccess->SingleQuery($query);

    // Om $idPerson inte innehåller något är det en ny användare. 
    // Hämta då dennes id.
    if (!$idPerson) {
        $idPerson = $dbAccess->LastId();
        $redirect = "edit_usr&id=".$idPerson;
    }
    if ($debugEnable) $debug .= "idPerson: " . $idPerson . "<br />\r\n";

    // Skicka lösenordet i mejl om detta är begärt.
    if (isset($formValues['send'])) {
        if ($debugEnable) $debug.="send= ".$formValues['send']."<br />\r\n";
        // Hämta mejladress. från personen eller dess målsman.
        $query = "
            SELECT ePostPerson FROM {$tablePerson} 
            WHERE idPerson = '{$idPerson}';";
        $result = $dbAccess->SingleQuery($query);
        $row = $result->fetch_object();
        $result->close();
        if ($row->ePostPerson) {
            $eMailAdr = $row->ePostPerson;
        } else {
            $query = "
                SELECT ePostMalsman FROM 
                    (({$tablePerson} JOIN {$tableElev} 
                    ON idPerson = elev_idPerson)
                    JOIN {$viewMalsman} ON idPerson = idElev)
                WHERE idElev = '{$idPerson}';
            ";
            if ($result = $dbAccess->SingleQuery($query)) {
                $row = $result->fetch_object();
                $result->close();
            }
            if (isset($row->ePostMalsman)) {
                $eMailAdr = $row->ePostMalsman;
            } else {
                $eMailAdr = "";
            }
        }
        if ($eMailAdr) { // Om vi har hittat en e-postadress.
        
            // Send mail
            $headers =  WS_MAILHEADERS;
            $subject = "Svenska skolföreningen";
            $text = 
                "Din användarinformation till Svenska skolföreningens hemsida.\r\n".
                "\r\n".
                "Användarnamn: ".$accountPerson."\r\n".
                "Lösenord: ".$passwordPerson."\r\n".
                "\r\n".
                "Du kan själv logga in och ändra ditt lösenord.";
            mail( $eMailAdr, $subject, $text, $headers);
            if ($debugEnable) $debug.="Mail to: ".$eMailAdr." Subj: ".$subject
                ." Headers: ".$headers."<br /> \n";
                
        } else { // Om vi inte har hittat någon adress.
            $_SESSION['errorMessage'] = 
                "Det finns ingen mejladress att skicka lösenordet till i 
                databasen!";
        }
    }
    
    // Hoppa vidare till nästa sida om inte debug.
    if ($debugEnable) {
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formuläret inför ny visning.
        $mainTextHTML .= "<a title='Vidare' href='?p={$redirect}'>
            <img src='../images/b_enter.gif' alt='Vidare' /></a> <br />\n";

    } else {
        header('Location: ' . WS_SITELINK . "?p={$redirect}");
        exit;
    }
}


/*
 * Om formuläret inte är riktigt ifyllt så skrivs det ut igen med kommentarer.
 */

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med <em>*</em>'
    ))
    ->setTemplateForId('submit', '<div class="element">{element} or 
        <a href="/">Cancel</a></div>')
;
$form->render($renderer);


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Editera användarkonto";

$mainTextHTML .= <<<HTMLCode
<h2>{$pageTitle}</h2>
<p>Formulär för att skapa en ny användaridentitet eller editera en gammal.</p>
<p>Lösenordet är slumpgenererat. Vill du byta så gör det.</p>
HTMLCode;

$mainTextHTML .= $renderer;

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


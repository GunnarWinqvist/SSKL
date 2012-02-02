<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditAccount.php
// Anropas med 'edit_account' från index.php.
// Visar ett formulär för kontoinformation med användarnamn, lösenord och behörighet.
// Formuläret genereras med QuickForm2, kontrollerar att allt är riktigt ifyllt och uppdaterar 
// databasen.
// 
// Input: 'id' eller NULL
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // Måste vara minst adm för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns och bestäm vilken som är nästa sida.

$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
if ($idPerson) $redirect = "show_user&id=".$idPerson;
else           $redirect = "search_user";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen 

$dbAccess       = new CdbAccess();
$tablePerson    = DB_PREFIX . 'Person';
$tableElev      = DB_PREFIX . 'Elev';
$viewMalsman    = DB_PREFIX . 'ListaMalsman';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om $idPerson har ett värde så ska en användare editeras. Hämta då den nuvarande informationen ur 
// databasen.

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


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skapa ett slumplösenord.

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


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formuläret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

$formAction = WS_SITELINK . "?p=edit_account&id=".$idPerson; // Pekar tillbaka på samma sida igen.
$form       = new HTML_QuickForm2('account', 'post', array('action' => $formAction), array('name' => 'account'));

// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'account'     => $arrayPerson[1],
    'password'    => $pwd,
    'passwordRep' => $pwd
)));

$fsAccount = $form->addElement('fieldset')->setLabel('Användarkonto');

// Användarnamn
$accountPerson = $fsAccount->addElement(
    'text', 'account', array('style' => 'width: 300px;'), array('label' => 'Användarnamn:') );
$accountPerson->addRule('required', 'Du måste ange ett användarnamn');
$accountPerson->addRule('maxlength', 'Användarnamnet får inte vara längre än 20 tecken.', 20);
$accountPerson->addRule('regex', 'Användarnamnet får bara innehålla bokstäver a-z, A-Z.', '/^[a-zA-Z]+$/');

// Lösenord
//$oldPasswordPerson = $fsAccount->addElement('password', 'oldPasswordPerson', array('style' => 'width: 300px;'),
//                                array('label' => 'Ditt gamla lösenord:'));
$newPasswordPerson = $fsAccount->addElement('password', 'password', array('style' => 'width: 300px;'),
                                array('label' => 'Lösenord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', array('style' => 'width: 300px;'),
                                array('label' => 'Lösenord igen:'));

//$oldPasswordPerson->addRule('required', 'Du måste ange ditt gamla lösenord.');
$newPasswordPerson->addRule('required', 'Du måste ange ett lösenord.');
$passwordRep      ->addRule('required', 'Du måste upprepa lösenordet.');
$newPasswordPerson->addRule('minlength', 'Lösenordet måste innehålla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 'Lösenordet får inte vara längre än 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 'Du har angett två olika lösenord.', $passwordRep);


// Skicka lösenord?
$sendPassword = $fsAccount->addElement('checkbox', 'send', array('value' => '1'))
    ->setContent('Skicka lösenordet med mejl till användaren');
$fsAccount->addElement('static', 'comment')
               ->setContent('Denna tjänst fungerar för närvarande inte till gmail-adresser. Om du har en gmail-adress 
                    registrerad i föreningens register så måste du skicka en lösenordsförfrågan manuellt till 
                        registrering@svenskaskolankualalumpur.com');


// Behörighetsgrupp
$fsAuthority = $form->addElement('fieldset')->setLabel('Behörighet');
if ($arrayPerson[3] == "adm") {
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'usr'))
        ->setContent('Vanlig användare');
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'adm', 'checked' => 'checked'))
        ->setContent('Administratör');
} else {
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'usr', 'checked' => 'checked'))
        ->setContent('Vanlig användare');
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'adm'))
        ->setContent('Administratör');
}

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="Återställ" href="?p=edit_account&amp;id='.$idPerson.'" ><img src="../images/b_undo.gif" alt="Återställ" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p='.$redirect.'" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formuläret.

// Ta bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

//Om sidan är riktigt ifylld så uppdatera databasen.
if ($form->validate()) {

    //Tvätta inparametrarna.
    $formValues       = $form->getValue();
    $accountPerson 	  = $dbAccess->WashParameter(strip_tags($formValues['account']));
    $behorighetPerson = $dbAccess->WashParameter(strip_tags($formValues['authority']));
    $passwordPerson   = $dbAccess->WashParameter(strip_tags($formValues['password']));

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

    // Om $idPerson inte innehåller något är det en ny användare. Hämta då dennes id.
    if (!$idPerson) {
        $idPerson = $dbAccess->LastId();
        $redirect = "show_user&id=".$idPerson;
    }
    if ($debugEnable) $debug .= "idPerson: " . $idPerson . "<br /> \n";

    // Skicka lösenordet i mejl om detta är begärt.
    if ($formValues['send']) {
        // Hämta mejladress. från personen eller dess målsman.
        $query = "SELECT ePostPerson FROM {$tablePerson} WHERE idPerson = '{$idPerson}';";
        $result = $dbAccess->SingleQuery($query);
        $row = $result->fetch_object();
        $result->close();
        if ($row->ePostPerson) {
            $eMailAdr = $row->ePostPerson;
        } else {
            $query = <<<QUERY
SELECT ePostMalsman FROM 
    (({$tablePerson} JOIN {$tableElev} ON idPerson = elev_idPerson)
    JOIN {$viewMalsman} ON idPerson = idElev)
    WHERE idElev = '{$idPerson}';
QUERY;
            $result = $dbAccess->SingleQuery($query);
            if ($result) {
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
            $headers =  "From: registrering@svenskaskolankualalumpur.com"."\r\n".
                        "Reply-To: registrering@svenskaskolankualalumpur.com"."\r\n".
                        "Content-type: text/html; charset=iso-8859-1"."\r\n".
                        "MIME-Version: 1.0"."\r\n".
                        "Return-Path: <registrering@svenskaskolankualalumpur.com>";
            $subject = "Svenska skolföreningen";
            $text = <<<Text
Din användarinformation till Svenska skolföreningens hemsida.

Användarnamn: {$accountPerson}
Lösenord: {$passwordPerson}

Du kan själv logga in och ändra ditt lösenord.

Text;
            mail( $eMailAdr, $subject, $text, $headers);
            if ($debugEnable) $debug .= "Mail to: ".$eMailAdr." Subj: ".$subject." Headers: ".$headers."<br /> \n";
        } else { // Om vi inte har hittat någon adress.
            $_SESSION['errorMessage'] = "Det finns ingen mejladress att skicka lösenordet till i databasen!";
        }
    }
    
    // Hoppa vidare till nästa sida om inte debug.
    if ($debugEnable) {
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formuläret inför ny visning.
    } else {
        header('Location: ' . WS_SITELINK . "?p={$redirect}");
        exit;
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
        'required_note' => ''
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


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Editera användarkonto";

$mainTextHTML = <<<HTMLCode
<h2>{$pageTitle}</h2>
<p>Formulär för att skapa en ny användaridentitet eller editera en gammal.</p>
<p>Lösenordet är slumpgenererat. Vill du byta så gör det.</p>
<p>Obligatoriska fält är markerade med en (<em style="color:red;">*</em>).</p>
HTMLCode;

$mainTextHTML .= $renderer;

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


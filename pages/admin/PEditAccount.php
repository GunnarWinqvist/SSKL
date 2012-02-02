<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditAccount.php
// Anropas med 'edit_account' fr�n index.php.
// Visar ett formul�r f�r kontoinformation med anv�ndarnamn, l�senord och beh�righet.
// Formul�ret genereras med QuickForm2, kontrollerar att allt �r riktigt ifyllt och uppdaterar 
// databasen.
// 
// Input: 'id' eller NULL
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // M�ste vara minst adm f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan om det finns och best�m vilken som �r n�sta sida.

$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
if ($idPerson) $redirect = "show_user&id=".$idPerson;
else           $redirect = "search_user";


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen 

$dbAccess       = new CdbAccess();
$tablePerson    = DB_PREFIX . 'Person';
$tableElev      = DB_PREFIX . 'Elev';
$viewMalsman    = DB_PREFIX . 'ListaMalsman';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Om $idPerson har ett v�rde s� ska en anv�ndare editeras. H�mta d� den nuvarande informationen ur 
// databasen.

if ($idPerson) {
    $idPerson 		= $dbAccess->WashParameter($idPerson);
    $query          = "SELECT * FROM {$tablePerson} WHERE idPerson = {$idPerson};";
    $result         = $dbAccess->SingleQuery($query); 
    $arrayPerson    = $result->fetch_row();
    $result->close();
} else {
    // Nollst�ll alla parametrar om vi ska skapa en ny person.
    $arrayPerson     = array("","","","","","","","","","");
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skapa ett slumpl�senord.

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
// Generera formul�ret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

$formAction = WS_SITELINK . "?p=edit_account&id=".$idPerson; // Pekar tillbaka p� samma sida igen.
$form       = new HTML_QuickForm2('account', 'post', array('action' => $formAction), array('name' => 'account'));

// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'account'     => $arrayPerson[1],
    'password'    => $pwd,
    'passwordRep' => $pwd
)));

$fsAccount = $form->addElement('fieldset')->setLabel('Anv�ndarkonto');

// Anv�ndarnamn
$accountPerson = $fsAccount->addElement(
    'text', 'account', array('style' => 'width: 300px;'), array('label' => 'Anv�ndarnamn:') );
$accountPerson->addRule('required', 'Du m�ste ange ett anv�ndarnamn');
$accountPerson->addRule('maxlength', 'Anv�ndarnamnet f�r inte vara l�ngre �n 20 tecken.', 20);
$accountPerson->addRule('regex', 'Anv�ndarnamnet f�r bara inneh�lla bokst�ver a-z, A-Z.', '/^[a-zA-Z]+$/');

// L�senord
//$oldPasswordPerson = $fsAccount->addElement('password', 'oldPasswordPerson', array('style' => 'width: 300px;'),
//                                array('label' => 'Ditt gamla l�senord:'));
$newPasswordPerson = $fsAccount->addElement('password', 'password', array('style' => 'width: 300px;'),
                                array('label' => 'L�senord:'));
$passwordRep = $fsAccount->addElement('password', 'passwordRep', array('style' => 'width: 300px;'),
                                array('label' => 'L�senord igen:'));

//$oldPasswordPerson->addRule('required', 'Du m�ste ange ditt gamla l�senord.');
$newPasswordPerson->addRule('required', 'Du m�ste ange ett l�senord.');
$passwordRep      ->addRule('required', 'Du m�ste upprepa l�senordet.');
$newPasswordPerson->addRule('minlength', 'L�senordet m�ste inneh�lla minst 5 tecken.', 5);
$newPasswordPerson->addRule('maxlength', 'L�senordet f�r inte vara l�ngre �n 20 tecken.', 20);
$newPasswordPerson->addRule('eq', 'Du har angett tv� olika l�senord.', $passwordRep);


// Skicka l�senord?
$sendPassword = $fsAccount->addElement('checkbox', 'send', array('value' => '1'))
    ->setContent('Skicka l�senordet med mejl till anv�ndaren');
$fsAccount->addElement('static', 'comment')
               ->setContent('Denna tj�nst fungerar f�r n�rvarande inte till gmail-adresser. Om du har en gmail-adress 
                    registrerad i f�reningens register s� m�ste du skicka en l�senordsf�rfr�gan manuellt till 
                        registrering@svenskaskolankualalumpur.com');


// Beh�righetsgrupp
$fsAuthority = $form->addElement('fieldset')->setLabel('Beh�righet');
if ($arrayPerson[3] == "adm") {
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'usr'))
        ->setContent('Vanlig anv�ndare');
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'adm', 'checked' => 'checked'))
        ->setContent('Administrat�r');
} else {
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'usr', 'checked' => 'checked'))
        ->setContent('Vanlig anv�ndare');
    $behorighetPerson = $fsAuthority->addElement('radio', 'authority', array('value' => 'adm'))
        ->setContent('Administrat�r');
}

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="�terst�ll" href="?p=edit_account&amp;id='.$idPerson.'" ><img src="../images/b_undo.gif" alt="�terst�ll" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p='.$redirect.'" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formul�ret.

// Ta bort 'space' f�rst och sist p� alla v�rden.
$form->addRecursiveFilter('trim'); 

//Om sidan �r riktigt ifylld s� uppdatera databasen.
if ($form->validate()) {

    //Tv�tta inparametrarna.
    $formValues       = $form->getValue();
    $accountPerson 	  = $dbAccess->WashParameter(strip_tags($formValues['account']));
    $behorighetPerson = $dbAccess->WashParameter(strip_tags($formValues['authority']));
    $passwordPerson   = $dbAccess->WashParameter(strip_tags($formValues['password']));

    if ($idPerson) { //Om anv�ndaren redan finns s� uppdateras databasen.
        $query = <<<QUERY
UPDATE {$tablePerson} SET 
    accountPerson = '{$accountPerson}',
    passwordPerson = md5('{$passwordPerson}'),
    behorighetPerson = '{$behorighetPerson}'
    WHERE idPerson = '{$idPerson}';
QUERY;
    } else { //Annars l�ggs en ny anv�ndare in.
        $query = <<<QUERY
INSERT INTO {$tablePerson} (accountPerson, passwordPerson, behorighetPerson)
    VALUES ('{$accountPerson}', md5('{$passwordPerson}'), '{$behorighetPerson}');
QUERY;
    }
    $dbAccess->SingleQuery($query);

    // Om $idPerson inte inneh�ller n�got �r det en ny anv�ndare. H�mta d� dennes id.
    if (!$idPerson) {
        $idPerson = $dbAccess->LastId();
        $redirect = "show_user&id=".$idPerson;
    }
    if ($debugEnable) $debug .= "idPerson: " . $idPerson . "<br /> \n";

    // Skicka l�senordet i mejl om detta �r beg�rt.
    if ($formValues['send']) {
        // H�mta mejladress. fr�n personen eller dess m�lsman.
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
            $subject = "Svenska skolf�reningen";
            $text = <<<Text
Din anv�ndarinformation till Svenska skolf�reningens hemsida.

Anv�ndarnamn: {$accountPerson}
L�senord: {$passwordPerson}

Du kan sj�lv logga in och �ndra ditt l�senord.

Text;
            mail( $eMailAdr, $subject, $text, $headers);
            if ($debugEnable) $debug .= "Mail to: ".$eMailAdr." Subj: ".$subject." Headers: ".$headers."<br /> \n";
        } else { // Om vi inte har hittat n�gon adress.
            $_SESSION['errorMessage'] = "Det finns ingen mejladress att skicka l�senordet till i databasen!";
        }
    }
    
    // Hoppa vidare till n�sta sida om inte debug.
    if ($debugEnable) {
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formul�ret inf�r ny visning.
    } else {
        header('Location: ' . WS_SITELINK . "?p={$redirect}");
        exit;
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
$pageTitle = "Editera anv�ndarkonto";

$mainTextHTML = <<<HTMLCode
<h2>{$pageTitle}</h2>
<p>Formul�r f�r att skapa en ny anv�ndaridentitet eller editera en gammal.</p>
<p>L�senordet �r slumpgenererat. Vill du byta s� g�r det.</p>
<p>Obligatoriska f�lt �r markerade med en (<em style="color:red;">*</em>).</p>
HTMLCode;

$mainTextHTML .= $renderer;

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


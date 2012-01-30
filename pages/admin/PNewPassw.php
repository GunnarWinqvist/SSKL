<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNewPassw.php
// Anropas med 'new_passw' från index.php.
// På sidan kan du ange en epostadress till vilken du vill ha ett nytt lösenord skickat. 
// Detta verkställs på PNewPassw2
// Input: -
// Output: 'ePost' som POST's.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();



///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formuläret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

$formAction = WS_SITELINK . "?p=new_passw"; // Pekar tillbaka på samma sida igen.
$form       = new HTML_QuickForm2('new_passw', 'post', array('action' => $formAction), array('name' => 'new_passw'));

$fsAccount  = $form->addElement('fieldset')->setLabel('Skicka nytt lösenord');

$ePost      = $fsAccount->addElement(
                'text', 'ePost', array('style' => 'width: 300px;'), array('label' => 'Fyll i din epostadress:') );
$ePost->addRule('required', 'Fyll i din e-postadress som du registrerat i svenska skolans register.');
$ePost->addRule('regex', 'Det är inte en korrekt e-postadress.', 
                "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Skicka'));
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=main" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formuläret.

// Ta bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

$mainTextHTML = "";

if ($form->validate()) { // Om sidan är riktigt ifylld.
    
    // Förbered databasen 
    $dbAccess       = new CdbAccess();
    $tablePerson    = DB_PREFIX . 'Person';

    //Tvätta inparametrarna.
    $formValues = $form->getValue();
    $ePost 	    = $dbAccess->WashParameter($formValues['ePost']);

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

    // Kolla om epostadressen finns i databasen.
    $query = "SELECT idPerson, accountPerson FROM {$tablePerson} WHERE ePostPerson = '{$ePost}';";

    if ($ePost AND $result = $dbAccess->SingleQuery($query)) {
        // Adressen finns i registret. Uppdatera och skicka nytt password.
        $row = $result->fetch_object();
        $result->close();
        $query = <<<QUERY
UPDATE {$tablePerson} SET 
    passwordPerson = md5('{$pwd}')
    WHERE idPerson = '{$row->idPerson}';
QUERY;
        $dbAccess->SingleQuery($query);
        $subject = "Nytt losenord";
        $text = <<<Text
Din anvandarinformation till Svenska skolforeningens hemsida.
Anvandarnamn: {$row->accountPerson}
Losenord: {$pwd}

Du kan sjalv logga in pa sidan och andra ditt losenord.
Text;
        mail( $ePost, $subject, $text);
        $mainTextHTML .= "<p>Ett nytt lösenord har nu skickats till den angivna epostadressen.</p>  \n";
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formuläret inför ny visning.


    } else {
        $mainTextHTML .= "<p>Den angivna epostadressen kunde inte hittas i databasen.</p>  \n";
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
$mainTextHTML .= $renderer;


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Nytt lösenord 1";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


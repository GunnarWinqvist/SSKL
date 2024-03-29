<?php

/**
 * New password (new_pwd)
 *
 * P� sidan kan du ange en epostadress till vilken du vill ha ett nytt 
 * l�senord skickat.
 *
 */


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formul�ret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

$formAction = WS_SITELINK . "?p=new_pwd"; // Pekar tillbaka p� samma sida igen.
$form = new HTML_QuickForm2('new_passw', 'post', 
    array('action' => $formAction), 
    array('name' => 'new_passw'));

$fsAccount = $form->addElement('fieldset')->setLabel('Skicka nytt l�senord');

$ePost = $fsAccount->addElement('text', 'ePost', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Fyll i din epostadress:') );
$ePost->addRule('required', 
    'Fyll i din e-postadress som du registrerat i svenska skolans register.');
$ePost->addRule('regex', 'Det �r inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");

// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', 
    array('src' => '../images/b_enter.gif', 'title' => 'Skicka'));
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=main" >
        <img src="../images/b_cancel.gif" alt="Avbryt" /></a>');


/*
 * Behandla informationen i formul�ret.
 */

// Ta bort 'space' f�rst och sist p� alla v�rden.
$form->addRecursiveFilter('trim'); 

$mainTextHTML = "";

if ($form->validate()) { // Om sidan �r riktigt ifylld.
    
    // F�rbered databasen 
    $dbAccess       = new CdbAccess();
    $tablePerson    = DB_PREFIX . 'Person';

    //Tv�tta inparametrarna.
    $formValues = $form->getValue();
    $eMailAdr 	    = $dbAccess->WashParameter($formValues['ePost']);

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

    // Kolla om epostadressen finns i databasen.
    $query = "
        SELECT idPerson, accountPerson FROM {$tablePerson} 
        WHERE ePostPerson = '{$eMailAdr}';";

    if ($eMailAdr AND $result = $dbAccess->SingleQuery($query)) {
        // Adressen finns i registret. Uppdatera och skicka nytt password.
        $row = $result->fetch_object();
        $result->close();
        $query = <<<QUERY
UPDATE {$tablePerson} SET 
    passwordPerson = md5('{$pwd}')
    WHERE idPerson = '{$row->idPerson}';
QUERY;
        $dbAccess->SingleQuery($query);
        
        // Send mail
        $headers =  WS_MAILHEADERS;
        $subject = "Svenska skolf�reningen";
        $text = 
            "Din anv�ndarinformation till Svenska skolf�reningens hemsida."."\r\n".
            "\r\n".
            "Anv�ndarnamn: ".$row->accountPerson."\r\n".
            "L�senord: ".$pwd."\r\n".
            "\r\n".
            "Du kan sj�lv logga in och �ndra ditt l�senord.";
        mail( $eMailAdr, $subject, $text, $headers);
        if ($debugEnable) $debug.="Mail to: ".$eMailAdr." Subj: ".$subject
            ." Headers: ".$headers."<br /> \n";

        $mainTextHTML .= "<p>Ett nytt l�senord har nu skickats till den 
            angivna epostadressen.</p>  \n";
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formul�ret inf�r ny visning.


    } else {
        $mainTextHTML .= "<p>Den angivna epostadressen kunde inte hittas i 
            databasen.</p>  \n";
    }
}


/*
 * Om formul�ret inte �r riktigt ifyllt s� skrivs det ut igen med kommentarer.
 */

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'F�ljand information saknas eller �r felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => ''
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
$pageTitle = "Nytt l�senord 1";

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


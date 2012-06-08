<?php

/**
 * Anmälan (appl)
 *
 * Sidan genererar ett anmälningsformulär med 'QuickForm2'. Vid submit 
 * valideras sidan och om den är riktigt ifylld skickas ett mail med all info
 * till webmaster. Om den är felaktigt ifylld markeras det felaktiga och man 
 * får en chans till. 
 *
 */


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


/*
 * Generera formuläret med QuickForm2.
 */
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Alternativ för nationalitet.
$options = array(
    '--' => '--', 'se' => 'Svensk', 'no' => 'Norsk', 'dk' => 'Dansk',
    'fi' => 'Finsk', 'nn' => 'Annan');

$formAction = WS_SITELINK . "?p=appl"; // Pekar tillbaka på samma sida igen.
$form = new HTML_QuickForm2('application', 'post', 
    array('action' => $formAction), array('name' => 'application'));


// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'personnummerElev'  => 'ååååmmdd-nnnn',
    'stadsdelBostad'    => 'Mont Kiara, Ampang, ...',
    'stadBostad'        => 'Kuala Lumpur',
    'statBostad'        => 'Kuala Lumpur, Selangor, ...',
    'skolaElev'         => 'MKIS, ISKL, ...'
)));


// Data för eleven
$fsElev = $form->addElement('fieldset')->setLabel('Eleven');

$fornamnPerson = $fsElev->addElement('text', 'fornamnPerson', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Förnamn:') );
$fornamnPerson->addRule('required', 'Fyll i elevens förnamn');
$fornamnPerson->addRule('maxlength', 
    'Elevens förnamn får max vara 50 tecken.', 50);

$efteramnPerson = $fsElev->addElement('text', 'efternamnPerson', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Efternamn:') );
$efteramnPerson->addRule('required', 'Fyll i elevens efternamn');
$efteramnPerson->addRule('maxlength', 
    'Elevens efternamn får max vara 50 tecken.', 50);

$nationalitetElev = $fsElev->addElement('select', 'nationalitetElev', null, 
    array('options' => $options, 'label' => 'Nationalitet:') );

$personnummerElev = $fsElev->addElement('text', 'personnummerElev', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Personnummer:') );
$personnummerElev->addRule('required', 
    'Fyll i elevens personnummer eller födelsedatum.');
$personnummerElev->addRule('regex', 
    'Personnumret måste ha formen ååååmmdd-nnnn. Födelsedatum formen ååååmmdd.',
    '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(-\d{4})?$/');
$kommentar = $fsElev->addElement('static', 'comment')
    ->setContent('Fyll i svenskt personnummer om eleven har det, annars 
    födelsedatum.');

$arskursElev = $fsElev->addElement('text', 'arskursElev', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Årskurs i ordinarie skola:') );
$arskursElev->addRule('maxlength', 'Ange årskursen med siffror.', 2);

$skolaElev = $fsElev->addElement('text', 'skolaElev', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Ordinarie skola:') );
$skolaElev->addRule('maxlength', 
    'Ordinarie skolas namn får max vara 50 tecken.', 50);


// Data för målsman
$fsMalsman = $form->addElement('fieldset')->setLabel('Målsman');
$fornamnMalsman = $fsMalsman->addElement('text', 'fornamnMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Förnamn:') );
$fornamnMalsman->addRule('required', 'Fyll i målsmans förnamn');
$fornamnMalsman->addRule('maxlength', 
    'Målsmans förnamn får max vara 50 tecken.', 50);

$efternamnMalsman = $fsMalsman->addElement('text', 'efternamnMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Efternamn:') );
$efternamnMalsman->addRule('required', 'Fyll i målsmans efternamn');
$efternamnMalsman->addRule('maxlength', 
    'Målsmans efternamn får max vara 50 tecken.', 50);

$nationalitetMalsman = $fsMalsman->addElement('select', 'nationalitetMalsman', 
    null, array('options' => $options, 'label' => 'Nationalitet:') );
$kommentar = $fsMalsman->addElement('static', 'comment')
    ->setContent('Minst en målsman måste vara svensk medborgare för att vi ska 
        få ekonomiskt bidrag för eleven.');

$adressBostad = $fsMalsman->addElement('text', 'adressBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Bostadsadress:') );
$adressBostad->addRule('maxlength', 
    'Bostadsadressen får max vara 100 tecken.', 100);

$stadsdelBostad = $fsMalsman->addElement('text', 'stadsdelBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Stadsdel:') );
$stadsdelBostad->addRule('maxlength', 
    'Stadsdelen får max vara 20 tecken.', 20);

$postnummerBostad = $fsMalsman->addElement('text', 'postnummerBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Postnummer:') );
$postnummerBostad->addRule('maxlength', 
    'Postnumret får max vara 10 tecken.', 10);

$stadBostad = $fsMalsman->addElement('text', 'stadBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Stad:') );
$stadBostad->addRule('maxlength', 
    'Stadens namn får max vara 20 tecken.', 20);

$statBostad = $fsMalsman->addElement('text', 'statBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Stat:') );
$statBostad->addRule('maxlength', 
    'Statsnamnet får max vara 20 tecken.', 20);

$telefonBostad = $fsMalsman->addElement('text', 'telefonBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Telefonnummer bostad:') );
$telefonBostad->addRule('maxlength', 
    'Telefonnumret får max vara 20 tecken.', 20);

$mobilMalsman = $fsMalsman->addElement('text', 'mobilMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Mobilnummer:') );
$mobilMalsman->addRule('maxlength', 
    'Mobilnumret får max vara 20 tecken.', 20);

$ePostMalsman = $fsMalsman->addElement('text', 'ePostMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'E-postadress:') );
$ePostMalsman->addRule('required', 'Fyll i målsmans e-postadress');
$ePostMalsman->addRule('regex', 'Det är inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");
$ePostMalsman->addRule('maxlength', 
    'E-postadressen får max vara 50 tecken.', 50);

// Sändknappen
$submitButton = $form->addElement('submit', 'submit', 
    array('value' => 'Skicka'));

// Tar bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

$mainTextHTML = "";
if ($form->validate()) { 
    //Om sidan är riktigt ifylld.
    $mainTextHTML .= "<h2>Din information har skickats till Svenska 
        Skolföreningen. Tack för din anmälan!</h2>";
    $eMailAdr = "registrering@svenskaskolankualalumpur.com";
    $subject = "Ny anmälan till SSKL";
    $headers = WS_MAILHEADERS;
    $text = "Ny anmälan till Svenska Skolföreningen i Kuala Lumpur. \n";
    foreach ($form->getValue() as $parameter => $value)
        $text .= $parameter . "\t" . $value . "\n";
    mail( $eMailAdr, $subject, $text, $headers);
    $form->removeChild($submitButton); // Tag bort sänd-knappen.
    $form->removeChild($kommentar); // Tag bort kommentarer.
    $form->toggleFrozen(true); // Frys formuläret inför ny visning.
    if ($debugEnable) $debug .= "eMailAdr=".$eMailAdr." subject=".$subject.
        "text=".$text." headers=".$headers."<br />\r\n";

}

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt 
            ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med en 
            (<em>*</em>).'
    ))
    ->setTemplateForId('submit', '<div class="element">{element} or 
        <a href="/">Cancel</a></div>')
;


$form->render($renderer);

$mainTextHTML .= $renderer;


/*
 * Skriv ut sidan.
 */
$page = new CHTMLPage(); 
$pageTitle = "Application";

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


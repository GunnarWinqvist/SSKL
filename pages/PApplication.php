<?php

/**
 * Anm�lan (appl)
 *
 * Sidan genererar ett anm�lningsformul�r med 'QuickForm2'. Vid submit 
 * valideras sidan och om den �r riktigt ifylld skickas ett mail med all info
 * till webmaster. Om den �r felaktigt ifylld markeras det felaktiga och man 
 * f�r en chans till. 
 *
 */


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');


/*
 * Generera formul�ret med QuickForm2.
 */
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Alternativ f�r nationalitet.
$options = array(
    '--' => '--', 'se' => 'Svensk', 'no' => 'Norsk', 'dk' => 'Dansk',
    'fi' => 'Finsk', 'nn' => 'Annan');

$formAction = WS_SITELINK . "?p=appl"; // Pekar tillbaka p� samma sida igen.
$form = new HTML_QuickForm2('application', 'post', 
    array('action' => $formAction), array('name' => 'application'));


// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'personnummerElev'  => '����mmdd-nnnn',
    'stadsdelBostad'    => 'Mont Kiara, Ampang, ...',
    'stadBostad'        => 'Kuala Lumpur',
    'statBostad'        => 'Kuala Lumpur, Selangor, ...',
    'skolaElev'         => 'MKIS, ISKL, ...'
)));


// Data f�r eleven
$fsElev = $form->addElement('fieldset')->setLabel('Eleven');

$fornamnPerson = $fsElev->addElement('text', 'fornamnPerson', 
    array('style' => 'width: 300px;'), 
    array('label' => 'F�rnamn:') );
$fornamnPerson->addRule('required', 'Fyll i elevens f�rnamn');
$fornamnPerson->addRule('maxlength', 
    'Elevens f�rnamn f�r max vara 50 tecken.', 50);

$efteramnPerson = $fsElev->addElement('text', 'efternamnPerson', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Efternamn:') );
$efteramnPerson->addRule('required', 'Fyll i elevens efternamn');
$efteramnPerson->addRule('maxlength', 
    'Elevens efternamn f�r max vara 50 tecken.', 50);

$nationalitetElev = $fsElev->addElement('select', 'nationalitetElev', null, 
    array('options' => $options, 'label' => 'Nationalitet:') );

$personnummerElev = $fsElev->addElement('text', 'personnummerElev', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Personnummer:') );
$personnummerElev->addRule('required', 
    'Fyll i elevens personnummer eller f�delsedatum.');
$personnummerElev->addRule('regex', 
    'Personnumret m�ste ha formen ����mmdd-nnnn. F�delsedatum formen ����mmdd.',
    '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(-\d{4})?$/');
$kommentar = $fsElev->addElement('static', 'comment')
    ->setContent('Fyll i svenskt personnummer om eleven har det, annars 
    f�delsedatum.');

$arskursElev = $fsElev->addElement('text', 'arskursElev', 
    array('style' => 'width: 300px;'), 
    array('label' => '�rskurs i ordinarie skola:') );
$arskursElev->addRule('maxlength', 'Ange �rskursen med siffror.', 2);

$skolaElev = $fsElev->addElement('text', 'skolaElev', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Ordinarie skola:') );
$skolaElev->addRule('maxlength', 
    'Ordinarie skolas namn f�r max vara 50 tecken.', 50);


// Data f�r m�lsman
$fsMalsman = $form->addElement('fieldset')->setLabel('M�lsman');
$fornamnMalsman = $fsMalsman->addElement('text', 'fornamnMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'F�rnamn:') );
$fornamnMalsman->addRule('required', 'Fyll i m�lsmans f�rnamn');
$fornamnMalsman->addRule('maxlength', 
    'M�lsmans f�rnamn f�r max vara 50 tecken.', 50);

$efternamnMalsman = $fsMalsman->addElement('text', 'efternamnMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Efternamn:') );
$efternamnMalsman->addRule('required', 'Fyll i m�lsmans efternamn');
$efternamnMalsman->addRule('maxlength', 
    'M�lsmans efternamn f�r max vara 50 tecken.', 50);

$nationalitetMalsman = $fsMalsman->addElement('select', 'nationalitetMalsman', 
    null, array('options' => $options, 'label' => 'Nationalitet:') );
$kommentar = $fsMalsman->addElement('static', 'comment')
    ->setContent('Minst en m�lsman m�ste vara svensk medborgare f�r att vi ska 
        f� ekonomiskt bidrag f�r eleven.');

$adressBostad = $fsMalsman->addElement('text', 'adressBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Bostadsadress:') );
$adressBostad->addRule('maxlength', 
    'Bostadsadressen f�r max vara 100 tecken.', 100);

$stadsdelBostad = $fsMalsman->addElement('text', 'stadsdelBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Stadsdel:') );
$stadsdelBostad->addRule('maxlength', 
    'Stadsdelen f�r max vara 20 tecken.', 20);

$postnummerBostad = $fsMalsman->addElement('text', 'postnummerBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Postnummer:') );
$postnummerBostad->addRule('maxlength', 
    'Postnumret f�r max vara 10 tecken.', 10);

$stadBostad = $fsMalsman->addElement('text', 'stadBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Stad:') );
$stadBostad->addRule('maxlength', 
    'Stadens namn f�r max vara 20 tecken.', 20);

$statBostad = $fsMalsman->addElement('text', 'statBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Stat:') );
$statBostad->addRule('maxlength', 
    'Statsnamnet f�r max vara 20 tecken.', 20);

$telefonBostad = $fsMalsman->addElement('text', 'telefonBostad', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Telefonnummer bostad:') );
$telefonBostad->addRule('maxlength', 
    'Telefonnumret f�r max vara 20 tecken.', 20);

$mobilMalsman = $fsMalsman->addElement('text', 'mobilMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Mobilnummer:') );
$mobilMalsman->addRule('maxlength', 
    'Mobilnumret f�r max vara 20 tecken.', 20);

$ePostMalsman = $fsMalsman->addElement('text', 'ePostMalsman', 
    array('style' => 'width: 300px;'), 
    array('label' => 'E-postadress:') );
$ePostMalsman->addRule('required', 'Fyll i m�lsmans e-postadress');
$ePostMalsman->addRule('regex', 'Det �r inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");
$ePostMalsman->addRule('maxlength', 
    'E-postadressen f�r max vara 50 tecken.', 50);

// S�ndknappen
$submitButton = $form->addElement('submit', 'submit', 
    array('value' => 'Skicka'));

// Tar bort 'space' f�rst och sist p� alla v�rden.
$form->addRecursiveFilter('trim'); 

$mainTextHTML = "";
if ($form->validate()) { 
    //Om sidan �r riktigt ifylld.
    $mainTextHTML .= "<h2>Din information har skickats till Svenska 
        Skolf�reningen. Tack f�r din anm�lan!</h2>";
    $eMailAdr = "registrering@svenskaskolankualalumpur.com";
    $subject = "Ny anm�lan till SSKL";
    $headers = WS_MAILHEADERS;
    $text = "Ny anm�lan till Svenska Skolf�reningen i Kuala Lumpur. \n";
    foreach ($form->getValue() as $parameter => $value)
        $text .= $parameter . "\t" . $value . "\n";
    mail( $eMailAdr, $subject, $text, $headers);
    $form->removeChild($submitButton); // Tag bort s�nd-knappen.
    $form->removeChild($kommentar); // Tag bort kommentarer.
    $form->toggleFrozen(true); // Frys formul�ret inf�r ny visning.
    if ($debugEnable) $debug .= "eMailAdr=".$eMailAdr." subject=".$subject.
        "text=".$text." headers=".$headers."<br />\r\n";

}

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'F�ljand information saknas eller �r felaktigt 
            ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska f�lt �r markerade med en 
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


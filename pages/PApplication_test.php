<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PApplication.php
// Anropas med 'appl' fr�n index.php.
// Sidan genererar ett anm�lningsformul�r med 'QuickForm2'. Vid submit valideras sidan och om den �r
// riktigt ifylld skickas ett mail med all info till webmaster. Om den �r felaktigt ifylld markeras
// det felaktiga och man f�r en chans till. 
// Input:  
// Output:   
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formul�ret med QuickForm2.

if (TP_PEARPATH) set_include_path(TP_PEARPATH);
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Alternativ f�r nationalitet.
$options = array(
    '--' => '--', 'se' => 'Svensk', 'no' => 'Norsk', 'dk' => 'Dansk',
    'fi' => 'Finsk', 'nn' => 'Annan');

$formAction = WS_SITELINK . "?p=appl"; // Pekar tillbaka p� samma sida igen.
$form = new HTML_QuickForm2('application', 'post', array('action' => $formAction), array('name' => 'application'));


// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'personnummerElev'=> '����mmdd-nnnn'
)));


// Data f�r eleven
$fsElev = $form->addElement('fieldset')->setLabel('Eleven');

$fornamnPerson = $fsElev->addElement(
    'text', 'fornamnPerson', array('style' => 'width: 300px;'), array('label' => 'F�rnamn:') );
$fornamnPerson->addRule('required', 'Fyll i elevens f�rnamn');
$fornamnPerson->addRule('maxlength', 'Elevens f�rnamn �r f�r l�ngt f�r databasen', 50);

$efteramnPerson = $fsElev->addElement(
    'text', 'efternamnPerson', array('style' => 'width: 300px;'), array('label' => 'Efternamn:') );
$efteramnPerson->addRule('required', 'Fyll i elevens efternamn');
$efteramnPerson->addRule('maxlength', 'Elevens efternamn �r f�r l�ngt f�r databasen', 50);

$nationalitetElev = $fsElev->addElement(
    'select', 'nationalitetElev', null, array('options' => $options, 'label' => 'Nationalitet:') );

$personnummerElev = $fsElev->addElement(
    'text', 'personnummerElev', array('style' => 'width: 300px;'), array('label' => 'Personnummer:') );
$personnummerElev->addRule('required', 'Fyll i elevens personnummer eller f�delsedatum.');
$personnummerElev->addRule('regex', 'Personnumret m�ste ha formen ����mmdd-nnnn. F�delsedatum formen ����mmdd.', 
    '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(-\d{4})?$/');
$kommentar = $fsElev->addElement('static', 'comment')
               ->setContent('Fyll i svenskt personnummer om eleven har det, annars f�delsedatum.');


// Data f�r m�lsman
$fsMalsman = $form->addElement('fieldset')->setLabel('M�lsman');
$fornamnMalsman = $fsMalsman->addElement(
    'text', 'fornamnMalsman', array('style' => 'width: 300px;'), array('label' => 'F�rnamn:') );
$fornamnMalsman->addRule('required', 'Fyll i m�lsmans f�rnamn');
$fornamnMalsman->addRule('maxlength', 'M�lsmans f�rnamn �r f�r l�ngt f�r databasen', 50);

$efternamnMalsman = $fsMalsman->addElement(
    'text', 'efternamnMalsman', array('style' => 'width: 300px;'), array('label' => 'Efternamn:') );
$efternamnMalsman->addRule('required', 'Fyll i m�lsmans efternamn');
$efternamnMalsman->addRule('maxlength', 'M�lsmans efternamn �r f�r l�ngt f�r databasen', 50);

$nationalitetMalsman = $fsMalsman->addElement(
    'select', 'nationalitetMalsman', null, array('options' => $options, 
                'label' => 'Nationalitet:') );

$adressBostad = $fsMalsman->addElement(
    'text', 'adressBostad', array('style' => 'width: 300px;'), array('label' => 'Bostadsadress:') );
$adressBostad->addRule('maxlength', 'Bostadsadressen �r f�r l�ng f�r databasen', 100);

$stadsdelBostad = $fsMalsman->addElement(
    'text', 'stadsdelBostad', array('style' => 'width: 300px;'), array('label' => 'Stadsdel:') );
$stadsdelBostad->addRule('maxlength', 'Stadsdelen �r f�r l�ng f�r databasen', 20);

$postnummerBostad = $fsMalsman->addElement(
    'text', 'postnummerBostad', array('style' => 'width: 300px;'), array('label' => 'Postnummer:') );
$postnummerBostad->addRule('maxlength', 'Postnumret �r f�r l�ngt f�r databasen', 10);

$statBostad = $fsMalsman->addElement(
    'text', 'statBostad', array('style' => 'width: 300px;'), array('label' => 'Stat:') );
$statBostad->addRule('maxlength', 'Statsnamnet �r f�r l�ngt f�r databasen', 20);

$telefonBostad = $fsMalsman->addElement(
    'text', 'telefonBostad', array('style' => 'width: 300px;'), array('label' => 'Telefonnummer bostad:') );
$telefonBostad->addRule('maxlength', 'Telefonnumret �r f�r l�ngt f�r databasen', 20);

$mobilMalsman = $fsMalsman->addElement(
    'text', 'mobilMalsman', array('style' => 'width: 300px;'), array('label' => 'Mobilnummer:') );
$mobilMalsman->addRule('maxlength', 'Mobilnumret �r f�r l�ngt f�r databasen', 20);

$ePostMalsman = $fsMalsman->addElement(
    'text', 'ePostMalsman', array('style' => 'width: 300px;'), array('label' => 'E-postadress:') );
$ePostMalsman->addRule('required', 'Fyll i m�lsmans e-postadress');
$ePostMalsman->addRule('regex', 'Det �r inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");
$ePostMalsman->addRule('maxlength', 'E-postadressen �r f�r l�ng f�r databasen', 50);

// S�ndknappen
$submitButton = $form->addElement('submit', 'submit', array('value' => 'Skicka'));

/* $submitButton = $form->addElement('static', 'comment')
               ->setContent('<div class="clear_button">
                    <a class="button" href="javascript:document.application.submit();" onclick="this.blur();">
                    <span>Skicka</span></a></div>');
*/

$form->addRecursiveFilter('trim'); // Tar bort 'space' f�rst och sist p� alla v�rden.

$mainTextHTML = "";
if ($form->validate()) { //Om sidan �r riktigt ifylld.
    $mainTextHTML .= "<h2>Din information har skickats till Svenska Skolf�reningen. Tack f�r din anm�lan!</h2>";
    $eMailAdr = "webmaster@svenskaskolankualalumpur.com";
    $subject = "Ny anm�lan";
    $text = var_dump($form->getValue());
    mail( $eMailAdr, $subject, $text);
    $form->removeChild($submitButton); // Tag bort s�nd-knappen.
    $fsElev->removeChild($kommentar); // Tag bort kommentartext.
    $form->toggleFrozen(true); // Frys formul�ret inf�r ny visning.
}

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

echo "Flag 1";
$form->render($renderer);
// Output javascript libraries, needed by hierselect
echo "Flag 2";
$mainTextHTML .= $renderer->getJavascriptBuilder()->getLibraries(true, true);
echo "Flag 3";
$mainTextHTML .= $renderer;
echo "Flag 4";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Skriv ut sidan.

$page = new CHTMLPage(); 
$pageTitle = "Template";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


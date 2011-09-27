<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PApplication.php
// Anropas med 'appl' från index.php.
// Sidan genererar ett anmälningsformulär med 'QuickForm2'. Vid submit valideras sidan och om den är
// riktigt ifylld skickas ett mail med all info till webmaster. Om den är felaktigt ifylld markeras
// det felaktiga och man får en chans till. 
// Input:  
// Output:   
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formuläret med QuickForm2.

if (TP_PEARPATH) set_include_path(TP_PEARPATH);
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Alternativ för nationalitet.
$options = array(
    '--' => '--', 'se' => 'Svensk', 'no' => 'Norsk', 'dk' => 'Dansk',
    'fi' => 'Finsk', 'nn' => 'Annan');

$formAction = WS_SITELINK . "?p=appl"; // Pekar tillbaka på samma sida igen.
$form = new HTML_QuickForm2('application', 'post', array('action' => $formAction), array('name' => 'application'));


// data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'personnummerElev'=> 'ååååmmdd-nnnn'
)));


// Data för eleven
$fsElev = $form->addElement('fieldset')->setLabel('Eleven');

$fornamnPerson = $fsElev->addElement(
    'text', 'fornamnPerson', array('style' => 'width: 300px;'), array('label' => 'Förnamn:') );
$fornamnPerson->addRule('required', 'Fyll i elevens förnamn');
$fornamnPerson->addRule('maxlength', 'Elevens förnamn är för långt för databasen', 50);

$efteramnPerson = $fsElev->addElement(
    'text', 'efternamnPerson', array('style' => 'width: 300px;'), array('label' => 'Efternamn:') );
$efteramnPerson->addRule('required', 'Fyll i elevens efternamn');
$efteramnPerson->addRule('maxlength', 'Elevens efternamn är för långt för databasen', 50);

$nationalitetElev = $fsElev->addElement(
    'select', 'nationalitetElev', null, array('options' => $options, 'label' => 'Nationalitet:') );

$personnummerElev = $fsElev->addElement(
    'text', 'personnummerElev', array('style' => 'width: 300px;'), array('label' => 'Personnummer:') );
$personnummerElev->addRule('required', 'Fyll i elevens personnummer eller födelsedatum.');
$personnummerElev->addRule('regex', 'Personnumret måste ha formen ååååmmdd-nnnn. Födelsedatum formen ååååmmdd.', 
    '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(-\d{4})?$/');
$kommentar = $fsElev->addElement('static', 'comment')
               ->setContent('Fyll i svenskt personnummer om eleven har det, annars födelsedatum.');


// Data för målsman
$fsMalsman = $form->addElement('fieldset')->setLabel('Målsman');
$fornamnMalsman = $fsMalsman->addElement(
    'text', 'fornamnMalsman', array('style' => 'width: 300px;'), array('label' => 'Förnamn:') );
$fornamnMalsman->addRule('required', 'Fyll i målsmans förnamn');
$fornamnMalsman->addRule('maxlength', 'Målsmans förnamn är för långt för databasen', 50);

$efternamnMalsman = $fsMalsman->addElement(
    'text', 'efternamnMalsman', array('style' => 'width: 300px;'), array('label' => 'Efternamn:') );
$efternamnMalsman->addRule('required', 'Fyll i målsmans efternamn');
$efternamnMalsman->addRule('maxlength', 'Målsmans efternamn är för långt för databasen', 50);

$nationalitetMalsman = $fsMalsman->addElement(
    'select', 'nationalitetMalsman', null, array('options' => $options, 
                'label' => 'Nationalitet:') );

$adressBostad = $fsMalsman->addElement(
    'text', 'adressBostad', array('style' => 'width: 300px;'), array('label' => 'Bostadsadress:') );
$adressBostad->addRule('maxlength', 'Bostadsadressen är för lång för databasen', 100);

$stadsdelBostad = $fsMalsman->addElement(
    'text', 'stadsdelBostad', array('style' => 'width: 300px;'), array('label' => 'Stadsdel:') );
$stadsdelBostad->addRule('maxlength', 'Stadsdelen är för lång för databasen', 20);

$postnummerBostad = $fsMalsman->addElement(
    'text', 'postnummerBostad', array('style' => 'width: 300px;'), array('label' => 'Postnummer:') );
$postnummerBostad->addRule('maxlength', 'Postnumret är för långt för databasen', 10);

$statBostad = $fsMalsman->addElement(
    'text', 'statBostad', array('style' => 'width: 300px;'), array('label' => 'Stat:') );
$statBostad->addRule('maxlength', 'Statsnamnet är för långt för databasen', 20);

$telefonBostad = $fsMalsman->addElement(
    'text', 'telefonBostad', array('style' => 'width: 300px;'), array('label' => 'Telefonnummer bostad:') );
$telefonBostad->addRule('maxlength', 'Telefonnumret är för långt för databasen', 20);

$mobilMalsman = $fsMalsman->addElement(
    'text', 'mobilMalsman', array('style' => 'width: 300px;'), array('label' => 'Mobilnummer:') );
$mobilMalsman->addRule('maxlength', 'Mobilnumret är för långt för databasen', 20);

$ePostMalsman = $fsMalsman->addElement(
    'text', 'ePostMalsman', array('style' => 'width: 300px;'), array('label' => 'E-postadress:') );
$ePostMalsman->addRule('required', 'Fyll i målsmans e-postadress');
$ePostMalsman->addRule('regex', 'Det är inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");
$ePostMalsman->addRule('maxlength', 'E-postadressen är för lång för databasen', 50);

// Sändknappen
$submitButton = $form->addElement('submit', 'submit', array('value' => 'Skicka'));

/* $submitButton = $form->addElement('static', 'comment')
               ->setContent('<div class="clear_button">
                    <a class="button" href="javascript:document.application.submit();" onclick="this.blur();">
                    <span>Skicka</span></a></div>');
*/

$form->addRecursiveFilter('trim'); // Tar bort 'space' först och sist på alla värden.

$mainTextHTML = "";
if ($form->validate()) { //Om sidan är riktigt ifylld.
    $mainTextHTML .= "<h2>Din information har skickats till Svenska Skolföreningen. Tack för din anmälan!</h2>";
    $eMailAdr = "webmaster@svenskaskolankualalumpur.com";
    $subject = "Ny anmälan";
    $text = var_dump($form->getValue());
    mail( $eMailAdr, $subject, $text);
    $form->removeChild($submitButton); // Tag bort sänd-knappen.
    $fsElev->removeChild($kommentar); // Tag bort kommentartext.
    $form->toggleFrozen(true); // Frys formuläret inför ny visning.
}

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med en (<em>*</em>).'
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

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


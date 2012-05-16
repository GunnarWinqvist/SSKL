<?php

/**
 * Edit Album (edit_album).
 *
 * Presents a form with QuickForm2 for adding a new album or editing an old in the
 * gallery.
 * There are buttons for submit, return and clear.
 * Return sends you back to the page you came from.
 * Clear resets the form without changing.
 * Submit validates the form. If correct values are present the data base is
 * updated accordingly.
 * Input to the page is 'id' or null. If an 'id' is present the information 
 * for an album is updated. If no 'id' is present a new album is added to the DB.
 * 
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();

$redirect = "glry";


/*
 * Process input 'id' if exists.
 * Decide next page 'redirect' depending on if 'id' exists or not.
 */
$idAlbum = isset($_GET['id']) ? $_GET['id'] : NULL;
if ($debugEnable) $debug .= "idAlbum: " . $idAlbum . "<br /> \r\n";


/*
 * Prepare the database.
 */
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableAlbum             = DB_PREFIX . 'Album';
$tablePicture           = DB_PREFIX . 'Picture';


/*
 * If $idAlbum exists the DB will be updated. Get the existing info.
 */
if ($idAlbum) {
    $idAlbum 	= $dbAccess->WashParameter($idAlbum);
    $query      = "SELECT * FROM {$tableAlbum} WHERE idAlbum = {$idAlbum};";
    $result     = $dbAccess->SingleQuery($query); 
    $arrayAlbum = $result->fetch_row();
    $result->close();
} else {
    // Clear all parameters if a new user will be created.
    $arrayAlbum  = array("","","","","","","");
}


/*
 * Create the form with QuickForm2.
 */
require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Point back to the same page for validation.
$formAction = WS_SITELINK . "?p=edit_alb&id=".$idAlbum;

// Create a new form object.
$form = new HTML_QuickForm2('album', 'post', 
    array('action' => $formAction), array('name' => 'album'));

// Data source with default values:
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'name'          => $arrayAlbum[2],
    'description'   => $arrayAlbum[3]
)));

// Album info.
$fsAlbum = $form->addElement('fieldset')->setLabel('Album');

$nameAlbum = $fsAlbum->addElement('text', 'name', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Namn') 
);
$nameAlbum->addRule('required', 'Fyll i namn på albumet');
$nameAlbum->addRule('maxlength', 
    'Namnet är för långt för databasen.', 100);
    
$descriptionAlbum = $fsAlbum->addElement('textarea', 'description', 
    array('style' => 'width: 300px;'), 
    array('label' => 'Beskrivning') 
);


// Buttons
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', 
    array('src' => 'images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="Återställ" href="?p=edit_alb&amp;id='.
        $idAlbum.'" ><img src="images/b_undo.gif" alt="Återställ" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p='.$redirect.'" >
        <img src="images/b_cancel.gif" alt="Avbryt" /></a>');


/*
 * Process the form.
 */

// Remove 'space' first and last in all parameters.
$form->addRecursiveFilter('trim'); 

$mainTextHTML = "";

//If the page is correct filled in the update the DB.
if ($form->validate()) {

    //Wash the input.
    $formValues       = $form->getValue();
    $nameAlbum        = $dbAccess->WashParameter(
        strip_tags($formValues['name']));
    $descriptionAlbum = $dbAccess->WashParameter(
        strip_tags($formValues['description']));
    $presentTime = time();
    
    if ($idAlbum) {
        // If $idAlbum already exists, update the DB.
        $timeEditedAlbum = $presentTime;
        $query = "
            UPDATE {$tableAlbum} SET 
                nameAlbum        = '{$nameAlbum}',
                descriptionAlbum = '{$descriptionAlbum}',
                timeEditedAlbum  = '{$timeEditedAlbum}'
                WHERE idAlbum = '{$idAlbum}';
        ";
        $dbAccess->SingleQuery($query);

    } else {
        // Otherwise a new album is added to the DB.
        $album_idUser     = $_SESSION['idUser'];
        $timeCreatedAlbum = $presentTime;
        $timeEditedAlbum  = $presentTime;
        $query = "
            INSERT INTO {$tableAlbum} (
                album_idUser, 
                nameAlbum, 
                descriptionAlbum, 
                timeCreatedAlbum,
                timeEditedAlbum)
            VALUES (
                '{$album_idUser}', 
                '{$nameAlbum}',
                '{$descriptionAlbum}',
                '{$timeCreatedAlbum}',
                '{$timeEditedAlbum}'
                );
        ";
        $dbAccess->SingleQuery($query);
        $idAlbum = $dbAccess->LastId();
        if ($debugEnable) $debug .= "idAlbum: " . $idAlbum . "<br /> \r\n";
    }


    // Jump to next page if not in debug.
    if ($debugEnable) {
        $form->removeChild($buttons);   // Remove buttons.
        $form->toggleFrozen(true);      // Freeze the form for display.
        $mainTextHTML .= "<a title='Vidare' href='?p={$redirect}'>
            <img src='images/accept.png' alt='Vidare' /></a> <br />\r\n";

    } else {
        $redirect = str_replace("&amp;", "&", $redirect);
        header('Location: ' . WS_SITELINK . "?p={$redirect}");
        exit;
    }
}


/*
 * If the form is incorrect filled it is displayed again with comments.
 */
$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med <em>*</em>'
    ))
    ->setTemplateForId('submit', '<div class="element">{element} or <a href="/">Cancel</a></div>');

$form->render($renderer);
$mainTextHTML .= "<h3>Formulär för att skapa ett nytt album eller editera ett 
    gammalt.</h3><br />\r\n" . $renderer;

/*
 * Add all thumbs in the album, if there are any, with possibility to 
 * chose signature picture.
 */
$query = "
    SELECT idPicture FROM {$tablePicture} 
    WHERE picture_idAlbum = {$idAlbum};
";

if ($idAlbum and $result = $dbAccess->SingleQuery($query)) {
    $mainTextHTML .= "<h3>Välj signaturbild för albumet</h3>";
    while($row = $result->fetch_object()) {
        $mainTextHTML .= "<a href='?p=sign_pict&amp;album=".$idAlbum.
                "&amp;pict=".$row->idPicture."'>
            <img src='".WS_PICTUREARCHIVE.PA_THUMBPREFIX.$row->idPicture.".jpg' />
            </a>";
    }
    $result->close();
}


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page         = new CHTMLPage(); 
$pageTitle    = "Editera album";

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


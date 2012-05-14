<?php

/**
 * Editera kalender (edit_cal)
 *
 * Öppnar filen Kalender.txt och stoppar i en editerbar textruta.
 * När man är nöjd och trycker på submit-knappen kommer man tillbaka till 
 * samma sida och filen sparas igen.
 * Som editor används nicedit.js.
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
$intFilter->UserIsAuthorisedOrDie('fnk');


$calendarFileName = "Kalender.txt";
$calendarPath = TP_DOCUMENTS . $calendarFileName;

$mainTextHTML = "";



if (isset($_POST['submitBtn'])){
    // If the submit button has been pressed, process the form information.

    // Get calendar from the form.
    $calendar = $_POST['calendar'];
    
    //Open the file and write the calendar.
    $fh = fopen($calendarPath, "wt");
    fwrite($fh, $calendar);
    
    fclose($fh);
    
    
    if ($debugEnable) { // Om debug så visa formuläret färdigifyllt.
        $mainTextHTML .= "<a title='Vidare' href='?p=show_usr&amp;id={$idPerson}'
            tabindex='1'><img src='../images/b_enter.gif' alt='Vidare' /></a>
            <br />\r\n";
    } else { // Annars hoppa vidare.
        header('Location: ' . WS_SITELINK . "?p=topics");
        exit;
    }
} else {
    // If no submit it's read the calendar file.
    // Öppna filen kalender.txt och läs in den i $calendar.
    $calendar = file_get_contents($calendarPath);
}


/*
 * Skriv ut sidan.
 */
$page = new CHTMLPage(); 
$pageTitle = "Editera kalender";

// Ladda javascript för NicEdit och skriv ut formuläret.
$mainTextHTML .= <<<HTMLCode
<script src="./src/nicEdit.js" type="text/javascript"></script>
<script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);
</script>
<form action="?p=edit_cal" method="post" >
   <h1>{$pageTitle}</h1>
   <textarea name="calendar" rows='20' cols='50' maxlength='65535'>
    {$calendar}</textarea>
   <input type="submit" name="submitBtn" class="sbtn" value="Spara" />
   <input type="button" value="Cancel" onclick="location='?p=topics'" />
   
</form>

HTMLCode;

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);


?>


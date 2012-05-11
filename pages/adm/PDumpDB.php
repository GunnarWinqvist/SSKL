<?php

/**
 * Dump the database on a file.
 *
 * This page dumps the whole database on a text file. The file can be used as 
 * back-up. The first row is a title row..
 */ 


/*
 * Check if allowed to access.
 * If $nextPage is not set, the page is not reached via the page controller.
 * Then check if the viewer is signed in.
 */
if(!isset($nextPage)) die('Direct access to the page is not allowed.');
$intFilter = new CAccessControl();
$intFilter->UserIsSignedInOrRedirect();
$intFilter->UserIsAuthorisedOrDie('adm'); //Must be adm to access the page.


/*
 * Initiate the DB.
 */
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBostad        = DB_PREFIX . 'Bostad';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableMalsman       = DB_PREFIX . 'Malsman';
$tableElev          = DB_PREFIX . 'Elev';
$tableRelation      = DB_PREFIX . 'Relation';
$tableBlogg         = DB_PREFIX . 'Blogg';
$tableAlbum         = DB_PREFIX . 'Album';
$tablePicture       = DB_PREFIX . 'Picture';

$dumpFileName       = "DB_dump.txt";
$delimiter          = "¤";
$substitution       = "*";

/*
 * Open the file and add the title.
 */
$dumpFilePath = TP_ROOT . $dumpFileName;
$fh = fopen($dumpFilePath, "w");

$querys = array( 
    array( "\r\n-*-tableBostad\r\n",     "SELECT * FROM {$tableBostad};"),
    array( "\r\n-*-tablePerson\r\n",     "SELECT * FROM {$tablePerson};"),
    array( "\r\n-*-tableFunktionar\r\n", "SELECT * FROM {$tableFunktionar};"),
    array( "\r\n-*-tableMalsman\r\n",    "SELECT * FROM {$tableMalsman};"),
    array( "\r\n-*-tableElev\r\n",       "SELECT * FROM {$tableElev};"),
    array( "\r\n-*-tableRelation\r\n",   "SELECT * FROM {$tableRelation};"),
    array( "\r\n-*-tableBlogg\r\n",      "SELECT * FROM {$tableBlogg};"),
    array( "\r\n-*-tableAlbum\r\n",      "SELECT * FROM {$tableAlbum};"),
    array( "\r\n-*-tablePicture\r\n",      "SELECT * FROM {$tablePicture};")
    );
    
foreach ($querys as $set) {
    list($header, $query) = $set;
    fwrite($fh, $header);
    if ($result = $dbAccess->SingleQuery($query)) {
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)
                ."<br />\r\n";
            for($i=0; $i<count($row);$i++) {
                // Replace $delimiter with $substitution if $delimiter is in the text.
                $row[$i] = str_replace($delimiter, $substitution, $row[$i]); 
                // Remove all /cr and /nl
                $row[$i] = str_replace("\r", "", $row[$i]);
                $row[$i] = str_replace("\n", "", $row[$i]);
            }
            fwrite($fh, implode($delimiter, $row)."\r\n");
        }
        $result->close();
    }
}

fclose($fh);


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Dumpa databasen på fil";
$fileLink = WS_SITELINK . $dumpFileName;
$mainTextHTML = <<<HTMLCode
<p>Gjorde en lyckad dump av databasen till filen: {$dumpFilePath}.
<p>Vill du ladda ner filen?</p>
<a title='Hämta dumpfil' href='{$fileLink}' >
    <img src='../images/b_enter.gif' alt='Hämta dumpfil' /></a>

HTMLCode;

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PDumpDB.php
// Anropas med 'dump_db' fr�n index.php.
// Sidan dumpar hela databasen i en semikolon separerad fil. F�rsta raden �r rubrikrad.
// Input: 
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // M�ste vara minst administrat�r f�r att n� sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen
//
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBostad        = DB_PREFIX . 'Bostad';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableMalsman       = DB_PREFIX . 'Malsman';
$tableElev          = DB_PREFIX . 'Elev';
$tableRelation      = DB_PREFIX . 'Relation';
$tableBlogg         = DB_PREFIX . 'Blogg';
$delimiter          = "�";
$substitution       = "*";

// �ppna filen och l�gg till rubrikraden.
$dumpFileName = TP_DOCUMENTSPATH . "DB_dump.txt";
$fh = fopen($dumpFileName, "w");

$querys = array( 
    array( "\r\n-*-tableBostad\r\n",     "SELECT * FROM {$tableBostad};"),
    array( "\r\n-*-tablePerson\r\n",     "SELECT * FROM {$tablePerson};"),
    array( "\r\n-*-tableFunktionar\r\n", "SELECT * FROM {$tableFunktionar};"),
    array( "\r\n-*-tableMalsman\r\n",    "SELECT * FROM {$tableMalsman};"),
    array( "\r\n-*-tableElev\r\n",       "SELECT * FROM {$tableElev};"),
    array( "\r\n-*-tableRelation\r\n",   "SELECT * FROM {$tableRelation};"),
    array( "\r\n-*-tableBlogg\r\n",      "SELECT * FROM {$tableBlogg};")
    );
    
foreach ($querys as $set) {
    list($header, $query) = $set;
    fwrite($fh, $header);
    if ($result = $dbAccess->SingleQuery($query)) {
        while($row = $result->fetch_row()) {
            if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
            for($i=0; $i<count($row);$i++) {
                // Ers�tt $delimiter med $substitution om $delimiter finns i texten.
                $row[$i] = str_replace($delimiter, $substitution, $row[$i]); 
            }
            fwrite($fh, implode($delimiter, $row)."\r\n");
        }
        $result->close();
    }
}

fclose($fh);

$documents = WS_SITELINK . "documents/DB_dump.txt";
$mainTextHTML = <<<HTMLCode
<p>Gjorde en lyckad dump av databasen till filen: {$dumpFileName}.
<p>Vill du ladda ner filen?</p>
<a title='H�mta dumpfil' href='{$documents}' ><img src='../images/b_enter.gif' alt='H�mta dumpfil' /></a>

HTMLCode;

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Dumpa databasen";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


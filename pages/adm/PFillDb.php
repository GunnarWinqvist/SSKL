<?php

/**
 * Fill the database from a file. (fill_db)
 *
 * Fill the databased from a back-up file. 
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
$dumpFileName       = "DB_dump.txt";
$delimiter          = "¤";
$maxTextLength      = 65535; // Length of datatyp TEXT in MqSQL.


/*
 * Open the file and add the title.
 */
$dumpFilePath = TP_ROOT . $dumpFileName;
$fh = fopen($dumpFilePath, "rt");
if ($debugEnable) $debug .= "dumpFileName = ".$dumpFilePath." fh=".$fh
    ."<br />\r\n";

$mainTextHTML = "<p>Databasen har från filen ".$dumpFilePath
    ." fyllts med följande information:<p><br />\r\n";

do {
    // Find a header.
    $header = "";
    do {
        $row = fgets($fh);
        if ($debugEnable) $debug .= "row = ".$row."<br /> \n";
        if (preg_match("/-*-/", $row)) $header = trim(trim($row, "-*"));
    } while( !feof($fh) && !$header );
    if ($debugEnable) $debug .= "header = ".$header."<br /> \n";

    // Read the file row by row and fill the DB untill there is an empty row or
    // eof.
    $i = 0;
    while (!feof($fh) && $row = trim(fgets($fh, $maxTextLength))) { 
        $segments = explode($delimiter, $row);
        for($j=0; $j<count($segments); $j++) 
            $segments[$j] = $dbAccess->WashParameter($segments[$j]);
        if ($debugEnable) 
            $debug.="segments = ".print_r($segments, TRUE)."<br />\r\n";
    
        // Different querys dependent on the header.
        switch ($header) { 
            case 'tableBostad':
                $query = "
                    INSERT INTO {$tableBostad} (
                        idBostad, 
                        telefonBostad, 
                        adressBostad, 
                        stadsdelBostad, 
                        postnummerBostad, 
                        statBostad)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}', 
                        '{$segments[2]}', 
                        '{$segments[3]}', 
                        '{$segments[4]}', 
                        '{$segments[5]}'
                    );
                ";
            break;

            case 'tablePerson':
                $query = "
                    INSERT INTO {$tablePerson} (
                        idPerson, 
                        accountPerson, 
                        passwordPerson, 
                        behorighetPerson, 
                        fornamnPerson, 
                        efternamnPerson, 
                        ePostPerson, 
                        mobilPerson, 
                        person_idBostad, 
                        senastInloggadPerson)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}', 
                        '{$segments[2]}', 
                        '{$segments[3]}', 
                        '{$segments[4]}', 
                        '{$segments[5]}', 
                        '{$segments[6]}', 
                        '{$segments[7]}', 
                        '{$segments[8]}', 
                        '{$segments[9]}'
                    );
                ";
            break;
            
            case 'tableFunktionar':
                $query = "
                    INSERT INTO {$tableFunktionar} (
                        idFunktion, 
                        funktionar_idPerson, 
                        funktionFunktionar)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}', 
                        '{$segments[2]}'
                    );
                ";
            break;

            case 'tableMalsman':
                $query = "
                    INSERT INTO {$tableMalsman} (
                        malsman_idPerson, 
                        nationalitetMalsman, 
                        personnummerMalsman)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}', 
                        '{$segments[2]}'
                    );
                ";
            break;

            case 'tableElev':
                $query = "
                    INSERT INTO {$tableElev} (
                        elev_idPerson, 
                        personnummerElev, 
                        gruppElev, 
                        nationalitetElev, 
                        arskursElev, 
                        skolaElev, 
                        betaltElev)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}', 
                        '{$segments[2]}', 
                        '{$segments[3]}', 
                        '{$segments[4]}', 
                        '{$segments[5]}', 
                        '{$segments[6]}'
                    );
                ";
            break;

            case 'tableRelation':
                $query = "
                    INSERT INTO {$tableRelation} (
                        relation_idElev, 
                        relation_idMalsman)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}'
                    );
                ";
            break;

            case 'tableBlogg':
                $query = "
                    INSERT INTO {$tableBlogg} (
                        idPost, 
                        post_idPerson, 
                        titelPost, 
                        textPost, 
                        tidPost, 
                        internPost)
                    VALUES (
                        '{$segments[0]}', 
                        '{$segments[1]}', 
                        '{$segments[2]}', 
                        '{$segments[3]}', 
                        '{$segments[4]}', 
                        '{$segments[5]}'
                    );
                ";
            break;
        }
        $dbAccess->SingleQuery($query);
        $i++;
    }
    $mainTextHTML .= "Tabell ".$header.": ".$i." rader<br />\r\n";
} while (!feof($fh));

//Close the back-up file.
fclose($fh);


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Fyll databasen";

require(TP_PAGES.'rightColumn.php');
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>

<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// FillDb.php
// Anropas med 'fill_db' fr�n index.php.
// Fyller p� databasen med lite information.
// Endast anv�ndare som h�r till grupp adm har tillg�ng till sidan.
// Input:
// Output: 
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Kolla beh�righet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();
$intFilter->UserIsAuthorisedOrDie('adm');         // M�ste vara minst adm f�r att n� sidan.


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

// �ppna filen. 
$dumpFileName = TP_ROOTPATH . "DB_dump.txt";
$fh = fopen($dumpFileName, "rt");
if ($debugEnable) $debug .= "dumpFileName = ".$dumpFileName." fh=".$fh."<br /> \n";
$mainTextHTML = "<p>Databasen har fr�n filen ".$dumpFileName." fyllts med f�ljande information:<p><br /> \n";

do {
    // Hitta en rubrikrad.
    $header = "";
    do {
        $row = fgets($fh);
        if ($debugEnable) $debug .= "row = ".$row."<br /> \n";
        if (preg_match("/-*-/", $row)) $header = trim(trim($row, "-*"));
    } while( !feof($fh) && !$header );
    if ($debugEnable) $debug .= "header = ".$header."<br /> \n";

    //Skriv rad f�r rad i databasen tills det kommer en tom rad eller eof.
    $i = 0;
    while (!feof($fh) && $row = trim(fgets($fh))) { 
        $row = explode($delimiter, $row);
        for($i=0; $i<count($row);$i++) $row[$i] = $dbAccess->WashParameter($row[$i]);
        if ($debugEnable) $debug .= "row = ".print_r($row, TRUE)."<br /> \n";
    
        // Olika query beroende p� rubrik.
        switch ($header) { 
            case 'tableBostad':
                $query = <<<Query
INSERT INTO {$tableBostad} (idBostad, telefonBostad, adressBostad, stadsdelBostad, postnummerBostad, 
        statBostad)
VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}', '{$row[3]}', '{$row[4]}', '{$row[5]}');
Query;
            break;
            case 'tablePerson':
                $query = <<<Query
INSERT INTO {$tablePerson} (idPerson, accountPerson, passwordPerson, behorighetPerson, fornamnPerson, 
    efternamnPerson, ePostPerson, mobilPerson, person_idBostad, senastInloggad)
VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}', '{$row[3]}', '{$row[4]}', '{$row[5]}', '{$row[6]}', 
    '{$row[7]}', '{$row[8]}', '{$row[9]}');
Query;
            break;
            case 'tableFunktionar':
                $query = <<<Query
INSERT INTO {$tableFunktionar} (idFunktion, funktionar_idPerson, funktionFunktionar)
VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}');
Query;
            break;
            case 'tableMalsman':
                $query = <<<Query
INSERT INTO {$tableMalsman} (malsman_idPerson, nationalitetMalsman, personnummerMalsman)
VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}');
Query;
            break;
            case 'tableElev':
                $query = <<<Query
INSERT INTO {$tableElev} (elev_idPerson, personnummerElev, gruppElev, nationalitetElev, arskursElev, ordinarieSkola, betaltElev)
VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}', '{$row[3]}', '{$row[4]}', '{$row[5]}', '{$row[6]}');
Query;
            break;
            case 'tableRelation':
                $query = <<<Query
INSERT INTO {$tableRelation} (relation_idElev, relation_idMalsman)
VALUES ('{$row[0]}', '{$row[1]}');
Query;
            break;
            case 'tableBlogg':
                $query = <<<Query
INSERT INTO {$tableBlogg} (idPost, post_idPerson, titelPost, textPost, tidPost, internPost)
VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}', '{$row[3]}', '{$row[4]}', '{$row[5]}');
Query;
            break;
        }
        $dbAccess->SingleQuery($query);
        $i++;
    }
    $mainTextHTML .= "Tabell ".$header.": ".$i." rader<br /> \n";

} while (!feof($fh));

//St�ng filen
fclose($fh);


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Fyll databasen";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>

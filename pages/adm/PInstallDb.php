<?php

/**
 * Installera databasen (install_db)
 *
 * Initierar databasen, skapar alla tabeller och fyller den med n�dv�ndig 
 * startinformation. Endast anv�ndare som h�r till grupp adm har tillg�ng till 
 * sidan.
 * 
 * F�rsta g�ngen man initerar databasen och s�ledes inte kan vara inloggad 
 * s�tter man kommentarsstreck (//) framf�r inloggningskraven nedan. Adressera 
 * sedan sidan direkt med svenskaskolankualalumpur.com/?p=install_db.
 * Vid f�rsta initieringen av databasen m�ste �ven en uranv�ndare initieras. 
 * Det g�rs genom att ta  bort kommentarsstrecken vid rad 136 och 137 nedan. 
 * Efter det kan man logga in som Admin (password admin). �ndra l�senordet 
 * omedelbart efter inloggning och gl�m inte att spara filen igen n�r du har 
 * �terst�llt kommentarmarkeringarna enligt ovan.
 * 
 * Om du g�r �ndringar i databasstrukturen s� gl�m inte att motsvarande �ndringar 
 * ocks� m�ste g�ras i PFillDb.php.
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
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';
$tableBlogg             = DB_PREFIX . 'Blogg';
$viewMalsman            = DB_PREFIX . 'ListaMalsman';

// $totalStatements must be edited manually. Count the statements in the
// query below and enter the number here. Only used for debug help.
$totalStatements = 17;
$query = <<<QUERY

-- Tag bort tabellerna om de redan finns.
DROP VIEW  IF EXISTS {$viewMalsman};
DROP TABLE IF EXISTS {$tableBlogg};
DROP TABLE IF EXISTS {$tableRelation};
DROP TABLE IF EXISTS {$tableMalsman};
DROP TABLE IF EXISTS {$tableElev};
DROP TABLE IF EXISTS {$tableFunktionar};
DROP TABLE IF EXISTS {$tablePerson};
DROP TABLE IF EXISTS {$tableBostad};


-- Tabell f�r bostad.
CREATE TABLE {$tableBostad} (
  idBostad INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  telefonBostad CHAR(20),
  adressBostad CHAR(100),
  stadsdelBostad CHAR(20),
  postnummerBostad CHAR(10),
  statBostad CHAR(20)
);

-- Tabell f�r personer.
CREATE TABLE {$tablePerson} (
  idPerson INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  accountPerson CHAR(20) NOT NULL UNIQUE,
  passwordPerson CHAR(32) NOT NULL,
  behorighetPerson CHAR(3) NOT NULL,
  fornamnPerson CHAR(50),
  efternamnPerson CHAR(50),
  ePostPerson CHAR(50),
  mobilPerson CHAR(20),
  person_idBostad INT,
  FOREIGN KEY (person_idBostad) REFERENCES {$tableBostad}(idBostad),
  senastInloggadPerson INT DEFAULT '0'
);

-- Tabell f�r funktion�r.
CREATE TABLE {$tableFunktionar} (
  idFunktion INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  funktionar_idPerson INT NOT NULL,
  FOREIGN KEY (funktionar_idPerson) REFERENCES {$tablePerson}(idPerson),  
  funktionFunktionar CHAR(50)
);

-- Tabell f�r m�lsman.
CREATE TABLE {$tableMalsman} (
  malsman_idPerson INT NOT NULL PRIMARY KEY,
  FOREIGN KEY (malsman_idPerson) REFERENCES {$tablePerson}(idPerson),  
  nationalitetMalsman CHAR(2),
  personnummerMalsman CHAR(13)
);

-- Tabell f�r elev.
CREATE TABLE {$tableElev} (
  elev_idPerson INT NOT NULL PRIMARY KEY,
  FOREIGN KEY (elev_idPerson) REFERENCES {$tablePerson}(idPerson),  
  personnummerElev CHAR(13),
  gruppElev CHAR(10),
  nationalitetElev CHAR(2),
  arskursElev CHAR(2),
  skolaElev CHAR(50),
  betaltElev CHAR(10)

);

-- Tabell f�r relation mellan elev och m�lsman.
CREATE TABLE {$tableRelation} (
  relation_idElev INT NOT NULL,
  relation_idMalsman INT NOT NULL,
  FOREIGN KEY (relation_idElev) REFERENCES {$tablePerson}(idPerson),
  FOREIGN KEY (relation_idMalsman) REFERENCES {$tablePerson}(idPerson),
  PRIMARY KEY (relation_idElev, relation_idMalsman)
);

-- View f�r att l�ttare visa m�lsm�n i tabell.
CREATE VIEW {$viewMalsman} (idElev, idMalsman, fornamnMalsman, efternamnMalsman, 
    ePostMalsman, mobilMalsman, nationalitetMalsman, personnummerMalsman)
AS SELECT relation_idElev, relation_idMalsman, fornamnPerson, efternamnPerson, 
    ePostPerson, mobilPerson, nationalitetMalsman, personnummerMalsman
FROM (({$tablePerson} JOIN {$tableMalsman} ON idPerson = malsman_idPerson)
JOIN {$tableRelation} ON idPerson = relation_idMalsman
);

-- Tabell f�r poster i bloggen.
CREATE TABLE {$tableBlogg} (
    idPost              INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    post_idPerson       INT NOT NULL,  
    FOREIGN KEY (post_idPerson) REFERENCES {$tablePerson}(idPerson),
    titelPost           CHAR(100),
    textPost            TEXT,
    tidPost             INT DEFAULT '0',
    internPost          BOOLEAN
);

-- L�gg till administrat�r f�r att kunna administrera databasen f�rsta 
-- g�ngen den installeras.
-- F�rsta g�ngen m�ste kommentarsstrecken p� de tv� raderna som b�rjar med 
-- INSERT och VALUES nedan tas bort.
-- Password m�ste �ndras direkt f�r att ingen ska kunna kapa databasen.
-- INSERT INTO {$tablePerson} (accountPerson, passwordPerson, behorighetPerson)
-- VALUES ('admin', md5('admin'), 'adm');


QUERY;

// Enter into the DB with a multy query.
$statements = $dbAccess->MultiQueryNoResultSet($query);
if ($debugEnable) $debug.=$statements." statements of ".$totalStatements.
    " was executed.<br />\r\n"; 


/*
 * Define everything that shall be on the page, generate the left column
 * and then display the page.
 */
$page = new CHTMLPage(); 
$pageTitle = "Installera databas";

$mainTextHTML = <<<HTMLCode
<p>Databasen har initierats med f�ljande query:</p>
<code>{$query}</code>
<p>{$statements} statements av {$totalStatements} k�rdes.</p>

HTMLCode;

require(TP_PAGES.'rightColumn.php'); 
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


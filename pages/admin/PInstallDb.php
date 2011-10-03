<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// InstallDb.php
// Anropas med 'install_db' från index.php.
// Initierar databasen, skapar alla tabeller och fyller den med nödvändig startinformation.
// Endast användare som hör till grupp adm har tillgång till sidan.
//
// Första gången man initerar databasen och således inte kan vara inloggad sätter man 
// kommentarsstreck (//) framför inloggningskraven nedan. Adressera sedan sidan direkt med 
// svenskaskolankualalumpur.com/?p=install_db.
// Vid första initieringen av databasen måste även en uranvändare initieras. Det görs genom att ta 
// bort kommentarsstrecken vid rad 136 och 137 nedan. 
// Efter det kan man logga in som Admin (password admin). Ändra lösenordet omedelbart efter inloggning 
// och glöm inte att spara filen igen när du har återställt kommentarmarkeringarna enligt ovan.
// 
// Om du gör ändringar i databasstrukturen så glöm inte att motsvarande ändringar också måste göras 
// i PFillDb.php.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn(); //Kommentera bort med // första databasinitieringen.
$intFilter->UserIsAuthorisedOrDie('adm');       // Måste vara minst admin för att nå sidan.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered och genomför en SQL query för att skapa tabeller etc i databasen 'forum'.
//
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';
$tableBostad            = DB_PREFIX . 'Bostad';
$tableFunktionar        = DB_PREFIX . 'Funktionar';
$tableElev              = DB_PREFIX . 'Elev';
$tableMalsman           = DB_PREFIX . 'Malsman';
$tableRelation          = DB_PREFIX . 'Relation';
$tableBlogg             = DB_PREFIX . 'Blogg';
$viewMalsman            = DB_PREFIX . 'ListaMalsman';

$totalStatements = 16; //Måste uppdateras manuellt om antalet statements ändras.
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


-- Tabell för bostad.
CREATE TABLE {$tableBostad} (
  idBostad INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  telefonBostad CHAR(20),
  adressBostad CHAR(100),
  stadsdelBostad CHAR(20),
  postnummerBostad CHAR(10),
  statBostad CHAR(20)
);

-- Tabell för personer.
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

-- Tabell för funktionär.
CREATE TABLE {$tableFunktionar} (
  idFunktion INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  funktionar_idPerson INT NOT NULL,
  FOREIGN KEY (funktionar_idPerson) REFERENCES {$tablePerson}(idPerson),  
  funktionFunktionar CHAR(50)
);

-- Tabell för målsman.
CREATE TABLE {$tableMalsman} (
  malsman_idPerson INT NOT NULL PRIMARY KEY,
  FOREIGN KEY (malsman_idPerson) REFERENCES {$tablePerson}(idPerson),  
  nationalitetMalsman CHAR(2),
  personnummerMalsman CHAR(13)
);

-- Tabell för elev.
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

-- Tabell för relation mellan elev och målsman.
CREATE TABLE {$tableRelation} (
  relation_idElev INT NOT NULL,
  relation_idMalsman INT NOT NULL,
  FOREIGN KEY (relation_idElev) REFERENCES {$tablePerson}(idPerson),
  FOREIGN KEY (relation_idMalsman) REFERENCES {$tablePerson}(idPerson),
  PRIMARY KEY (relation_idElev, relation_idMalsman)
);

-- View för att lättare visa målsmän i tabell.
CREATE VIEW {$viewMalsman} (idElev, idMalsman, fornamnMalsman, efternamnMalsman, ePostMalsman, mobilMalsman,
                nationalitetMalsman, personnummerMalsman)
    AS SELECT relation_idElev, relation_idMalsman, fornamnPerson, efternamnPerson, ePostPerson, mobilPerson,
                nationalitetMalsman, personnummerMalsman
    FROM (({$tablePerson} JOIN {$tableMalsman} ON idPerson = malsman_idPerson)
    JOIN {$tableRelation} ON idPerson = relation_idMalsman
);

-- Tabell för poster i bloggen.
CREATE TABLE {$tableBlogg} (
    idPost              INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    post_idPerson       INT NOT NULL,  
    FOREIGN KEY (post_idPerson) REFERENCES {$tablePerson}(idPerson),
    titelPost           CHAR(100),
    textPost            TEXT,
    tidPost             INT DEFAULT '0',
    internPost          BOOLEAN
);

-- Lägg till administratör för att kunna administrera databasen första gången den installeras.
-- Första gången måste kommentarsstrecken på de två raderna som börjar med INSERT och VALUES nedan tas bort.
-- Password måste ändras direkt för att ingen ska kunna kapa databasen.
-- INSERT INTO {$tablePerson} (accountPerson, passwordPerson, behorighetPerson)
-- VALUES ('admin', md5('admin'), 'adm');


QUERY;

// In med alltihop i databasen med en multiquery.
$statements = $dbAccess->MultiQueryNoResultSet($query);
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} kördes.<br /> \n"; 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Installera databas";

$mainTextHTML = <<<HTMLCode
<p>Databasen har initierats med följande query:</p>
<code>{$query}</code>
<p>{$statements} statements av {$totalStatements} kördes.</p>
HTMLCode;

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


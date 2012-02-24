<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditUser.php
// Anropas med 'edit_user' från index.php.
// Sidan presenterar ett formulär i QuickForm2 för alla uppgifter om en användare utom kontouppgifter.
// När alla uppgifter är korrekta så uppdateras databasen.
// Från sidan skickas man till PShowUser.
// Input: 'id'
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla behörighet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// Förbered databasen.
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBostad        = DB_PREFIX . 'Bostad';
$tableFunktionar    = DB_PREFIX . 'Funktionar';
$tableElev          = DB_PREFIX . 'Elev';
$tableMalsman       = DB_PREFIX . 'Malsman';
$tableRelation      = DB_PREFIX . 'Relation';


///////////////////////////////////////////////////////////////////////////////////////////////////
// Tag hand om inparametrar till sidan.

$idPerson = isset($_GET['id']) ? $_GET['id'] : NULL;
$idPerson = $dbAccess->WashParameter($idPerson);
if ($debugEnable) $debug .= "Input: id=" . $idPerson . " Authority = ".$_SESSION['authorityUser']."<br /> \n";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kontrollera om personen har behörighet till sidan, d v s är personen på sidan, målsman till
// personen på sidan eller adm. Om inte avbryt.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;

// Kontrollera om SESSION idUser är målsman till idPerson.
$query = "SELECT * FROM {$tableRelation} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Behörighet till sidan som målsman.
    }
}

if (!$showPage) { // Om sidan inte får visas avbryt och visa felmeddelande.
    $message = "Du kan bara ändra lösenord på dig själv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta den nuvarande informationen om personen ur databasen.

$totalStatements = 5;
$query = <<<QUERY
SELECT * FROM ({$tablePerson} LEFT OUTER JOIN {$tableBostad} ON person_idBostad = idBostad)
   WHERE idPerson = {$idPerson};
SELECT * FROM {$tableFunktionar} WHERE funktionar_idPerson = {$idPerson};
SELECT * FROM {$tableElev}       WHERE elev_idPerson       = {$idPerson};
SELECT * FROM {$tableMalsman}    WHERE malsman_idPerson    = {$idPerson};
SELECT idPerson, fornamnPerson, efternamnPerson
FROM {$tablePerson}	INNER JOIN {$tableRelation}
		ON {$tablePerson}.idPerson = {$tableRelation}.relation_idMalsman
WHERE relation_idElev = {$idPerson};
QUERY;

// Multiquery som returnerar en array med resultatset.
$statements = $dbAccess->MultiQuery($query, $arrayResult); 
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} kördes.<br /> \n"; 

// Som vi stoppar in i en array för respektive tabell och stänger.
$arrayPerson     = $arrayResult[0]->fetch_row(); $arrayResult[0]->close();
$arrayElev       = $arrayResult[2]->fetch_row(); $arrayResult[2]->close();
$arrayMalsman    = $arrayResult[3]->fetch_row(); $arrayResult[3]->close();
if ($debugEnable) $debug .= "Person = ".print_r($arrayPerson, TRUE)."<br /> \n";

// Lista målsmän för en elev i en array.
if (isset($arrayResult[4])) { 
    $i = 0;
    while($row = $arrayResult[4]->fetch_object()) {
        $aMalsmanElev[$i] = array( 'id'=>$row->idPerson, 'fornamn'=>$row->fornamnPerson, 'efternamn'=>$row->efternamnPerson );
        $i++;
    }
    $arrayResult[4]->close();
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formuläret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Alternativ för select, nationalitet.
$nationality = array(
    '--' => '--', 'se' => 'Svensk', 'no' => 'Norsk', 'dk' => 'Dansk',
    'fi' => 'Finsk', 'nn' => 'Annan');

$formAction = WS_SITELINK . "?p=edit_user&id=".$idPerson; // Pekar tillbaka på samma sida igen.
$form = new HTML_QuickForm2('user', 'post', array('action' => $formAction), array('name' => 'user'));

// Sätt defaultvärden för formuläret.
$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
    'fornamn'       => $arrayPerson[4],
    'efternamn'     => $arrayPerson[5],
    'ePost'         => $arrayPerson[6],
    'mobil'         => $arrayPerson[7],
    'telefon'       => $arrayPerson[11],
    'adress'        => $arrayPerson[12],
    'stadsdel'      => $arrayPerson[13],
    'postnummer'    => $arrayPerson[14],
    'stat'          => $arrayPerson[15],
    
    'natMalsman'    => $arrayMalsman[1],
    'personnummer'  => $arrayElev[1],
    'grupp'         => $arrayElev[2],
    'nat'           => $arrayElev[3],
    'grade'         => $arrayElev[4],
    'skola'         => $arrayElev[5],
    'pay'           => $arrayElev[6]
)));


// Personuppgifter
$fsPerson = $form->addElement('fieldset')->setLabel('Personuppgifter');

$fornamnPerson = $fsPerson->addElement(
    'text', 'fornamn', array('style' => 'width: 300px;'), array('label' => 'Förnamn') );
$fornamnPerson->addRule('required', 'Fyll i förnamn');
$fornamnPerson->addRule('maxlength', 'Förnamnet är för långt för databasen.', 50);

$efternamnPerson = $fsPerson->addElement(
    'text', 'efternamn', array('style' => 'width: 300px;'), array('label' => 'Efternamn') );
$efternamnPerson->addRule('required', 'Fyll i efternamn');
$efternamnPerson->addRule('maxlength', 'Efternamnet är för långt för databasen.', 50);

$ePostPerson = $fsPerson->addElement(
    'text', 'ePost', array('style' => 'width: 300px;'), array('label' => 'E-postadress') );
//$ePostPerson->addRule('required', 'Fyll i e-postadress');
$ePostPerson->addRule('regex', 'Det är inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");
$ePostPerson->addRule('maxlength', 'E-postadressen är för lång för databasen.', 50);

$mobilPerson = $fsPerson->addElement(
    'text', 'mobil', array('style' => 'width: 300px;'), array('label' => 'Mobilnummer') );
$mobilPerson->addRule('maxlength', 'Mobilnumret är för långt för databasen.', 20);


// Funktionär
if ($_SESSION['authorityUser'] == 'fnk' or  $_SESSION['authorityUser'] == 'adm') { 
    // Visa bara om man är funktionär eller adm. Annars kan 
    // man sätta sig själv till funk och få tillgång till mer än man ska.
    $fsFunk = $form->addElement('fieldset')->setLabel('Funktionär');
    if (isset($arrayResult[1])) { //Resultat från queryn från början av sidan.
        while($row = $arrayResult[1]->fetch_object()) {
            $fsFunk->addElement('checkbox', 'delFunk', array('value' => $row->idFunktion))
                        ->setContent('<small>Radera funktion</small>')
                        ->setLabel($row->funktionFunktionar);
        }
        $arrayResult[1]->close();
    }
    $funktionFunktionar = $fsFunk->addElement('text', 'addFunk', 
            array('style' => 'width: 300px;'), 
            array('label' => 'Ny funktion <br /><i><small>(sekreterare, lärare, ...)</small></i>') );
    $funktionFunktionar->addRule('maxlength', 'Funktionsnamnet får max vara 50 tecken.', 50);
}


// Målsman
if ($_SESSION['authorityUser'] == 'adm') { 
    $fsMalsman = $form->addElement('fieldset')->setLabel('Målsman');
    if ($arrayMalsman[0]) {
        $fsMalsman->addElement('checkbox', 'delMalsman', array('value' => $arrayMalsman[0]))
                            ->setContent('<small>Radera som målsman</small>')
                            ->setLabel($arrayPerson[4].' är målsman');
    } else {
        $fsMalsman->addElement('checkbox', 'addMalsman', array('value' => '1'))
            ->setLabel('Gör '.$arrayPerson[4].' till målsman');
    }
    $nationalitetMalsman = $fsMalsman->addElement('select', 'natMalsman', null, 
                                array('options' => $nationality, 'label' => 'Nationalitet') );
}

// Elev
if ($_SESSION['authorityUser'] == 'adm') { 
    $fsElev = $form->addElement('fieldset')->setLabel('Elev');

    if ($arrayElev[0]) {
        $fsElev->addElement('checkbox', 'delElev', array('value' => $arrayElev[0]))
                            ->setContent('<small>Radera som elev</small>')
                            ->setLabel($arrayPerson[4].' är elev');
    } else {
        $fsElev->addElement('checkbox', 'addElev', array('value' => '1'))
            ->setLabel('Gör '.$arrayPerson[4].' till elev');
    }

    $fsElev->addElement('static', 'comment')
                   ->setContent('<small><i>Fyll i svenskt personnummer om eleven har det, annars födelsedatum.</i></small>');
    $personnummerElev = $fsElev->addElement(
        'text', 'personnummer', array('style' => 'width: 300px;'), array('label' => 'Personnummer') );
    $personnummerElev->addRule('regex', 'Personnumret måste ha formen ååååmmdd-nnnn. Födelsedatum formen ååååmmdd.', 
        '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(-\d{4})?$/');

    $gruppElev = $fsElev->addElement(
        'text', 'grupp', array('style' => 'width: 300px;'), array('label' => 'Vilken grupp är eleven i') );
    $gruppElev->addRule('maxlength', 'Gruppnamnet är för långt för databasen.', 10);

    $nationalitetElev = $fsElev->addElement(
        'select', 'nat', null, array('options' => $nationality, 'label' => 'Nationalitet') );

    $arskursElev = $fsElev->addElement(
        'text', 'grade', array('style' => 'width: 300px;'), array('label' => 'Årskurs i ordinarie skola') );
    $arskursElev->addRule('maxlength', 'Årskursen kan bara bestå av 2 tecken.', 2);

    $skolaElev = $fsElev->addElement(
        'text', 'skola', array('style' => 'width: 300px;'), array('label' => 'Ordinarie skola') );
    $skolaElev->addRule('maxlength', 'Skolnamnet kan bara bestå av 50 tecken.', 50);

    if (strcmp("fnk", $_SESSION['authorityUser']) > 0 ) {
        $betaltElev = $fsElev->addElement(
            'text', 'pay', array('style' => 'width: 300px;'), array('label' => 'Senast betalt') );
        $betaltElev->addRule('maxlength', 'Senast betalt kan bara bestå av 10 tecken.', 10);
    }

    $fsElev->addElement('static', 'comment')
                   ->setLabel('Målsman för eleven');

    // Lista målsmän för eleven.
    if (isset($aMalsmanElev)) { 
        $i=0;
        foreach($aMalsmanElev as $malsmanElev) {
            $fsElev->addElement('checkbox', 'delMalsmanElev', array('value' => $malsmanElev['id']))
                        ->setContent('<small>Radera malsman</small>')
                        ->setLabel($malsmanElev['fornamn']." ".$malsmanElev['efternamn']);
        }
    }

    // Lista alla möjliga målsmän.
    $query = "SELECT idPerson, fornamnPerson, efternamnPerson FROM {$tablePerson}	INNER JOIN {$tableMalsman}
                ON {$tablePerson}.idPerson = {$tableMalsman}.malsman_idPerson ORDER BY efternamnPerson;";
    $result = $dbAccess->SingleQuery($query);
    while($row = $result->fetch_object()) {
        $malsmanList[$row->idPerson] = $row->fornamnPerson." ".$row->efternamnPerson;
    }
    $result->close();
    $fsElev->addElement(
        'select', 'addMalsmanElev', array('multiple' => 'multiple', 'size' => 3),
        array('options' => $malsmanList, 'label' => 'Välj en eller flera nya målsmän<br /><small>Håll ner ctrl för flera</small>'));
}

// Bostad
$fsBostad = $form->addElement('fieldset')->setLabel('Bostad');

// Samma bostad som första målsman.
if ($_SESSION['authorityUser'] == 'adm') { 
    if (isset($aMalsmanElev)) { 
        $fsBostad->addElement('checkbox', 'sammaBostadSomMalsman', array('value' => $aMalsmanElev[0]['id']))
                    ->setContent($aMalsmanElev[0]['fornamn'].$aMalsmanElev[0]['efternamn'])
                    ->setLabel('Samma bostad som målsman');
    } else {
        $fsBostad->addElement('checkbox', 'sammaBostadSomMalsman', array('value' => 'same'))
                    ->setLabel('Samma bostad som målsman ovan');
    }
}

// Samma bostad som någon annan.
if ($_SESSION['authorityUser'] == 'adm') { 
    $query = "SELECT idPerson, fornamnPerson, efternamnPerson FROM {$tablePerson} ORDER BY efternamnPerson;";
    $result = $dbAccess->SingleQuery($query);
    $bostadLista = array("" => "");
    while($row = $result->fetch_object()) {
        $bostadLista[$row->idPerson] = $row->fornamnPerson." ".$row->efternamnPerson;
    }
    $result->close();
    $fsBostad->addElement(
            'select', 'sammaBostadSomAnnan', null, array('options' => $bostadLista, 'label' => 'Samma bostad som') );
}

// Eller editera bostadsadress
$adressBostad = $fsBostad->addElement(
    'text', 'adress', array('style' => 'width: 300px;'), array('label' => 'Adress') );
$adressBostad->addRule('maxlength', 'Adressen är för lång för databasen. Max 100 tecken.', 100);

$stadsdelBostad = $fsBostad->addElement(
    'text', 'stadsdel', array('style' => 'width: 300px;'), array('label' => 'Stadsdel') );
$stadsdelBostad->addRule('maxlength', 'Stadsdelen är för lång för databasen.', 20);

$postnummerBostad = $fsBostad->addElement(
    'text', 'postnummer', array('style' => 'width: 300px;'), array('label' => 'Postnummer') );
$postnummerBostad->addRule('maxlength', 'Postnumret är för lång för databasen.', 10);

$statBostad = $fsBostad->addElement(
    'text', 'stat', array('style' => 'width: 300px;'), array('label' => 'Stat') );
$statBostad->addRule('maxlength', 'Statsnamnet är för långt för databasen.', 20);

$telefonBostad = $fsBostad->addElement(
    'text', 'telefon', array('style' => 'width: 300px;'), array('label' => 'Telefonnummer bostad') );
$telefonBostad->addRule('maxlength', 'Telefonnumret är för långt för databasen.', 20);


// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="Återställ" href="?p=edit_user&amp;id='.$idPerson.'" ><img src="../images/b_undo.gif" alt="Återställ" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=show_user&amp;id='.$idPerson.'" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');



///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formuläret.

$mainTextHTML = "";

// Ta bort 'space' först och sist på alla värden.
$form->addRecursiveFilter('trim'); 

//Om sidan är riktigt ifylld så uppdatera databasen.
if ($form->validate()) {

    // Hämta all input från formuläret.
    $formValues       = $form->getValue();

    // Uppdatera personuppgifter.
    $fornamnPerson 	  = $dbAccess->WashParameter(strip_tags($formValues['fornamn']));
    $efternamnPerson  = $dbAccess->WashParameter(strip_tags($formValues['efternamn']));
    $epostPerson 	  = $dbAccess->WashParameter(strip_tags($formValues['ePost']));
    $mobilPerson      = $dbAccess->WashParameter(strip_tags($formValues['mobil']));
    $query = <<<QUERY
UPDATE {$tablePerson} SET 
    fornamnPerson   = '{$fornamnPerson}',
    efternamnPerson = '{$efternamnPerson}',
    epostPerson     = '{$epostPerson}',
    mobilPerson     = '{$mobilPerson}'
    WHERE idPerson = '{$idPerson}';
QUERY;
    $dbAccess->SingleQuery($query);

    // Radera funktion för funktionär.
    if (isset($formValues['delFunk'])) {
        $idFunktion = $formValues['delFunk'];
        if ($debugEnable) $debug .= "delFunk=".$idFunktion."<br /> \n";
        $query = "DELETE FROM {$tableFunktionar} WHERE idFunktion = '{$idFunktion}';";
        $dbAccess->SingleQuery($query);
    }
    
    // Lägg till funktion för funktionär.
    if (isset($formValues['addFunk'])) {
        $funktion = $dbAccess->WashParameter(strip_tags($formValues['addFunk']));
        if ($debugEnable) $debug .= "addFunk=".$funktion."<br /> \n";
        $query = <<<QUERY
INSERT INTO {$tableFunktionar} (funktionar_idPerson, funktionFunktionar)
    VALUES ('{$idPerson}', '{$funktion}');
QUERY;
        $dbAccess->SingleQuery($query);
    }

    //Målsman
    if ($_SESSION['authorityUser'] == 'adm') { 
        if (isset($formValues['delMalsman'])) {
            // Radera en målsman ur relationstabellen och målsmantabellen.
            $query = "DELETE FROM {$tableRelation} WHERE relation_idMalsman = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
            $query = "DELETE FROM {$tableMalsman} WHERE malsman_idPerson = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
        } else {
            $nationalitetMalsman = $formValues['natMalsman'];
            if (isset($formValues['addMalsman'])) {
                // Lägg till en målsman.
                $query = "INSERT INTO {$tableMalsman} (malsman_idPerson, nationalitetMalsman) VALUES ('{$idPerson}', '{$nationalitetMalsman}');";
            } else {
                // Eller uppdatera nationaliteten på en målsman.
                // Om personen inte inte är målsman och heller inte ska bli målsman så finns inte personen i tabellen målsman och
                // därför blir queryn bara ignorerad.
                $query = "UPDATE {$tableMalsman} SET nationalitetMalsman = '{$nationalitetMalsman}' WHERE malsman_idPerson = '{$idPerson}';";
            }
            $dbAccess->SingleQuery($query);
        }
    }
    
    // Elev
    if ($_SESSION['authorityUser'] == 'adm') { 
        if (isset($formValues['delElev'])) {
            // Radera en elev ur relationstabellen och elevtabellen.
            $query = "DELETE FROM {$tableRelation} WHERE relation_idElev = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
            $query = "DELETE FROM {$tableElev} WHERE elev_idPerson = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
        } else {
            $personnummerElev = $dbAccess->WashParameter(strip_tags($formValues['personnummer']));
            $gruppElev        = $dbAccess->WashParameter(strip_tags($formValues['grupp']));
            $nationalitetElev = $dbAccess->WashParameter(strip_tags($formValues['nat']));
            $arskursElev      = $dbAccess->WashParameter(strip_tags($formValues['grade']));
            $skolaElev        = $dbAccess->WashParameter(strip_tags($formValues['skola']));
            $betaltElev       = $dbAccess->WashParameter(strip_tags($formValues['pay']));
            if (isset($formValues['addElev'])) {
                // Lägg till en elev.
                $query = "INSERT INTO {$tableElev} (elev_idPerson, personnummerElev, gruppElev, nationalitetElev, 
                            arskursElev, skolaElev, betaltElev)
                            VALUES ('{$idPerson}', '{$personnummerElev}', '{$gruppElev}', '{$nationalitetElev}', 
                            '{$arskursElev}', '{$skolaElev}', '{$betaltElev}');";
            } else {
                // Eller uppdatera en elev.
                // Om personen inte inte är elev och heller inte ska bli elev så finns inte personen i tabellen elev och
                // därför blir queryn bara ignorerad.
                $query = "UPDATE {$tableElev} SET 
                            personnummerElev = '{$personnummerElev}',
                            gruppElev = '{$gruppElev}',
                            nationalitetElev = '{$nationalitetElev}',
                            arskursElev = '{$arskursElev}',
                            skolaElev = '{$skolaElev}',
                            betaltElev = '{$betaltElev}'
                            WHERE elev_idPerson = '{$idPerson}';";
            }
            $dbAccess->SingleQuery($query);
        }
    }
    
    // Radera målsman för elev.
    if (isset($formValues['delMalsmanElev'])) {
        $idMalsman = $formValues['delMalsmanElev'];
        if ($debugEnable) $debug .= "delMalsmanElev=".$idMalsman."<br /> \n";
        $query = "DELETE FROM {$tableRelation} WHERE relation_idMalsman = '{$idMalsman}' AND relation_idElev='{$idPerson}';";
        $dbAccess->SingleQuery($query);
    }
    
    // Lägg till en eller flera målsmän för eleven.
    if (isset($formValues['addMalsmanElev'])) {
        $aNewMalsman = $formValues['addMalsmanElev'];
        foreach ($aNewMalsman as $newMalsman) {
            $query = <<<QUERY
INSERT INTO {$tableRelation} (relation_idElev, relation_idMalsman)
    VALUES ('{$idPerson}', '{$newMalsman}');
QUERY;
            $dbAccess->SingleQuery($query);
        }
        
    }

    // Bostad.
    
    // Undersök om personen ska bo i samma bostad som någon annan.
    $sammaBostadSom = "";
    if (isset($formValues['sammaBostadSomAnnan'])) $sammaBostadSom = $formValues['sammaBostadSomAnnan'];
    if (isset($formValues['sammaBostadSomMalsman'])) {
        if ($formValues['sammaBostadSomMalsman'] == 'same') $sammaBostadSom = $newMalsman;
        else $sammaBostadSom = $formValues['sammaBostadSomMalsman'];
    }
    
    if ($sammaBostadSom) {
        // Samma bostad som någon annan.
        
        // Vilken bostad bor den andre?
        $query = "SELECT person_idBostad FROM {$tablePerson} WHERE idPerson = '{$sammaBostadSom}';";
        $result = $dbAccess->SingleQuery($query);
        $row = $result->fetch_object();
        $idBostad = $row->person_idBostad;
        $result->close();
        
        // Uppdatera till den bostaden.
        $query = "UPDATE {$tablePerson} SET person_idBostad = '{$idBostad}' WHERE idPerson  = '{$idPerson}';";
        $dbAccess->SingleQuery($query);
        
        // Kontrollera om ingen annan bor i den gamla bostaden. I så fall ta bort den.
        $gammalBostadId = $arrayPerson[8];
        $query = "SELECT * FROM {$tablePerson} WHERE person_idBostad = {$gammalBostadId};";
        if (!$dbAccess->SingleQuery($query)) {
            $query = "DELETE FROM {$tableBostad} WHERE idBostad = '{$gammalBostadId}';";
            $dbAccess->SingleQuery($query);
        }
        
    } else {
        // Uppdatera bostad.
        //Tvätta inparametrarna.
        $idBostad         = $arrayPerson[8];
        $telefonBostad    = $dbAccess->WashParameter(strip_tags($formValues['telefon']));
        $adressBostad     = $dbAccess->WashParameter(strip_tags($formValues['adress']));
        $stadsdelBostad   = $dbAccess->WashParameter(strip_tags($formValues['stadsdel']));
        $postnummerBostad = $dbAccess->WashParameter(strip_tags($formValues['postnummer']));
        $statBostad       = $dbAccess->WashParameter(strip_tags($formValues['stat']));

        if ($idBostad) { // Om personen har en bostad knuten till sig så uppdatera den.
            $query = <<<QUERY
UPDATE {$tableBostad} SET 
    telefonBostad    = '{$telefonBostad}',
    adressBostad     = '{$adressBostad}',
    stadsdelBostad   = '{$stadsdelBostad}',
    postnummerBostad = '{$postnummerBostad}',
    statBostad       = '{$statBostad}'
    WHERE idBostad   = '{$idBostad}';
QUERY;
            $dbAccess->SingleQuery($query);
        } else { //Annars läggs en ny bostad in.
            if (!$adressBostad) // Om ingen adress är angiven läggs en temporär adress in för att senare kunna uppdateras.
                $adressBostad = "Temporär adress för ".$fornamnPerson." ".$efternamnPerson;
            $query = <<<QUERY
INSERT INTO {$tableBostad} (telefonBostad, adressBostad, stadsdelBostad, postnummerBostad, statBostad)
    VALUES ('{$telefonBostad}', '{$adressBostad}', '{$stadsdelBostad}', '{$postnummerBostad}', 
        '{$statBostad}');
QUERY;
            $dbAccess->SingleQuery($query);
            // Koppla bostaden till personen.
            $idBostad = $dbAccess->LastId();
            $query = "UPDATE {$tablePerson} SET person_idBostad = '{$idBostad}' WHERE idPerson = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
        }
    }


    if ($debugEnable) { // Om debug så visa formuläret färdigifyllt.
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formuläret inför ny visning.
        $mainTextHTML .= "<a title='Vidare' href='?p=show_user&amp;id={$idPerson}' tabindex='1'><img src='../images/b_enter.gif' alt='Vidare' /></a> <br />\n";
    } else { // Annars hoppa vidare.
        header('Location: ' . WS_SITELINK . "?p=show_user&id={$idPerson}");
        exit;
    }
}

    
///////////////////////////////////////////////////////////////////////////////////////////////////
// Om formuläret inte är riktigt ifyllt så skrivs det ut igen med kommentarer.

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'Följand information saknas eller är felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska fält är markerade med <em>*</em>'
    ))
    ->setTemplateForId('submit', '<div class="element">{element} or <a href="/">Cancel</a></div>')
    /*->setTemplateForClass(
        'HTML_QuickForm2_Element_Input',
        '<div class="element<qf:error> error</qf:error>"><qf:error>{error}</qf:error>' .
        '<label for="{id}" class="qf-label<qf:required> required</qf:required>">{label}</label>' .
        '{element}' .
        '<qf:label_2><div class="qf-label-1">{label_2}</div></qf:label_2></div>' 
    )*/;

$form->render($renderer);


///////////////////////////////////////////////////////////////////////////////////////////////////
// Bygg upp sidan

$page = new CHTMLPage(); 
$pageTitle = "Editera användarinformation";

$mainTextHTML .= "<div class='name'>{$arrayPerson[4]} {$arrayPerson[5]}</div> \n";
//$mainTextHTML .= $renderer->getJavascriptBuilder()->getLibraries(true, true);
$mainTextHTML .= $renderer;

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en högerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


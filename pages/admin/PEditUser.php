<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PEditUser.php
// Anropas med 'edit_user' fr�n index.php.
// Sidan presenterar ett formul�r i QuickForm2 f�r alla uppgifter om en anv�ndare utom kontouppgifter.
// N�r alla uppgifter �r korrekta s� uppdateras databasen.
// Fr�n sidan skickas man till PShowUser.
// Input: 'id'
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
// Kolla beh�righet med mera.

$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRedirectToSignIn();


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen.
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
// Kontrollera om personen har beh�righet till sidan, d v s �r personen p� sidan, m�lsman till
// personen p� sidan eller adm. Om inte avbryt.

$showPage = FALSE;
if ($idPerson == $_SESSION['idUser']) $showPage = TRUE;
if ($_SESSION['authorityUser'] == "adm") $showPage = TRUE;

// Kontrollera om SESSION idUser �r m�lsman till idPerson.
$query = "SELECT * FROM {$tableRelation} WHERE relation_idElev = {$idPerson};";
if ($result = $dbAccess->SingleQuery($query)) {
    while($row = $result->fetch_object()) {
        if ($row->relation_idMalsman == $_SESSION['idUser']) $showPage = TRUE; //Beh�righet till sidan som m�lsman.
    }
}

if (!$showPage) { // Om sidan inte f�r visas avbryt och visa felmeddelande.
    $message = "Du kan bara �ndra l�senord p� dig sj�lv eller ett barn till dig.";
    require(TP_PAGESPATH . 'login/PNoAccess.php');
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// H�mta den nuvarande informationen om personen ur databasen.

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
if ($debugEnable) $debug .= "{$statements} statements av {$totalStatements} k�rdes.<br /> \n"; 

// Som vi stoppar in i en array f�r respektive tabell och st�nger.
$arrayPerson     = $arrayResult[0]->fetch_row(); $arrayResult[0]->close();
$arrayElev       = $arrayResult[2]->fetch_row(); $arrayResult[2]->close();
$arrayMalsman    = $arrayResult[3]->fetch_row(); $arrayResult[3]->close();
if ($debugEnable) $debug .= "Person = ".print_r($arrayPerson, TRUE)."<br /> \n";

// Lista m�lsm�n f�r en elev i en array.
if (isset($arrayResult[4])) { 
    $i = 0;
    while($row = $arrayResult[4]->fetch_object()) {
        $aMalsmanElev[$i] = array( 'id'=>$row->idPerson, 'fornamn'=>$row->fornamnPerson, 'efternamn'=>$row->efternamnPerson );
        $i++;
    }
    $arrayResult[4]->close();
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Generera formul�ret med QuickForm2.

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';

// Alternativ f�r select, nationalitet.
$nationality = array(
    '--' => '--', 'se' => 'Svensk', 'no' => 'Norsk', 'dk' => 'Dansk',
    'fi' => 'Finsk', 'nn' => 'Annan');

$formAction = WS_SITELINK . "?p=edit_user&id=".$idPerson; // Pekar tillbaka p� samma sida igen.
$form = new HTML_QuickForm2('user', 'post', array('action' => $formAction), array('name' => 'user'));

// S�tt defaultv�rden f�r formul�ret.
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
    'text', 'fornamn', array('style' => 'width: 300px;'), array('label' => 'F�rnamn') );
$fornamnPerson->addRule('required', 'Fyll i f�rnamn');
$fornamnPerson->addRule('maxlength', 'F�rnamnet �r f�r l�ngt f�r databasen.', 50);

$efternamnPerson = $fsPerson->addElement(
    'text', 'efternamn', array('style' => 'width: 300px;'), array('label' => 'Efternamn') );
$efternamnPerson->addRule('required', 'Fyll i efternamn');
$efternamnPerson->addRule('maxlength', 'Efternamnet �r f�r l�ngt f�r databasen.', 50);

$ePostPerson = $fsPerson->addElement(
    'text', 'ePost', array('style' => 'width: 300px;'), array('label' => 'E-postadress') );
//$ePostPerson->addRule('required', 'Fyll i e-postadress');
$ePostPerson->addRule('regex', 'Det �r inte en korrekt e-postadress.', 
    "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");
$ePostPerson->addRule('maxlength', 'E-postadressen �r f�r l�ng f�r databasen.', 50);

$mobilPerson = $fsPerson->addElement(
    'text', 'mobil', array('style' => 'width: 300px;'), array('label' => 'Mobilnummer') );
$mobilPerson->addRule('maxlength', 'Mobilnumret �r f�r l�ngt f�r databasen.', 20);


// Funktion�r
if ($_SESSION['authorityUser'] == 'fnk' or  $_SESSION['authorityUser'] == 'adm') { 
    // Visa bara om man �r funktion�r eller adm. Annars kan 
    // man s�tta sig sj�lv till funk och f� tillg�ng till mer �n man ska.
    $fsFunk = $form->addElement('fieldset')->setLabel('Funktion�r');
    if (isset($arrayResult[1])) { //Resultat fr�n queryn fr�n b�rjan av sidan.
        while($row = $arrayResult[1]->fetch_object()) {
            $fsFunk->addElement('checkbox', 'delFunk', array('value' => $row->idFunktion))
                        ->setContent('<small>Radera funktion</small>')
                        ->setLabel($row->funktionFunktionar);
        }
        $arrayResult[1]->close();
    }
    $funktionFunktionar = $fsFunk->addElement('text', 'addFunk', 
            array('style' => 'width: 300px;'), 
            array('label' => 'Ny funktion <br /><i><small>(sekreterare, l�rare, ...)</small></i>') );
    $funktionFunktionar->addRule('maxlength', 'Funktionsnamnet f�r max vara 50 tecken.', 50);
}


// M�lsman
if ($_SESSION['authorityUser'] == 'adm') { 
    $fsMalsman = $form->addElement('fieldset')->setLabel('M�lsman');
    if ($arrayMalsman[0]) {
        $fsMalsman->addElement('checkbox', 'delMalsman', array('value' => $arrayMalsman[0]))
                            ->setContent('<small>Radera som m�lsman</small>')
                            ->setLabel($arrayPerson[4].' �r m�lsman');
    } else {
        $fsMalsman->addElement('checkbox', 'addMalsman', array('value' => '1'))
            ->setLabel('G�r '.$arrayPerson[4].' till m�lsman');
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
                            ->setLabel($arrayPerson[4].' �r elev');
    } else {
        $fsElev->addElement('checkbox', 'addElev', array('value' => '1'))
            ->setLabel('G�r '.$arrayPerson[4].' till elev');
    }

    $fsElev->addElement('static', 'comment')
                   ->setContent('<small><i>Fyll i svenskt personnummer om eleven har det, annars f�delsedatum.</i></small>');
    $personnummerElev = $fsElev->addElement(
        'text', 'personnummer', array('style' => 'width: 300px;'), array('label' => 'Personnummer') );
    $personnummerElev->addRule('regex', 'Personnumret m�ste ha formen ����mmdd-nnnn. F�delsedatum formen ����mmdd.', 
        '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])(-\d{4})?$/');

    $gruppElev = $fsElev->addElement(
        'text', 'grupp', array('style' => 'width: 300px;'), array('label' => 'Vilken grupp �r eleven i') );
    $gruppElev->addRule('maxlength', 'Gruppnamnet �r f�r l�ngt f�r databasen.', 10);

    $nationalitetElev = $fsElev->addElement(
        'select', 'nat', null, array('options' => $nationality, 'label' => 'Nationalitet') );

    $arskursElev = $fsElev->addElement(
        'text', 'grade', array('style' => 'width: 300px;'), array('label' => '�rskurs i ordinarie skola') );
    $arskursElev->addRule('maxlength', '�rskursen kan bara best� av 2 tecken.', 2);

    $skolaElev = $fsElev->addElement(
        'text', 'skola', array('style' => 'width: 300px;'), array('label' => 'Ordinarie skola') );
    $skolaElev->addRule('maxlength', 'Skolnamnet kan bara best� av 50 tecken.', 50);

    if (strcmp("fnk", $_SESSION['authorityUser']) > 0 ) {
        $betaltElev = $fsElev->addElement(
            'text', 'pay', array('style' => 'width: 300px;'), array('label' => 'Senast betalt') );
        $betaltElev->addRule('maxlength', 'Senast betalt kan bara best� av 10 tecken.', 10);
    }

    $fsElev->addElement('static', 'comment')
                   ->setLabel('M�lsman f�r eleven');

    // Lista m�lsm�n f�r eleven.
    if (isset($aMalsmanElev)) { 
        $i=0;
        foreach($aMalsmanElev as $malsmanElev) {
            $fsElev->addElement('checkbox', 'delMalsmanElev', array('value' => $malsmanElev['id']))
                        ->setContent('<small>Radera malsman</small>')
                        ->setLabel($malsmanElev['fornamn']." ".$malsmanElev['efternamn']);
        }
    }

    // Lista alla m�jliga m�lsm�n.
    $query = "SELECT idPerson, fornamnPerson, efternamnPerson FROM {$tablePerson}	INNER JOIN {$tableMalsman}
                ON {$tablePerson}.idPerson = {$tableMalsman}.malsman_idPerson ORDER BY efternamnPerson;";
    $result = $dbAccess->SingleQuery($query);
    while($row = $result->fetch_object()) {
        $malsmanList[$row->idPerson] = $row->fornamnPerson." ".$row->efternamnPerson;
    }
    $result->close();
    $fsElev->addElement(
        'select', 'addMalsmanElev', array('multiple' => 'multiple', 'size' => 3),
        array('options' => $malsmanList, 'label' => 'V�lj en eller flera nya m�lsm�n<br /><small>H�ll ner ctrl f�r flera</small>'));
}

// Bostad
$fsBostad = $form->addElement('fieldset')->setLabel('Bostad');

// Samma bostad som f�rsta m�lsman.
if ($_SESSION['authorityUser'] == 'adm') { 
    if (isset($aMalsmanElev)) { 
        $fsBostad->addElement('checkbox', 'sammaBostadSomMalsman', array('value' => $aMalsmanElev[0]['id']))
                    ->setContent($aMalsmanElev[0]['fornamn'].$aMalsmanElev[0]['efternamn'])
                    ->setLabel('Samma bostad som m�lsman');
    } else {
        $fsBostad->addElement('checkbox', 'sammaBostadSomMalsman', array('value' => 'same'))
                    ->setLabel('Samma bostad som m�lsman ovan');
    }
}

// Samma bostad som n�gon annan.
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
$adressBostad->addRule('maxlength', 'Adressen �r f�r l�ng f�r databasen. Max 100 tecken.', 100);

$stadsdelBostad = $fsBostad->addElement(
    'text', 'stadsdel', array('style' => 'width: 300px;'), array('label' => 'Stadsdel') );
$stadsdelBostad->addRule('maxlength', 'Stadsdelen �r f�r l�ng f�r databasen.', 20);

$postnummerBostad = $fsBostad->addElement(
    'text', 'postnummer', array('style' => 'width: 300px;'), array('label' => 'Postnummer') );
$postnummerBostad->addRule('maxlength', 'Postnumret �r f�r l�ng f�r databasen.', 10);

$statBostad = $fsBostad->addElement(
    'text', 'stat', array('style' => 'width: 300px;'), array('label' => 'Stat') );
$statBostad->addRule('maxlength', 'Statsnamnet �r f�r l�ngt f�r databasen.', 20);

$telefonBostad = $fsBostad->addElement(
    'text', 'telefon', array('style' => 'width: 300px;'), array('label' => 'Telefonnummer bostad') );
$telefonBostad->addRule('maxlength', 'Telefonnumret �r f�r l�ngt f�r databasen.', 20);


// Knappar
$buttons = $form->addGroup('buttons')->setSeparator('&nbsp;');
$buttons->addElement('image', 'submitButton', array('src' => '../images/b_enter.gif', 'title' => 'Spara'));
$buttons->addElement('static', 'resetButton')
    ->setContent('<a title="�terst�ll" href="?p=edit_user&amp;id='.$idPerson.'" ><img src="../images/b_undo.gif" alt="�terst�ll" /></a>');
$buttons->addElement('static', 'cancelButton')
    ->setContent('<a title="Avbryt" href="?p=show_user&amp;id='.$idPerson.'" ><img src="../images/b_cancel.gif" alt="Avbryt" /></a>');



///////////////////////////////////////////////////////////////////////////////////////////////////
// Behandla informationen i formul�ret.

$mainTextHTML = "";

// Ta bort 'space' f�rst och sist p� alla v�rden.
$form->addRecursiveFilter('trim'); 

//Om sidan �r riktigt ifylld s� uppdatera databasen.
if ($form->validate()) {

    // H�mta all input fr�n formul�ret.
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

    // Radera funktion f�r funktion�r.
    if (isset($formValues['delFunk'])) {
        $idFunktion = $formValues['delFunk'];
        if ($debugEnable) $debug .= "delFunk=".$idFunktion."<br /> \n";
        $query = "DELETE FROM {$tableFunktionar} WHERE idFunktion = '{$idFunktion}';";
        $dbAccess->SingleQuery($query);
    }
    
    // L�gg till funktion f�r funktion�r.
    if (isset($formValues['addFunk'])) {
        $funktion = $dbAccess->WashParameter(strip_tags($formValues['addFunk']));
        if ($debugEnable) $debug .= "addFunk=".$funktion."<br /> \n";
        $query = <<<QUERY
INSERT INTO {$tableFunktionar} (funktionar_idPerson, funktionFunktionar)
    VALUES ('{$idPerson}', '{$funktion}');
QUERY;
        $dbAccess->SingleQuery($query);
    }

    //M�lsman
    if ($_SESSION['authorityUser'] == 'adm') { 
        if (isset($formValues['delMalsman'])) {
            // Radera en m�lsman ur relationstabellen och m�lsmantabellen.
            $query = "DELETE FROM {$tableRelation} WHERE relation_idMalsman = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
            $query = "DELETE FROM {$tableMalsman} WHERE malsman_idPerson = '{$idPerson}';";
            $dbAccess->SingleQuery($query);
        } else {
            $nationalitetMalsman = $formValues['natMalsman'];
            if (isset($formValues['addMalsman'])) {
                // L�gg till en m�lsman.
                $query = "INSERT INTO {$tableMalsman} (malsman_idPerson, nationalitetMalsman) VALUES ('{$idPerson}', '{$nationalitetMalsman}');";
            } else {
                // Eller uppdatera nationaliteten p� en m�lsman.
                // Om personen inte inte �r m�lsman och heller inte ska bli m�lsman s� finns inte personen i tabellen m�lsman och
                // d�rf�r blir queryn bara ignorerad.
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
                // L�gg till en elev.
                $query = "INSERT INTO {$tableElev} (elev_idPerson, personnummerElev, gruppElev, nationalitetElev, 
                            arskursElev, skolaElev, betaltElev)
                            VALUES ('{$idPerson}', '{$personnummerElev}', '{$gruppElev}', '{$nationalitetElev}', 
                            '{$arskursElev}', '{$skolaElev}', '{$betaltElev}');";
            } else {
                // Eller uppdatera en elev.
                // Om personen inte inte �r elev och heller inte ska bli elev s� finns inte personen i tabellen elev och
                // d�rf�r blir queryn bara ignorerad.
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
    
    // Radera m�lsman f�r elev.
    if (isset($formValues['delMalsmanElev'])) {
        $idMalsman = $formValues['delMalsmanElev'];
        if ($debugEnable) $debug .= "delMalsmanElev=".$idMalsman."<br /> \n";
        $query = "DELETE FROM {$tableRelation} WHERE relation_idMalsman = '{$idMalsman}' AND relation_idElev='{$idPerson}';";
        $dbAccess->SingleQuery($query);
    }
    
    // L�gg till en eller flera m�lsm�n f�r eleven.
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
    
    // Unders�k om personen ska bo i samma bostad som n�gon annan.
    $sammaBostadSom = "";
    if (isset($formValues['sammaBostadSomAnnan'])) $sammaBostadSom = $formValues['sammaBostadSomAnnan'];
    if (isset($formValues['sammaBostadSomMalsman'])) {
        if ($formValues['sammaBostadSomMalsman'] == 'same') $sammaBostadSom = $newMalsman;
        else $sammaBostadSom = $formValues['sammaBostadSomMalsman'];
    }
    
    if ($sammaBostadSom) {
        // Samma bostad som n�gon annan.
        
        // Vilken bostad bor den andre?
        $query = "SELECT person_idBostad FROM {$tablePerson} WHERE idPerson = '{$sammaBostadSom}';";
        $result = $dbAccess->SingleQuery($query);
        $row = $result->fetch_object();
        $idBostad = $row->person_idBostad;
        $result->close();
        
        // Uppdatera till den bostaden.
        $query = "UPDATE {$tablePerson} SET person_idBostad = '{$idBostad}' WHERE idPerson  = '{$idPerson}';";
        $dbAccess->SingleQuery($query);
        
        // Kontrollera om ingen annan bor i den gamla bostaden. I s� fall ta bort den.
        $gammalBostadId = $arrayPerson[8];
        $query = "SELECT * FROM {$tablePerson} WHERE person_idBostad = {$gammalBostadId};";
        if (!$dbAccess->SingleQuery($query)) {
            $query = "DELETE FROM {$tableBostad} WHERE idBostad = '{$gammalBostadId}';";
            $dbAccess->SingleQuery($query);
        }
        
    } else {
        // Uppdatera bostad.
        //Tv�tta inparametrarna.
        $idBostad         = $arrayPerson[8];
        $telefonBostad    = $dbAccess->WashParameter(strip_tags($formValues['telefon']));
        $adressBostad     = $dbAccess->WashParameter(strip_tags($formValues['adress']));
        $stadsdelBostad   = $dbAccess->WashParameter(strip_tags($formValues['stadsdel']));
        $postnummerBostad = $dbAccess->WashParameter(strip_tags($formValues['postnummer']));
        $statBostad       = $dbAccess->WashParameter(strip_tags($formValues['stat']));

        if ($idBostad) { // Om personen har en bostad knuten till sig s� uppdatera den.
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
        } else { //Annars l�ggs en ny bostad in.
            if (!$adressBostad) // Om ingen adress �r angiven l�ggs en tempor�r adress in f�r att senare kunna uppdateras.
                $adressBostad = "Tempor�r adress f�r ".$fornamnPerson." ".$efternamnPerson;
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


    if ($debugEnable) { // Om debug s� visa formul�ret f�rdigifyllt.
        $form->removeChild($buttons);   // Tag bort knapparna.
        $form->toggleFrozen(true);      // Frys formul�ret inf�r ny visning.
        $mainTextHTML .= "<a title='Vidare' href='?p=show_user&amp;id={$idPerson}' tabindex='1'><img src='../images/b_enter.gif' alt='Vidare' /></a> <br />\n";
    } else { // Annars hoppa vidare.
        header('Location: ' . WS_SITELINK . "?p=show_user&id={$idPerson}");
        exit;
    }
}

    
///////////////////////////////////////////////////////////////////////////////////////////////////
// Om formul�ret inte �r riktigt ifyllt s� skrivs det ut igen med kommentarer.

$renderer = HTML_QuickForm2_Renderer::factory('default')
    ->setOption(array(
        'group_hiddens' => true,
        'group_errors'  => true,
        'errors_prefix' => 'F�ljand information saknas eller �r felaktigt ifylld:',
        'errors_suffix' => '',
        'required_note' => 'Obligatoriska f�lt �r markerade med <em>*</em>'
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
$pageTitle = "Editera anv�ndarinformation";

$mainTextHTML .= "<div class='name'>{$arrayPerson[4]} {$arrayPerson[5]}</div> \n";
//$mainTextHTML .= $renderer->getJavascriptBuilder()->getLibraries(true, true);
$mainTextHTML .= $renderer;

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


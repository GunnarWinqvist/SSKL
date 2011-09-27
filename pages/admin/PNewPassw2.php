<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// PNewPassw2.php
// Anropas med 'new_passw2' fr�n index.php.
// Sidan genererar ett nytt l�senord, lagrar det och skickar det till ePost om adressen finns i 
// registret.
// Input: 'ePost' som POSTs.
// 


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Kolla beh�righet med mera.
//
$intFilter = new CAccessControl();
$intFilter->FrontControllerIsVisitedOrDie();


///////////////////////////////////////////////////////////////////////////////////////////////////
// F�rbered databasen.
//
$dbAccess               = new CdbAccess();
$tablePerson            = DB_PREFIX . 'Person';


///////////////////////////////////////////////////////////////////////////////////////////////////
// H�mta input och tv�tta.
//
$ePost         = isset($_POST['ePost'])         ? $_POST['ePost']          : NULL;
$ePost 		  = $dbAccess->WashParameter($ePost);


///////////////////////////////////////////////////////////////////////////////////////////////////
// Skapa ett slumpl�senord.

$min=5; // minimum length of password
$max=10; // maximum length of password
$pwd=""; // to store generated password

for ( $i=0; $i<rand($min,$max); $i++ ) {
    $num=rand(48,122);
    if(($num > 97 && $num < 122))     $pwd.=chr($num);
    else if(($num > 65 && $num < 90)) $pwd.=chr($num);
    else if(($num >48 && $num < 57))  $pwd.=chr($num);
    else if($num==95)                 $pwd.=chr($num);
    else $i--;
}


// Kolla om epostadressen finns i databasen.
$query = "SELECT idPerson, accountPerson FROM {$tablePerson} WHERE ePostPerson = '{$ePost}';";

if ($ePost AND $result = $dbAccess->SingleQuery($query)) {
    // Adressen finns i registret. Uppdatera och skicka nytt password.
    $row = $result->fetch_object();
    $result->close();
    $query = <<<QUERY
UPDATE {$tablePerson} SET 
    passwordPerson = md5('{$pwd}')
    WHERE idPerson = '{$row->idPerson}';
QUERY;
    $dbAccess->SingleQuery($query);
    $subject = "Nytt l�senord";
    $text = <<<Text
Din anv�ndarinformation till Svenska skolf�reningens hemsida.
Anv�ndarnamn: {$row->accountPerson}
L�senord: {$pwd}

Du kan sj�lv logga in p� sidan och �ndra ditt l�senord.
Text;
    mail( $ePost, $subject, $text);
    $mainTextHTML = "<p>Ett nytt l�senord har nu skickats till den angivna epostadressen.</p>  \n";

} else {
    $mainTextHTML = "<p>Den angivna epostadressen kunde inte hittas i databasen.</p>  \n";
}


///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Bygg upp sidan
//
$page = new CHTMLPage(); 
$pageTitle = "Nytt l�senord 2";

require(TP_PAGESPATH.'rightColumn.php'); // Genererar en h�gerkolumn i $rightColumnHTML
$page->printPage($pageTitle, $mainTextHTML, "", $rightColumnHTML);

?>


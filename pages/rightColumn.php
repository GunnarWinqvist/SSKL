<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// rightColumn.php
// I den h�r filen �r all kod samlad som genererar h�gerkolumnen f�r alla sidor.
// 

$rightColumnHTML = "";

// Om anv�ndaren �r inloggad s� h�lsa v�lkommen och l�gg till knappar.
if (isset($_SESSION['idUser'])) {
    $rightColumnHTML .= <<<HTMLCode
<div class='login'>
<h2>V�lkommen</h2>
<h3>{$_SESSION['nameUser']}</h3>
<div class='clear_button'>
<a class='button' href='?p=logout' onclick="this.blur();"><span>Logga ut</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=show_usr&amp;id={$_SESSION['idUser']}' onclick="this.blur();"><span>Personuppgifter</span></a></div>

HTMLCode;


    // Om anv�ndaren �r minst funktion�r s� l�gg till knappar.
    if ($_SESSION['authorityUser'] == "fnk" OR $_SESSION['authorityUser'] == "adm") {
       $rightColumnHTML .= <<<HTMLCode
<div class='clear_button'>
<a class='button' href='?p=edit_post' onclick="this.blur();"><span>Nytt inl�gg</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=doc' onclick="this.blur();"><span>Dokument</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=lists' onclick="this.blur();"><span>Listor</span></a></div>

HTMLCode;
    }
    
    // Om anv�ndaren �r administrat�r s� l�gg till knappar.
    if ($_SESSION['authorityUser'] == "adm") {
       $rightColumnHTML .= <<<HTMLCode
<h3>Administrat�r</h3>
<div class='clear_button'>
<a class='button' href='?p=edit_acnt' onclick="this.blur();"><span>L�gg till ny anv�ndare</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=srch_usr' onclick="this.blur();"><span>S�k en person</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=dump_db' onclick="this.blur();"><span>Dumpa databasen p� fil</span></a></div>
<p>F�ljande aktiviteter f�rst�r databasen och g�r inte att backa!</p>
<div class='clear_button'><a class='button' href='?p=inst_db' 
    onclick="this.blur(); 
    return confirm('Vill du installera om databasen? Alla data blir f�rst�rda och kan inte �terskapas.');">
    <span>Ominstallera databasen</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=fill_db' 
    onclick="this.blur(); 
    return confirm('Vill du fylla databasen fr�n fil? Alla gamla data kommer att skrivas �ver.');">
    <span>Fyll databasen fr�n fil</span></a></div>

HTMLCode;
    }
        
// Annars erbjud att logga in.
} else {
    $redirect = $nextPage;
    $rightColumnHTML .= <<<HTMLCode
<div class='login'>
<form name='loginForm' action='?p=login' method='post'>
<input type='hidden' name='redirect' value='{$redirect}' />
<table>
<tr><td><h3>Inloggning</h3></td></tr>
<tr><td>Anv�ndarnamn</td></tr>
<tr><td><input type='text' name='account' size='20' maxlength='20' value='' /></td></tr>
<tr><td>L�senord</td></tr>
<tr><td><input type='password' name='password' size='20' maxlength='32' value='' /></td></tr>
<tr><td><input type='image' title='Logga in' src='../images/b_login.gif' alt='Logga in' />
<a title='Gl�mt?' href='?p=new_pwd'><img src='../images/b_help.gif' alt='Gl�mt?' /></a>
</td></tr>
</table>
</form>

HTMLCode;
}
//<input class='button' type='submit' value="<span>Logga in</span>" />

$rightColumnHTML .= "</div>";


///////////////////////////////////////////////////////////////////////////////////////////////////
// H�mta och skriv ut rubriken f�r de 10 senaste blogginl�ggen.
//

// F�rbered databasen.
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBlogg          = DB_PREFIX . 'Blogg';

$rightColumnHTML .= <<<HTMLCode
<div class='news'>
<h3>Senaste nytt</h3>
HTMLCode;

$onlyPublic = "WHERE internPost = 'FALSE'"; //Om inte inloggad s� visa bara ickeinterna inl�gg.
if (isset($_SESSION['idUser'])) $onlyPublic = ""; //Om inloggad s� visa alla.

$orderBy = "ORDER BY tidPost DESC";
$query = <<<QUERY
SELECT idPost, titelPost
    FROM {$tableBlogg} 
    {$onlyPublic}
    {$orderBy}
    LIMIT 10
QUERY;
$result=$dbAccess->SingleQuery($query);

if ($result) {
    while($row = $result->fetch_row()) {
        if ($debugEnable) $debug .= "Query result: ".print_r($row, TRUE)."<br /> \n";
        list($idPost, $titelPost) = $row;
        $rightColumnHTML .= <<<HTMLCode
<p><a href='?p=topics#news{$idPost}'>{$titelPost}</a></p>
HTMLCode;
    }
    $result->close();
}
$rightColumnHTML .= "</div>";


///////////////////////////////////////////////////////////////////////////////////////////////////
// L�gg till en ruta med antal bes�kare.
//
$hitCounter = $_SESSION["hitCounter"];
$rightColumnHTML .= <<<HTMLCode
<div class='counter'>
Bes�kare 2011: 
<em>{$hitCounter[0]}</em>
<em>{$hitCounter[1]}</em>
<em>{$hitCounter[2]}</em>
<em>{$hitCounter[3]}</em>
<em>{$hitCounter[4]}</em>
</div>
HTMLCode;




?>
<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// rightColumn.php
// I den här filen är all kod samlad som genererar högerkolumnen för alla sidor.
// 

$rightColumnHTML = "";

// Om användaren är inloggad så hälsa välkommen och lägg till knappar.
if (isset($_SESSION['idUser'])) {
    $rightColumnHTML .= <<<HTMLCode
<div class='login'>
<h2>Välkommen</h2>
<h3>{$_SESSION['nameUser']}</h3>
<div class='clear_button'>
<a class='button' href='?p=logout' onclick="this.blur();"><span>Logga ut</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=show_usr&amp;id={$_SESSION['idUser']}' onclick="this.blur();"><span>Personuppgifter</span></a></div>

HTMLCode;


    // Om användaren är minst funktionär så lägg till knappar.
    if ($_SESSION['authorityUser'] == "fnk" OR $_SESSION['authorityUser'] == "adm") {
       $rightColumnHTML .= <<<HTMLCode
<div class='clear_button'>
<a class='button' href='?p=edit_post' onclick="this.blur();"><span>Nytt inlägg</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=doc' onclick="this.blur();"><span>Dokument</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=lists' onclick="this.blur();"><span>Listor</span></a></div>

HTMLCode;
    }
    
    // Om användaren är administratör så lägg till knappar.
    if ($_SESSION['authorityUser'] == "adm") {
       $rightColumnHTML .= <<<HTMLCode
<h3>Administratör</h3>
<div class='clear_button'>
<a class='button' href='?p=edit_acnt' onclick="this.blur();"><span>Lägg till ny användare</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=srch_usr' onclick="this.blur();"><span>Sök en person</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=dump_db' onclick="this.blur();"><span>Dumpa databasen på fil</span></a></div>
<p>Följande aktiviteter förstör databasen och går inte att backa!</p>
<div class='clear_button'><a class='button' href='?p=inst_db' 
    onclick="this.blur(); 
    return confirm('Vill du installera om databasen? Alla data blir förstörda och kan inte återskapas.');">
    <span>Ominstallera databasen</span></a></div>
<div class='clear_button'>
<a class='button' href='?p=fill_db' 
    onclick="this.blur(); 
    return confirm('Vill du fylla databasen från fil? Alla gamla data kommer att skrivas över.');">
    <span>Fyll databasen från fil</span></a></div>

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
<tr><td>Användarnamn</td></tr>
<tr><td><input type='text' name='account' size='20' maxlength='20' value='' /></td></tr>
<tr><td>Lösenord</td></tr>
<tr><td><input type='password' name='password' size='20' maxlength='32' value='' /></td></tr>
<tr><td><input type='image' title='Logga in' src='../images/b_login.gif' alt='Logga in' />
<a title='Glömt?' href='?p=new_pwd'><img src='../images/b_help.gif' alt='Glömt?' /></a>
</td></tr>
</table>
</form>

HTMLCode;
}
//<input class='button' type='submit' value="<span>Logga in</span>" />

$rightColumnHTML .= "</div>";


///////////////////////////////////////////////////////////////////////////////////////////////////
// Hämta och skriv ut rubriken för de 10 senaste blogginläggen.
//

// Förbered databasen.
$dbAccess           = new CdbAccess();
$tablePerson        = DB_PREFIX . 'Person';
$tableBlogg          = DB_PREFIX . 'Blogg';

$rightColumnHTML .= <<<HTMLCode
<div class='news'>
<h3>Senaste nytt</h3>
HTMLCode;

$onlyPublic = "WHERE internPost = 'FALSE'"; //Om inte inloggad så visa bara ickeinterna inlägg.
if (isset($_SESSION['idUser'])) $onlyPublic = ""; //Om inloggad så visa alla.

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
// Lägg till en ruta med antal besökare.
//
$hitCounter = $_SESSION["hitCounter"];
$rightColumnHTML .= <<<HTMLCode
<div class='counter'>
Besökare 2011: 
<em>{$hitCounter[0]}</em>
<em>{$hitCounter[1]}</em>
<em>{$hitCounter[2]}</em>
<em>{$hitCounter[3]}</em>
<em>{$hitCounter[4]}</em>
</div>
HTMLCode;




?>
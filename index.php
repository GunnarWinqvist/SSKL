<?php

/**
 * Frontcontroller. 
 * 
 * All page access is done through this page.
 * The input parameter p states which page that shall be visited.
 */


/**
 * Load all parameters that is common for this web site.
 * If the code is running on a lab environment set $labEnvironment = TRUE.
 * If the code is running on a web server environment set $labEnvironment = FALSE.
 */
$labEnvironment = TRUE;
if ($labEnvironment)
    require_once('config_lab.php');
else
    require_once('config.php');


/**
 * Start a new session.
 */
session_start();


/**
 * Error handling on/off. If in debug mode $debug will start to grov a string
 * throughout execution and will be shown at the bottom of a page. 
 */
$debug = "";
$debugEnable = WS_DEBUG;
if ($debugEnable) error_reporting(E_ALL | E_STRICT);


/**
 * Start a timer to time the generation of this page (excluding config.php).
 */
if(WS_TIMER) {
	$gTimerStart = microtime(TRUE);
}


/**
 * Uppdate hit counter if it is a new visitor.
 */
// Get the old counter value from the file counter.txt
$hitCounter = implode("",file("counter.txt")); 
if(!isset($_SESSION["hitCounter"])) { 
    // If it's the first page in a new session.
    $hitCounter++; 
    $fh = fopen('counter.txt', 'w');
    fwrite($fh, $hitCounter); 
    fclose($fh);
}
$_SESSION["hitCounter"] = str_pad($hitCounter, 5, "0", STR_PAD_LEFT);


/**
 * Enable autoload for classes.
 */
function __autoload($class_name) {
    require_once(TP_SOURCE . $class_name . '.php');
}


/*
 * Input to the page is 'p'. Require the correct page.
 */
$nextPage = isset($_GET['p']) ? $_GET['p'] : 'main';
if (WS_WORK) $nextPage = 'work';
if ($debugEnable) $debug .= "nextPage = " . $nextPage . "<br /> \n";

switch($nextPage) {	

    // Open pages
    case 'main':      require_once(TP_PAGES . 'PMain.php');              break;
    case 'links':     require_once(TP_PAGES . 'PLinks.php');             break;
    case 'map':       require_once(TP_PAGES . 'PMap.php');               break;
    case 'contact':   require_once(TP_PAGES . 'PContact.php');           break;
    case 'appl':      require_once(TP_PAGES . 'PApplication.php');       break;

    // Blog pages
    case 'topics':    require_once(TP_PAGES . 'blog/PTopics.php');       break;
    case 'edit_post': require_once(TP_PAGES . 'blog/PEditPost.php');     break;
    case 'save_post': require_once(TP_PAGES . 'blog/PSavePost.php');     break;
    case 'del_post':  require_once(TP_PAGES . 'blog/PDelPost.php');      break;

    // Gallery pages
    case 'glry':      require_once(TP_PAGES . 'glry/PGallery.php');      break;
    case 'show_alb':  require_once(TP_PAGES . 'glry/PShowAlbum.php');    break;
    case 'edit_alb':  require_once(TP_PAGES . 'glry/PEditAlbum.php');    break;
    case 'show_pict': require_once(TP_PAGES . 'glry/PShowPict.php');     break;
    case 'add_pict':  require_once(TP_PAGES . 'glry/PAddPict.php');      break;

    // Funktionar pages
    case 'lists':     require_once(TP_PAGES . 'funk/PLists.php');        break;
    case 'lists_ex':  require_once(TP_PAGES . 'funk/PListsEx.php');      break;
    case 'doc':       require_once(TP_PAGES . 'funk/PDocs.php');         break;
    case 'doc_upld':  require_once(TP_PAGES . 'funk/PDocUpload.php');    break;

    // Administration pages.
    case 'list_usr':  require_once(TP_PAGES . 'adm/PListUsr.php');       break;
    case 'srch_usr':  require_once(TP_PAGES . 'adm/PSrchUsr.php');       break;
    case 'show_usr':  require_once(TP_PAGES . 'adm/PShowUsr.php');       break;
    case 'edit_usr':  require_once(TP_PAGES . 'adm/PEditUsr.php');       break;
    case 'edit_acnt': require_once(TP_PAGES . 'adm/PEditAcnt.php');      break;
    case 'del_acnt':  require_once(TP_PAGES . 'adm/PDelAcnt.php');       break;
    case 'edit_pwd':  require_once(TP_PAGES . 'adm/PEditPwd.php');       break;
    case 'new_pwd':   require_once(TP_PAGES . 'adm/PNewPwd.php');        break;

    // Handle the database.
    case 'dump_db':   require_once(TP_PAGES . 'adm/PDumpDB.php');        break;
    case 'inst_db':   require_once(TP_PAGES . 'adm/PInstallDb.php');     break;
    case 'fill_db':   require_once(TP_PAGES . 'adm/PFillDb.php');        break;
   
    // Login
    case 'login':     require_once(TP_PAGES . 'login/PLogin.php');       break;
    case 'logout':    require_once(TP_PAGES . 'login/PLogout.php');      break;

    // Work in progres
    case 'work':      require_once(TP_PAGES . 'PWork.php');              break;
    
    default:          require_once(TP_PAGES . 'PMain.php');              break;}

?>
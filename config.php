<?php

/**
 * Configuration file with parameters for this website.
 *
 * Is called every time you open index.php.
 */


/******************************************************************************
 * Helpers
 */

/**
 * Show debug information.
 */
define('WS_DEBUG',          FALSE);

/**
 * Show links to w3c validator tools.
 */
define('WS_VALIDATORS',     FALSE);

/**
 * Show timer for page generation.
 */
define('WS_TIMER', 		    FALSE);

/**
 * Work ongoing with the site.
 */
define('WS_WORK', 		    FALSE);

/**
 * Use hit counter.
 */
define('WS_HITCOUNTER',     TRUE);


/******************************************************************************
 * Website common parameters.
 */

/**
 * Website server type.
 */
define('WS_TYPE', 	'unix');

/**
 * Name for the website.
 */
define('WS_TITLE', 			'Svenska skolan');

/**
 * Default style sheet.
 */
define('WS_STYLESHEET', 	'style/stylesheetBlue.css');

/**
 * Pointer to the icon to be shown on the browser folder tab.
 */
define('WS_FAVICON', 	    'images/favicon.ico');

/**
 * Footer for all pages.
 */
define('WS_FOOTER', "Dispangulär har gjort den här web-platsen. 
    <a href='mailto:webmaster@svenskaskolankualalumpur.com'>Synpunkter?</a>");

/**
 * Character set Windows-1252 = svenska tecken.
 */
define('WS_CHARSET', 	    'windows-1252');

/**
 * Default language.
 */
define('WS_LANGUAGE',       'se');

/**
 * Time zone for the website.
 */
define('WS_TIMEZONE',       'Asia/Kuala_Lumpur');

/**
 * Show header on each page is default or not.
 */
define('WS_SHOWHEADER',     TRUE);

/**
 * Show footer on each page is default or not.
 */
define('WS_SHOWFOOTER',     TRUE);

/**
 * Floating design for columns is default or not.
 */
define('WS_FLOATINGDESIGN', TRUE);

/**
 * Link to site.
 */
define('WS_SITELINK',   'http://svenskaskolankualalumpur.com/');

/**
 * Link to picture archive.
 */
define('WS_PICTUREARCHIVE', 
    'http://svenskaskolankualalumpur.com/picture_archive/');

/**
 * Mail address to the site.
 */
define('WS_SITEMAIL',   'webmaster@svenskaskolankualalumpur.com');

/**
 * Correct mailserver for outgoing mail.
 */
ini_set( "SMTP", "mailout.one.com" );

/**
 * Correct port for outgoing mail.
 */
ini_set( "SMTP_port", "21" );


/******************************************************************************
 * Define the header menu.
 * If changed remember to change in index.php also.
 */

$menuElements = Array (
    'Framsidan'     => 'main',
    'Aktuellt'      => 'topics',
    'Länkar'        => 'links',
    'Karta'         => 'map',
    'Kontakt'       => 'contact',
    'Anmälan'       => 'appl'
);

/**
 * Make the menu elements a global constant.
 */
define('WS_MENU', serialize($menuElements));


/******************************************************************************
 * Database parameters.
 */

/**
 * Host name for the MySQL database.
 */
define('DB_HOSTNAME', 'svenskaskolankualalumpur.com.mysql');

/**
 * Name of the database.
 */
define('DB_DATABASE', 'svenskaskolanku');

/**
 * User name for the database.
 */
define('DB_USERNAME', 'svenskaskolanku');

/**
 * Password for the database is stored in a separate file.
 */
require_once('password.php');

/**
 * Prefix to be able to use several DBs on the same DB server.
 */
define('DB_PREFIX', 	'svenskaskolanku_');



/******************************************************************************
 * Path names for the file structure.
 */

switch(WS_TYPE) {
    // Set path delimiter depending of server type.
    case 'unix':    $pd = "/";    break;
    case 'windows': $pd = "\\";   break;
}

/**
 * Root path
 */
define('TP_ROOT', 	    dirname(__FILE__) . $pd);

/**
 * Source path
 */
define('TP_SOURCE', 	dirname(__FILE__) . $pd.'src'.$pd);

/**
 * Pages path
 */
define('TP_PAGES', 	    dirname(__FILE__) . $pd.'pages'.$pd);

/**
 * Images path
 */
define('TP_IMAGES',     dirname(__FILE__) . $pd.'images'.$pd);

/**
 * Documents path
 */
define('TP_DOCUMENTS',  dirname(__FILE__) . $pd.'documents'.$pd);

/**
 * Style sheet path
 */
define('TP_STYLE',      dirname(__FILE__) . $pd.'style'.$pd);

/**
 * Picture archive path
 */
define('TP_PICTURES',   dirname(__FILE__) . $pd.'picture_archive'.$pd);

/**
 * Path to the PEAR library if locally installed.
 */
set_include_path(dirname(__FILE__).'/pear/PEAR/');


/******************************************************************************
 * Picture album constants.
 */

/**
 * Maximum upload size in megabytes.
 */
define('PA_MAXUPLOADSIZE', 			'10');

/**
 * Width in pixels of the picture.
 */
define('PA_NORMALWIDTH', 			'480');

/**
 * Height in pixels of the picture.
 */
define('PA_NORMALHEIGHT', 			'360');

/**
 * Width in pixels of the thumb.
 */
define('PA_THUMBWIDTH', 			'96');

/**
 * Height in pixels of the thumb.
 */
define('PA_THUMBHEIGHT', 			'72');

/**
 * Picture quality.
 */
define('PA_IMAGEQUALITYNORMAL', 	'3'); //1:Poor ... 5:Very good

/**
 * Thumb quality
 */
define('PA_IMAGEQUALITYTHUMB', 		'3'); //1:Poor ... 5:Very good

/**
 * Prefix to use for pictures.
 */
define('PA_NORMALPREFIX', 			'pict_');

/**
 * Prefix to use for thumbs.
 */
define('PA_THUMBPREFIX', 			'thumb_');


?>
<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// config.php
// Konfigurationsfil med parametrar som gäller för hela denna website.
// Anropas varje gång man passerar index.php.
//

///////////////////////////////////////////////////////////////////////////////////////////////////
// Körs koden på målservern?
$host = TRUE;



///////////////////////////////////////////////////////////////////////////////////////////////////
// Databasparametrar
// Fås från webplatsleverantören.
//

if ($host) {
    define('DB_HOSTNAME', 'svenskaskolankualalumpur.com.mysql');
    define('DB_USERNAME', 'svenskaskolanku');
    define('DB_DATABASE', 'svenskaskolanku');
    require_once('password.php');
} else {
    define('DB_HOSTNAME', 'localhost');
    define('DB_USERNAME', 'Gunnar');
    define('DB_PASSWORD', 'passord');
    define('DB_DATABASE', 'svenskaskolan');
}

define('DB_PREFIX', 	'svenskaskolanku_'); //Prefix för att kunna använda flera databasar på en webplats.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Website-gemensamma parametrar.
//
if ($host)
    define('WS_SITELINK',       'http://svenskaskolankualalumpur.com/'); // Link to site.	
else
    define('WS_SITELINK',       'http://localhost/');         // Adressen till webplatsens huvudsida.
define('WS_TITLE', 			'Svenska skolan');            // Namn på webplatsen.
define('WS_STYLESHEET', 	'style/stylesheetBlue.css'); // Vilket stylesheet vill du använda.	
define('WS_FAVICON', 	    'images/favicon.ico');        // Pekar på flikiconen.
define('WS_FOOTER', 		"Dispangulär har gjort den här web-platsen. <a href='mailto:webmaster@svenskaskolankualalumpur.com'>Synpunkter?</a>");
define('WS_CHARSET', 	    'windows-1252');              // Ange charset. windows-1252=svenska tecken
define('WS_LANGUAGE',       'se');                         // Defaultspråk svenska.
define('WS_UPDATED',        '2011 Sept 09');               // Senast uppdaterad.
define('WS_TIMEZONE',       'Asia/Kuala_Lumpur');          // Tidszon för webplatsen.


define('WS_DEBUG',          FALSE);                      // Visa debug-information    
define('WS_VALIDATORS',     FALSE);	                    // Visa länkar till w3c validators tools.
define('WS_TIMER', 		    FALSE);                      // Visa timer för sidgenerering.
define('WS_WORK', 		    FALSE);                      // Arbete med siten pågår.

define('TP_ROOTPATH', 	    dirname(__FILE__) . '/');        // Klasser, funktioner, kod
define('TP_SOURCEPATH', 	dirname(__FILE__) . '/src/');    // Klasser, funktioner, kod
define('TP_PAGESPATH', 	    dirname(__FILE__) . '/pages/');  // Pagecontrollers och moduler
define('TP_IMAGESPATH',     dirname(__FILE__) . '/images/'); // Bilder och grafik.
define('TP_DOCUMENTSPATH',  dirname(__FILE__) . '/documents/'); // Dokument.
if ($host)
    define('TP_PEARPATH',       FALSE);                             // Om PEAR-biblioteket är centralt installerat.
else
    define('TP_PEARPATH',       dirname(__FILE__) . '/pear/PEAR/'); // Om PEAR-biblioteket är lokalt installerat.

///////////////////////////////////////////////////////////////////////////////////////////////////
// Meny-innehåll i array.
// Ändringar måste göras i index.php samtidigt.
//
$menuElements = Array (
    'Framsidan'     => 'main',
    'Nyheter'       => 'news',
    'Länkar'        => 'links',
    'Karta'         => 'map',
    'Kontakt'       => 'contact',
    'Anmälan'       => 'appl',
);
define('WS_MENU', serialize($menuElements)); // Gör om menyelementen till en global konstant.


?>
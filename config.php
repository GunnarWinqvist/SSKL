<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// config.php
// Konfigurationsfil med parametrar som g�ller f�r hela denna website.
// Anropas varje g�ng man passerar index.php.
//

///////////////////////////////////////////////////////////////////////////////////////////////////
// K�rs koden p� m�lservern?
$host = TRUE;



///////////////////////////////////////////////////////////////////////////////////////////////////
// Databasparametrar
// F�s fr�n webplatsleverant�ren.
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

define('DB_PREFIX', 	'svenskaskolanku_'); //Prefix f�r att kunna anv�nda flera databasar p� en webplats.


///////////////////////////////////////////////////////////////////////////////////////////////////
// Website-gemensamma parametrar.
//
if ($host)
    define('WS_SITELINK',       'http://svenskaskolankualalumpur.com/'); // Link to site.	
else
    define('WS_SITELINK',       'http://localhost/');         // Adressen till webplatsens huvudsida.
define('WS_TITLE', 			'Svenska skolan');            // Namn p� webplatsen.
define('WS_STYLESHEET', 	'style/stylesheetBlue.css'); // Vilket stylesheet vill du anv�nda.	
define('WS_FAVICON', 	    'images/favicon.ico');        // Pekar p� flikiconen.
define('WS_FOOTER', 		"Dispangul�r har gjort den h�r web-platsen. <a href='mailto:webmaster@svenskaskolankualalumpur.com'>Synpunkter?</a>");
define('WS_CHARSET', 	    'windows-1252');              // Ange charset. windows-1252=svenska tecken
define('WS_LANGUAGE',       'se');                         // Defaultspr�k svenska.
define('WS_UPDATED',        '2011 Sept 09');               // Senast uppdaterad.
define('WS_TIMEZONE',       'Asia/Kuala_Lumpur');          // Tidszon f�r webplatsen.


define('WS_DEBUG',          FALSE);                      // Visa debug-information    
define('WS_VALIDATORS',     FALSE);	                    // Visa l�nkar till w3c validators tools.
define('WS_TIMER', 		    FALSE);                      // Visa timer f�r sidgenerering.
define('WS_WORK', 		    FALSE);                      // Arbete med siten p�g�r.

define('TP_ROOTPATH', 	    dirname(__FILE__) . '/');        // Klasser, funktioner, kod
define('TP_SOURCEPATH', 	dirname(__FILE__) . '/src/');    // Klasser, funktioner, kod
define('TP_PAGESPATH', 	    dirname(__FILE__) . '/pages/');  // Pagecontrollers och moduler
define('TP_IMAGESPATH',     dirname(__FILE__) . '/images/'); // Bilder och grafik.
define('TP_DOCUMENTSPATH',  dirname(__FILE__) . '/documents/'); // Dokument.
if ($host)
    define('TP_PEARPATH',       FALSE);                             // Om PEAR-biblioteket �r centralt installerat.
else
    define('TP_PEARPATH',       dirname(__FILE__) . '/pear/PEAR/'); // Om PEAR-biblioteket �r lokalt installerat.

///////////////////////////////////////////////////////////////////////////////////////////////////
// Meny-inneh�ll i array.
// �ndringar m�ste g�ras i index.php samtidigt.
//
$menuElements = Array (
    'Framsidan'     => 'main',
    'Nyheter'       => 'news',
    'L�nkar'        => 'links',
    'Karta'         => 'map',
    'Kontakt'       => 'contact',
    'Anm�lan'       => 'appl',
);
define('WS_MENU', serialize($menuElements)); // G�r om menyelementen till en global konstant.


?>
<?php

///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Klassen CHTMLPage innehåller metoder för att skapa och skriva ut HTML-sidor.
//
class CHTMLPage {
    

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Interna variabler.
    //
    private $stylesheet;
   
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Constructor och destructor.
    //
    public function __construct($aStylesheet = WS_STYLESHEET) {
    $this->stylesheet       = $aStylesheet;
    }
    
    public function __destruct() {
    ; 
    }

    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // Publik metod printPage.
    // Skriver ut hela sidan. Det är den här metoden som bestämmer designen av webplatsen 
    // tillsammans med stylesheetet.
    //
    public function printPage($pageTitle, $mainTextHTML, $leftColumnHTML="", $rightColumnHTML="",
                                $HTMLHead="") {
        
        $language	= WS_LANGUAGE;
        $charset	= WS_CHARSET;
        $siteTitle  = WS_TITLE;
        $favicon 	= WS_FAVICON;
        $stylesheet = $this->stylesheet;
        $top        = $this->prepareTop();
        $menu       = $this->prepareMenu();
        $body       = $this->preparePageBody($mainTextHTML, $leftColumnHTML, $rightColumnHTML);
        $footer     = WS_FOOTER;
        $timer      = $this->prepareTimer();
        $w3c        = $this->prepareValidatorTools();
        $debugInfo  = $this->prepareDebugInfo();
    
        echo <<<HTMLCode
<!DOCTYPE html>
<html lang="{$language}">
    <head>
        <meta charset="{$charset}" />
        <title>{$siteTitle}</title>
        <link rel="shortcut icon" href="{$favicon}" />
        <link rel="stylesheet" href="{$stylesheet}" />
        {$HTMLHead}
        <!-- HTML5 support for IE -->
        <!--<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>-->        
    </head>
    <body>
        <div class='page'>
            <div class='top'>{$top}</div>
            <div class='head'>
                <!--<div class='title'><p>{$pageTitle}</p></div>-->
            </div><!--End of div class head-->
            <div class='menu'>{$menu}</div>
            {$body}
            <div class='footer'>
                <p>{$footer}</p>
                <p>{$timer}{$w3c}</p>
            </div><!--End of div class footer-->
        </div><!--End of div class page-->
    </body>
{$debugInfo}
</html>
HTMLCode;
        }


        
    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Genererar toppen för sidan. Kan vara t ex en logo eller inloggningsmeny.
    //
    public function prepareTop() {
        
        $htmlTop = <<<HTMLCode
<img src='../images/huvud.gif' alt='Svenska skolan logga' width='780' height='119' />
HTMLCode;
        return $htmlTop;
    }


    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Genererar menyn som är gemensam för samtliga sidor. 
    //
    public function prepareMenu() {
        
        $menu = unserialize(WS_MENU);
        $htmlMenu = <<<HTMLCode
<ul class='menu'>
    <li class='left'></li>
HTMLCode;
        foreach($menu as $key => $value) {
            $htmlMenu .= <<<HTMLCode
    <li>
    <em></em>
    <a href='?p={$value}' title='{$key}'>{$key}</a> 
    </li>
HTMLCode;
        }
        $htmlMenu .= <<<HTMLCode
    <li>
    <em></em>
    </li>
    <li class='right'></li>
</ul>

HTMLCode;

/*        
        // Om användaren är inloggad så lägg till några knappar.
        if (isset($_SESSION['idUser'])) {
            $htmlMenu .= <<<HTMLCode
<a class='nav'  href='?p=new'>       Ny artikel</a><div class=separator></div>
<a class='nav'  href='?p=admin'>     Administrera</a><div class=separator></div>
<a class='nav'  href='?p=logout'>    Logga ut</a><div class=separator></div>
HTMLCode;
        } else {
            // If user is not logged in, show link to login-page
            $htmlMenu .= <<<HTMLCode
<a class='nav'  href='?p=login'>                Logga in      </a>
HTMLCode;
        }
*/

    return $htmlMenu;    
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Skriver ut en sidkropp med innehållet $bodyContent och/eller två kolumner med innehållet
    // $this->iPageBodyLeft respektive $this->iPageBodyRight om de innehåller något.
    //
    public function preparePageBody($pageBodyMain, $pageBodyLeft, $pageBodyRight) {
        
    $errorMessage = $this->prepareErrorMessage();
    
    $columns  = 0;
    $columns += empty($pageBodyMain)  ? 0 : 1;
    $columns += empty($pageBodyLeft)  ? 0 : 1;
    $columns += empty($pageBodyRight) ? 0 : 1;

    // En sida med tre kolumner.
    if ($columns == 3) {
        $body = <<<HTMLCode
<div class='pageBody threecol'>
	<div class="colmid">
		<div class="colleft">
			<div class="col1">
                {$errorMessage}
                {$pageBodyMain}
			</div>
			<div class="col2">
                {$pageBodyLeft}
			</div>
			<div class="col3">
                {$pageBodyRight}
            </div>
		</div>
	</div>
</div><!--End of div class pageBody threecol--> \n \n
HTMLCode;
    }
    
    // En sida med mitten- och vänsterkolumner.
    if (($columns == 2) && isset($pageBodyLeft)) {
        $body = <<<HTMLCode
<div class='pageBody leftmenu'>
	<div class="colleft">
		<div class="col1">
            {$errorMessage}
            {$pageBodyMain}
        </div>
        <div class="col2">
            {$pageBodyLeft}
        </div>
	</div>
</div><!--End of div class pageBody leftmenu--> \n \n
HTMLCode;
    }

    // En sida med mitten- och högerkolumner.
    if (($columns == 2) && isset($pageBodyRight)) {
        $body = <<<HTMLCode
<div class='pageBody rightmenu'>
	<div class="colleft">
		<div class="col1">
            {$errorMessage}
            {$pageBodyMain}
        </div>
        <div class="col2">
            {$pageBodyRight}
        </div>
	</div>
</div><!--End of div class pageBody rightmenu--> \n \n
HTMLCode;
    }

    // En sida med bara mittenkolumnen.
    if ($columns == 1) {
        $body = <<<HTMLCode
<div class='pageBody fullpage'>
        <div class="col1">
            {$errorMessage}
            {$pageBodyMain}
        </div>
</div><!--End of div class pageBody fullpage--> \n \n
HTMLCode;
    }
    return $body;
    } // Slut på function printPageBody

  

    // ------------------------------------------------------------------------------------
    //
    // Prepare html for the timer
    //
    public function prepareTimer() {
    
        if(WS_TIMER) {
            global $gTimerStart;
            return 'Page generated in ' . round(microtime(TRUE) - $gTimerStart, 5) . ' seconds.';
        }
    }
    
    
    // ------------------------------------------------------------------------------------
    //
    // Prepare html for validator tools
    //
    public function prepareValidatorTools() {

        if(!WS_VALIDATORS) { return ""; }

        // Create link to current page
        $refToThisPage = "http";
        $refToThisPage .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
        $refToThisPage .= "://";
        $serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' : ":{$_SERVER['SERVER_PORT']}";
        $refToThisPage .= $serverPort . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        $linkToCSSValidator      = "<a href='http://jigsaw.w3.org/css-validator/check/referer'>CSS</a>";
        $linkToMarkupValidator   = "<a href='http://validator.w3.org/check/referer'>XHTML</a>";
        $linkToCheckLinks        = "<a href='http://validator.w3.org/checklink?uri={$refToThisPage}'>Links</a>";
        $linkToHTML5Validator    = "<a href='http://html5.validator.nu/?doc={$refToThisPage}'>HTML5</a>";
 
        return "<br />{$linkToCSSValidator} {$linkToMarkupValidator} {$linkToCheckLinks} {$linkToHTML5Validator}";
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // Publik metod printDebug.
    // Skriver ut eventuell debuginformation, $debug.
    //
    public function prepareDebugInfo() {

        global $debug;
        global $debugEnable;

        if ($debugEnable) {
            return <<<HTMLCode
<div class='debug'>
<h2>Debug information</h2>
<p>{$debug}</p>
</div>
HTMLCode;
        } else {
            return "";
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // Publik metod getErrorMessage.
    // Undersöker om det finns ett felmeddelande i sessionen och skickar tillbaka det.
    //
    public function prepareErrorMessage() {
    
    $htmlCode = "";
    if ( isset($_SESSION['errorMessage'])) {
        $htmlCode = <<<HTMLCode
<div class=errorMessage>
{$_SESSION['errorMessage']}
</div> \n
HTMLCode;
        unset($_SESSION['errorMessage']);
        }
        return $htmlCode;
    }

   
} // Slut på class CTemplate.
?>
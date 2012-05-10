<?php

/**
 * Class for creating and displaying HTML pages.
 *
 * 
 */
class CHTMLPage {
    

    private $stylesheet;
   
    
    /**
     * Constructor
     *
     * When an object of CHTMLPage is created you have the oportunity to set
     * the stylesheet to be used.
     */
    public function __construct($aStylesheet = WS_STYLESHEET) {
    $this->stylesheet       = $aStylesheet;
    }
    
    
    /**
     * Destructor
     */
    public function __destruct() {
    ; 
    }

    
    /**
     * Create an HTML page.
     *
     * Creates an HTML page from the input parameters. 
     * It's this method that sets the design of the page together with  
     * the style sheet.
     */
    public function printPage($pageTitle, 
        $mainTextHTML, $leftColumnHTML="", $rightColumnHTML="", $HTMLHead="", 
        $showHeader=WS_SHOWHEADER, $showFooter=WS_SHOWFOOTER, 
        $floatingDesign = WS_FLOATINGDESIGN
    ) {
        
        $language	= WS_LANGUAGE;
        $charset	= WS_CHARSET;
        $siteTitle  = WS_TITLE;
        $favicon 	= WS_FAVICON;
        $stylesheet = $this->stylesheet;
        $footer     = WS_FOOTER;
        $timer      = $this->prepareTimer();
        $w3c        = $this->prepareValidatorTools();
        $debugInfo  = $this->prepareDebugInfo();
        
        if ($showHeader) {
            $top = $this->prepareTop();
        }
        
        if (WS_MENU) {
            $menu = $this->prepareMenu(); 
        } else { 
            $menu = "";
        }
        
        if ($floatingDesign) {
            $body = $this->preparePageBodyFloating(
                        $mainTextHTML, $leftColumnHTML, $rightColumnHTML
                    );
        } else {
            $body = $this->preparePageBodyFixed(
                        $mainTextHTML, $leftColumnHTML, $rightColumnHTML
                    );
        }
    
        echo <<<HTMLCode
<!DOCTYPE html>
<html lang="{$language}">
    <head>
        <meta charset="{$charset}" />
        <title>{$siteTitle}</title>
        <link rel="shortcut icon" href="{$favicon}" />
        <link rel="stylesheet" href="{$stylesheet}" type="text/css" />
        {$HTMLHead}
        <!-- HTML5 support for IE -->
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    </head>
    <body>
        <div class='page'>

HTMLCode;
        if ($showHeader) {
            echo <<<HTMLCode
            <div class='head'>
                <div class='top'>{$top}</div>
                <div class='title'>{$pageTitle}</div>
                <div class='menu'>{$menu}</div>
            </div><!--End of div class head-->

HTMLCode;
        }
        echo $body;
        if ($showFooter) {
            echo <<<HTMLCode
            <div class='footer'>
                <p>{$footer}</p>
                <p>{$timer}{$w3c}</p>
            </div><!--End of div class footer-->

HTMLCode;
        }
        echo <<<HTMLCode
        </div><!--End of div class page-->
    </body>
{$debugInfo}
</html>

HTMLCode;
    }


        
    /**
     * Create top.
     * 
     * Generate the top of the page. Can be a logo or a menu or whatever you 
     * want in the top.
     */
    public function prepareTop() {
        $htmlTop = <<<HTMLCode
<img src='../images/huvud.gif' alt='Svenska skolan logga' width='780' height='119' />
HTMLCode;
        return $htmlTop;
    }


    
    /**
     * Create menu.
     *
     * Generates the menu in the header that is common for all pages. 
     */
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

        return $htmlMenu;    
    }



    /**
     * Display floating body 
     * 
     * Displays a page with the content $bodyContent and/or one or two columns 
     * with the content $this->iPageBodyLeft resp $this->iPageBodyRight if 
     * there is any content.
     * The page design is fully floating.
     */
    public function preparePageBodyFloating(
        $pageBodyMain, $pageBodyLeft, $pageBodyRight
    ) {
        
        $errorMessage = $this->prepareErrorMessage();
        
        $columns  = 0;
        $columns += empty($pageBodyMain)  ? 0 : 1;
        $columns += empty($pageBodyLeft)  ? 0 : 1;
        $columns += empty($pageBodyRight) ? 0 : 1;

        // Page with three columns.
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
            </div><!--End of div class pageBody threecol-->

HTMLCode;
        }
        
        // Page with middle and left column.
        if (($columns == 2) && $pageBodyLeft) {
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
            </div><!--End of div class pageBody leftmenu-->

HTMLCode;
    }

        // Page with middle and right column.
        if (($columns == 2) && $pageBodyRight) {
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
            </div><!--End of div class pageBody rightmenu-->

HTMLCode;
        }

        // Page with middle column.
        if ($columns == 1) {
            $body = <<<HTMLCode
            <div class='pageBody fullpage'>
                    <div class="col1">
                        {$errorMessage}
                        {$pageBodyMain}
                    </div>
            </div><!--End of div class pageBody fullpage-->

HTMLCode;
        }
        return $body;
    }

  
    /**
     * Display fixed body 
     * 
     * Displays a page with the content $bodyContent and/or one or two columns 
     * with the content $this->iPageBodyLeft resp $this->iPageBodyRight if 
     * there is any content.
     * The page design is fixed.
     */
    public function preparePageBodyFixed(
        $pageBodyMain, $pageBodyLeft, $pageBodyRight
    ) {
        
        $errorMessage = $this->prepareErrorMessage();
        
        $columns  = 0;
        $columns += empty($pageBodyMain)  ? 0 : 1;
        $columns += empty($pageBodyLeft)  ? 0 : 1;
        $columns += empty($pageBodyRight) ? 0 : 1;

        // Page with three columns.
        if ($columns == 3) {
            $body = <<<HTMLCode

HTMLCode;
        }
        
        // Page with middle and left column.
        if (($columns == 2) && $pageBodyLeft) {
            $body = <<<HTMLCode
            <div class='fixedLeftColumn'>
                <div class="sideColumn">
                    {$pageBodyLeft}
                </div>
                <div class="mainColumn">
                    {$errorMessage}
                    {$pageBodyMain}
                </div>
            </div><!--End of div class fixedLeftColumn-->

HTMLCode;
        }

        // Page with middle and right column.
        if (($columns == 2) && $pageBodyRight) {
            $body = <<<HTMLCode
            <div class='fixedRightColumn'>
                <div class="mainColumn">
                    {$errorMessage}
                    {$pageBodyMain}
                </div>
                <div class="sideColumn">
                    {$pageBodyRight}
                </div>
            </div><!--End of div class fixedRightColumn-->

HTMLCode;
        }

        // Page with middle column.
        if ($columns == 1) {
            $body = <<<HTMLCode
            <div class='fixedOnlyMain'>
                <div class="mainColumn">
                    {$errorMessage}
                    {$pageBodyMain}
                </div>
            </div><!--End of div class fixedOnlyMain-->

HTMLCode;
        }
        return $body;
    }


    
    /**
     * Prepare html for the timer.
     *
     */
    public function prepareTimer() {
    
        if(WS_TIMER) {
            global $gTimerStart;
            return 'Page generated in ' .
                round(microtime(TRUE) - $gTimerStart, 5) . ' seconds.';
        }
    }
    
    
    /**
     * Prepare html for validator tools
    */
    public function prepareValidatorTools() {

        if(!WS_VALIDATORS) { return ""; }

        // Create link to current page
        $refToThisPage = "http";
        $refToThisPage .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
        $refToThisPage .= "://";
        $serverPort = ($_SERVER["SERVER_PORT"] == "80") 
            ? '' : ":{$_SERVER['SERVER_PORT']}";
        $refToThisPage .= $serverPort . $_SERVER["SERVER_NAME"] . 
            $_SERVER["REQUEST_URI"];
        $linkToCSSValidator    = 
            "<a href='http://jigsaw.w3.org/css-validator/check/referer'>CSS</a>";
        $linkToMarkupValidator = 
            "<a href='http://validator.w3.org/check/referer'>XHTML</a>";
        $linkToCheckLinks      = 
            "<a href='http://validator.w3.org/checklink?uri={$refToThisPage}'>Links</a>";
        $linkToHTML5Validator  = 
            "<a href='http://html5.validator.nu/?doc={$refToThisPage}'>HTML5</a>";
 
        return "<br />{$linkToCSSValidator} {$linkToMarkupValidator} 
            {$linkToCheckLinks} {$linkToHTML5Validator}";
    }


    /**
     * Print debug information.
     *
     * Prints the debug info $debug in HTML.
     */
    public function prepareDebugInfo() {

        global $debug;
        global $debugEnable;

        if ($debugEnable) {
            return <<<HTMLCode
<div class='debug'>
<code>
<h2>Debug information</h2>
<p>{$debug}</p>
</code>
</div>

HTMLCode;
        } else {
            return "";
        }
    }

    /**
     * Get Error Message.
     * 
     * Checks if there is an error message and sends it back.
     */
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

   
}

?>
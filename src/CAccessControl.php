<?php

/**
 * Class CAccessControl.
 *
 * Used in each pagecontroller to check access, authority, etc.
 *
 */
class CAccessControl {

    /**
     * Constructor
     */
	public function __construct() {
		;
	}


	/**
     * Destructor
     */
	public function __destruct() {
		;
	}


    /**
     * Check if index.php (frontcontroller) is visited, disallow direct 
     * access to pagecontrollers.
     * Does not work since config.php isn't passed and hence this class
     * can't be found.
     */
	public function FrontControllerIsVisitedOrDie() {
		
        global $nextPage;
        global $debugEnable;
        global $debug;
        
        if ($debugEnable) $debug.="FCIVOD visited nextPage = ".
            $nextPage."<br />\r\n";
		if(!isset($nextPage)) {
			die('Direktaccess till sidan är inte tillåten.');
		}
	}


	/**
     * Check if user has signed in or redirect user to $redirect.
     */
	public function UserIsSignedInOrRedirect($redirect="") {
		
        global $debugEnable;
        global $debug;
        
        $accountUser = isset($_SESSION['accountUser']) 
            ? $_SESSION['accountUser'] 
            : NULL;
        if ($debugEnable) $debug.="UISIOR visited accountUser=".$accountUser.
            " redirect=".$redirect."<br />\r\n";
		if(!$accountUser) {
            $message = "Du måste vara inloggad för att få tillgång till den 
                efterfrågade sidan.";
            if ($redirect) {
                $redirect = str_replace("&amp;", "&", $redirect);
                $_SESSION['errorMessage'] = $message;
                header('Location: ' . WS_SITELINK . "?p={$redirect}");
                exit;
            }
            require(TP_PAGES . 'login/PNoAccess.php');
		}
	}


	/**
     * Check if user is authorised.
     */
	public function UserIsAuthorisedOrDie($requiredAuthority) {
		
        global $debugEnable;
        global $debug;

        $authorityUser = isset($_SESSION['authorityUser']) 
            ? $_SESSION['authorityUser'] 
            : NULL;
        if ($debugEnable) $debug .= "UIAOD visited authorityUser=".
            $authorityUser." required=".$requiredAuthority."<br />\r\n";
        if(strcmp($authorityUser, $requiredAuthority) > 0 ){
            $message = "Du har inte rätt behörighet för att få 
                tillgång till den efterfrågade sidan.";
            require(TP_PAGES . 'login/PNoAccess.php');
        }
	}

    
	/**
     * Check if $idUser is $_SESSION['idUser'].
     */
	public function UserIsUserOrDie($idUser) {
		
        global $debugEnable;
        global $debug;

        $sessionId = isset($_SESSION['idUser']) 
            ? $_SESSION['idUser'] 
            : NULL;
        if ($debugEnable) $debug .= "UIUOD visited sessionId=".$sessionId.
            " idUser=".$idUser."<br />\r\n";
        if(!$sessionId == $idUser){
            $message = "Du har inte rätt behörighet för att få tillgång till 
                den här sidan.";
            require(TP_PAGES . 'login/PNoAccess.php');
        }
	}


} // End of Class

?>
<?php
// ===========================================================================================
//
// Class CAccessControl
//
// Used in each pagecontroller to check access, authority.
//
//


class CAccessControl {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//

	// ------------------------------------------------------------------------------------
	//
	// Constructor
	//
	public function __construct() {
		;
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
		;
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if index.php (frontcontroller) is visited, disallow direct access to 
	// pagecontrollers
	//
	public function FrontControllerIsVisitedOrDie() {
		
        global $nextPage;
        
		if(!isset($nextPage)) {
			die('Direktaccess till sidorna är inte tillåten.');
		}
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if user has signed in or redirect user to sign in page
	//
	public function UserIsSignedInOrRedirectToSignIn() {
		
		if(!isset($_SESSION['accountUser'])) {
            $message = "Man måste vara inloggad för att få tillgång till denna sida.";
            require(TP_PAGESPATH . 'login/PNoAccess.php');
		}
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if user is 'admin'. 
	// 
	//
	public function UserIsAuthorisedOrDie($requiredAuthority) {
		
        if(strcmp($_SESSION['authorityUser'], $requiredAuthority) > 0 ){
            $message = "Tyvärr! Du har inte rätt behörighet för att få tillgång till den här sidan.";
            require(TP_PAGESPATH . 'login/PNoAccess.php');
        }

	}


} // End of Class

?>
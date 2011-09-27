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
			die('Direktaccess till sidorna �r inte till�ten.');
		}
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if user has signed in or redirect user to sign in page
	//
	public function UserIsSignedInOrRedirectToSignIn() {
		
		if(!isset($_SESSION['accountUser'])) {
            $message = "Man m�ste vara inloggad f�r att f� tillg�ng till denna sida.";
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
            $message = "Tyv�rr! Du har inte r�tt beh�righet f�r att f� tillg�ng till den h�r sidan.";
            require(TP_PAGESPATH . 'login/PNoAccess.php');
        }

	}


} // End of Class

?>
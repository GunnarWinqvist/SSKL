<?php
// ===========================================================================================
//
// Class CdbAccess
//
// Anv�nds av page controllers f�r access till databasen.
//
//


class CdbAccess {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//
    protected   $aMysqli;
    
    
	// ------------------------------------------------------------------------------------
	//
	// Constructor och �ppna database.
	//
	public function __construct() {

        global      $debug;
    
		$this->aMysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        if ($this->aMysqli->connect_error) {
            $debug .= "Connection to database failed: ".$this->aMysqli->connect_error."<br /> \n";
            echo $debug;
            exit;
        }
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
		$this->aMysqli->close();
	}


	// ------------------------------------------------------------------------------------
	//
	// G�r en Multiquery utan att returnera n�got resultatset.
    // Returnerar antal lyckade statements om det gick bra.
    // Om det inte gick bra skrivs $debug ut och programmet bryts.
	//
	public function MultiQueryNoResultSet($query) {

        global      $debugEnable;
        global      $debug;
        $mysqli = $this->aMysqli;
    
        $statements = 0;
        if ($debugEnable) $debug .= "Query: " . $query . "<br /> \n";
        if ($mysqli->multi_query($query)) {
            if ($debugEnable) $debug .="Multiquery performed.<br /> \n";
            do {
                // Ta hand om resultatet.
                $result = $mysqli->store_result();
                $statements++;
                if ($debugEnable) $debug .= "Query " . $statements . " returns " .
                    $mysqli->info . $mysqli->error . "<br /> \n";
                // F�ljande man�ver g�r jag f�r att bli av med varningen vid next_result n�r det inte 
                // finns n�got mer resultat att h�mta.
                $loop = FALSE;
                if ($mysqli->more_results()) {
                    $mysqli->next_result();
                    $loop = TRUE;
                }
            } while ($loop);
            return $statements;
        } else {
            echo $debug."Could not query database: ".$mysqli->error."<br /> \n";
            exit;
        }
	}


	// ------------------------------------------------------------------------------------
	//
	// G�r en Multiquery och returnera antalet statements och en array med resultatset om 
    // det gick bra. Om det inte gick bra skrivs $debug ut och programmet bryts.
	//
	public function MultiQuery($query, &$arrayResults) {

        global      $debugEnable;
        global      $debug;
        $mysqli = $this->aMysqli;
    
        $statements = 0;
        if ($debugEnable) $debug .= "Query: " . $query . "<br /> \n";
        if ($mysqli->multi_query($query)) {
            if ($debugEnable) $debug .="Multiquery performed.<br /> \n";
            do {
                // Ta hand om resultatet.
                $result = $mysqli->store_result();
                $arrayResults[$statements] = $result;
                $statements++;
                if ($debugEnable) $debug .= "Query " . $statements . " returns " .
                    $mysqli->info . $mysqli->error . "<br /> \n";
                // F�ljande man�ver g�r jag f�r att bli av med varningen vid next_result n�r det inte 
                // finns n�got mer resultat att h�mta.
                $loop = FALSE;
                if ($mysqli->more_results()) {
                    $mysqli->next_result();
                    $loop = TRUE;
                    // Om du vill g�ra n�got mellan resultatsetten g�r du det h�r.
                }
            } while ($loop);
            return $statements;
        } else {
            echo $debug."Could not query database: ".$mysqli->error."<br /> \n";
            exit;
        }
	}
    

    // ------------------------------------------------------------------------------------
	//
	// G�r en singelquery.
    // Queryn lyckas men den returnerar inget -> return = FALSE
    // Queryn lyckas och den returnerar ett resultatset med noll rader -> return = 0.
    // Queryn lyckas och den returnerar ett resultatset med minst en rad -> return = resultatset.
    // Queryn lyckas inte s� skrivs $debug ut och programmet bryts.
	//
	public function SingleQuery($query) {

        global      $debugEnable;
        global      $debug;
        
        if ($debugEnable) $debug .= "Query: " . $query . "<br /> \n";
        if ($result = $this->aMysqli->query($query)) {
            if ($debugEnable) $debug .= "Returnerar: ".$this->aMysqli->info . "<br /> \n"; 
            if ($result === TRUE) return FALSE;
            elseif ($result->num_rows) return $result;
            else return 0;
        } else {
            echo $debug."Could not query database: ".$this->aMysqli->error."<br /> \n";
            exit;
        }
    }

    
    // ------------------------------------------------------------------------------------
	//
	// Tv�tta en inparameter f�r databasen.
	//
	public function WashParameter($parameter) {
    
        return $this->aMysqli->real_escape_string($parameter);
    }

    
    // ------------------------------------------------------------------------------------
	//
	// Returnerar det senaste autogenererade id fr�n databasen.
	//
	public function LastId() {
    
        return $this->aMysqli->insert_id;
    }
    
} // End of Class

?>
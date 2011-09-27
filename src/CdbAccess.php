<?php
// ===========================================================================================
//
// Class CdbAccess
//
// Används av page controllers för access till databasen.
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
	// Constructor och öppna database.
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
	// Gör en Multiquery utan att returnera något resultatset.
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
                // Följande manöver gör jag för att bli av med varningen vid next_result när det inte 
                // finns något mer resultat att hämta.
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
	// Gör en Multiquery och returnera antalet statements och en array med resultatset om 
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
                // Följande manöver gör jag för att bli av med varningen vid next_result när det inte 
                // finns något mer resultat att hämta.
                $loop = FALSE;
                if ($mysqli->more_results()) {
                    $mysqli->next_result();
                    $loop = TRUE;
                    // Om du vill göra något mellan resultatsetten gör du det här.
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
	// Gör en singelquery.
    // Queryn lyckas men den returnerar inget -> return = FALSE
    // Queryn lyckas och den returnerar ett resultatset med noll rader -> return = 0.
    // Queryn lyckas och den returnerar ett resultatset med minst en rad -> return = resultatset.
    // Queryn lyckas inte så skrivs $debug ut och programmet bryts.
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
	// Tvätta en inparameter för databasen.
	//
	public function WashParameter($parameter) {
    
        return $this->aMysqli->real_escape_string($parameter);
    }

    
    // ------------------------------------------------------------------------------------
	//
	// Returnerar det senaste autogenererade id från databasen.
	//
	public function LastId() {
    
        return $this->aMysqli->insert_id;
    }
    
} // End of Class

?>
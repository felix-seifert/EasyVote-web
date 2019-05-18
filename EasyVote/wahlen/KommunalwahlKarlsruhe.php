<?php

require_once('Wahl.php');
require_once('Wahl_Interface.php');

/**
 * Class KommunalwahlKarlsruhe
 *
 * Implementation of the municipal election in Karlsruhe
 */
class KommunalwahlKarlsruhe extends Wahl implements Wahl_Interface
 {
 
	function __construct(){
		$this->xmlFile = 'wahlen/xmlFiles/kommunalwahl_karlsruhe.xml';
		$this->database = 'kommunalwahl_karlsruhe';
	}

    /**
     * Read maximum number of votes from XML file
     *
     * @return int|SimpleXMLElement
     */
	function MaxStimmen_auslesen(){
		$maxvotes = 0;
		if(file_exists($this->xmlFile)){
			$xml = simplexml_load_file($this->xmlFile);
			$maxvotes = $xml->rules->max_vote;
		} else{
			exit("ERROR");
		}
		
		return $maxvotes;
	}

    /**
     * Create database for this election with data from XML file
     */
	function datenbank_erstellen(){

		$connect = mysqli_connect($this->host, $this->username, $this->password) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");
		
		// Create database only if not existent
		if(mysqli_select_db($connect, $this->database) == '0'){

			mysqli_query($connect, "CREATE DATABASE IF NOT EXISTS " . $this->database) or die(mysqli_error());
			mysqli_query($connect, "USE ".$this->database) or die(mysqli_error());

			$sql = "CREATE TABLE IF NOT EXISTS parteien (id INTEGER NOT NULL, partei VARCHAR(20), PRIMARY KEY(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());

			$sql = "CREATE TABLE IF NOT EXISTS mitglieder (id INTEGER NOT NULL, name VARCHAR(40), vorname VARCHAR(40), partei INTEGER(20), PRIMARY KEY(id), FOREIGN KEY (partei)
			   REFERENCES parteien(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());
			
			// Read data from XML file
			if(file_exists($this->xmlFile)){
				$xml = simplexml_load_file($this->xmlFile);
					
				// Read parties
				foreach($xml->parties->party as $partei)
				{
					$sql = "INSERT INTO parteien (id, partei) VALUES (".$partei['id'].", '".$partei."')";
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
					
				// Read candidates
				foreach($xml->candidates->candidate as $kandidat)
				{
					$sql = "INSERT INTO mitglieder (id, name, vorname, partei) VALUES (".$kandidat['id'].", '".$kandidat['name']."', '".$kandidat['prename']."', (SELECT id FROM parteien WHERE partei = '".$kandidat['partei']."'))";
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
			}
			else{
				exit("ERROR: Cannot open XML file");
			}
		}
		
		mysqli_close($connect);
	}
	
	//Liest die Mitglieder einer Partei aus der Datenbank aus und liefert das Ergebnis der Anfrage zurück

    /**
     * Get candidates of given party out of database
     *
     * @param $party_id
     * @return bool|mysqli_result
     */
	function mitglieder_auslesen($party_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT * FROM mitglieder WHERE partei = " . $party_id;
		$response = mysqli_query($connect, $sql) or die(mysqli_error());

		mysqli_close($connect);
		
		return($response);
	}
	
	//Liest die Anzahl der Mitglieder einer Partei aus der Datenbank aus und liefert das Ergebnis zurück

    /**
     * Count candidates of given party
     *
     * @param $party_id
     * @return mixed
     */
	function count_members($party_id) {
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());

		$sql = "SELECT COUNT(*) FROM mitglieder WHERE partei = " . $party_id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		$tmp = mysqli_fetch_row($result);
		$response = $tmp[0];

		mysqli_close($connect);
		
		return($response);
	}

    /**
     * Display candidates of given party
     *
     * @param $party_id
     */
	function mitgliederliste_anzeigen($party_id) {
		$candidates = $this->mitglieder_auslesen($party_id);

		echo $this->display_party_button($party_id);
		
		echo "<div id='Tabelle'>";
		echo "<hr />";

		// Disable selects if a whole party got elected
        $disabled = false;
        $casted_votes = 0;
        if(isset($_SESSION['party_elected']) && (!isset($_SESSION['vote']) || empty($_SESSION['vote']))) {
            if(count($_SESSION['party_elected']) == 1) {
                $disabled = true;
                if(in_array('partei_'.$party_id, $_SESSION['party_elected'])) {
                    $votes_per_person = 1;
                }
                else {
                    $votes_per_person = 0;
                }
            }
        }
		
		// Table of the candidates of the displayed party
		$candidate_row = array();
		while($row = mysqli_fetch_array($candidates)){
			$candidate = "<b>". $row['name'].",</b> ". $row['vorname'];
			$color = '';

			// Change background colour for searched candidates
			if(isset($_SESSION['string_searching'])){
				if($row['id'] == $_SESSION['string_searching']){
					$color = 'bgcolor = #BEDBEE';
				}
			}

			if(!$disabled) {
                // Read selected option from SESSION
                $votes_per_person = 0;
                if(isset($_SESSION['vote']) && !empty($_SESSION['vote'])){
                    if(in_array('member_'.$party_id.'_'.$row['id'].'_1', $_SESSION['vote'])){
                        $votes_per_person = 1;
                    }
                    elseif(in_array('member_'.$party_id.'_'.$row['id'].'_2', $_SESSION['vote'])){
                        $votes_per_person = 2;
                    }
                    elseif(in_array('member_'.$party_id.'_'.$row['id'].'_3', $_SESSION['vote'])){
                        $votes_per_person = 3;
                    }
                }
            }
			else {
			    // If only one party got selected, votes are cast automatically. It is not possible to cast
                // more votes than allowed.
			    $casted_votes++;
			    if($casted_votes > $this->MaxStimmen_auslesen()) {
			        $votes_per_person = 0;
                }
            }

			// Tooltip for disabled selects
            $tooltip = "<span class='tooltip candidate-tooltip'>"
                ."F&uuml;r die direkte Wahl m&uuml;ssen Sie das Kreuz der Liste entfernen."
                ."</span>";

			// One form row per candidate
			$candidate_row[] = "<tr $color><td width = 25px><b>". $row['id'] ."</b></td><td>".$candidate."</td><td width = 50px class='candidate-td'>".
                "<form method='post' action='election.php'>
                    <select name='vote_changed' onchange='this.form.submit()' ".($disabled ? 'disabled' : '').">
                        <option value='member_".$party_id."_".$row['id']."_0' ".($votes_per_person == 0 ? 'selected' : '').">0</option>
                        <option value='member_".$party_id."_".$row['id']."_1' ".($votes_per_person == 1 ? 'selected' : '').">1</option>
                        <option value='member_".$party_id."_".$row['id']."_2' ".($votes_per_person == 2 ? 'selected' : '').">2</option>
                        <option value='member_".$party_id."_".$row['id']."_3' ".($votes_per_person == 3 ? 'selected' : '').">3</option>
                    </select>"
                    .($disabled ? $tooltip : '')
                ."</form></td></tr>";
		}

		// Create two tables (as columns) for all the candidates
		echo "<table style='float:left;' margin-right=20px; width = 375px;>";
		$counter = count($candidate_row);
		$first = round($counter / 2);
		for($i = 0; $i < $first; $i++){
			echo $candidate_row[$i];
		}
		echo "</table>";
		echo "<table width = 375px;>";
		for($i = $first; $i < $counter; $i++){
			echo $candidate_row[$i];
		}
		echo "</table>";
		echo "</div>";
	}

    /**
     * Return HTML code for button to elect party. Button disabled when one party or direct votes are cast.
     *
     * @param $party_id
     * @return string
     */
    function display_party_button($party_id) {
        $party_name = $this->get_partei($party_id);

        // Define correct image for button
        $disabled = false;
        if(isset($_SESSION['party_elected']) && !empty($_SESSION['party_elected'])) {
            if(in_array('partei_'.$party_id, $_SESSION['party_elected'])) {
                $image = "<img src='bilder/ballotChecked50.gif'>";
            }
            else {
                $image = "<img src='bilder/ballotUnchecked50.gif' class='party-button-disabled'>"
                    ."<span class='tooltip'>Sie k&ouml;nnen nur eine Liste komplett w&auml;hlen.</span>";
                $disabled = true;
            }
        }
        elseif (isset($_SESSION['vote']) && !empty($_SESSION['vote'])) {
            $image = "<img src='bilder/ballotUnchecked50.gif' class='party-button-disabled'>"
                ."<span class='tooltip'>W&auml;hlen Sie Kandidierende entweder direkt oder eine gesamte Liste.</span>";
            $disabled = true;
        }
        else {
            $image = "<img src='bilder/ballotUnchecked50.gif'>"
                ."<span class='tooltip'>Klicken Sie hier, um die gesamte Liste zu w&auml;hlen.</span>";
        }

        $response = "<h1>"
            ."<span style='width:8em;float:left;'>".$party_name."</span>"
            ."<form method='post' action='election.php'>"
                ."<div class='party-button-div'>"
                    ."<button type='submit' name='vote_changed' value='partei_".$party_id."' id='$party_id' class='text_button' ".($disabled ? 'disabled' : '').">".$image."</button>"
                ."</div>"
            ."</form></h1>";

        return $response;
    }

    /**
     * Display list of parties on the left
     */
	function parteienliste_laden()
	{
		$parties = $this->parteien_auslesen();
		
		echo "<form method='post' action='election.php'>";
		while($row = mysqli_fetch_array($parties)){
            $party = "partei_".$row['id'];
            $image = '';

            // Every elected party gets related button. Ones one candidate is elected, only relevant parties get another icon.
		    if(isset($_SESSION['party_elected']) && (!isset($_SESSION['vote']) || empty($_SESSION['vote']))) {
		        if(in_array($party, $_SESSION['party_elected'])) {
                    $image= "<img src='bilder/buttonIconPartyVoted.gif' height='15'>";
                }
            }
		    if(isset($_SESSION['vote'])) {
		        foreach($_SESSION['vote'] as $vote) {
		            if(explode("_", $vote)[1] == $row['id']) {
                        $image= "<img src='bilder/buttonIconCandidateVoted.gif' height='15'>";
                        break;
                    }
		            else {
		                $image = '';
                    }
                }
            }
		
			// Each party as button
			if(isset($_POST['partei']) && ($_POST['partei']== $row['id'])){
				echo "<button type='submit' name='partei' value='".$row['id']."' class='button_aktiv'>".$image. " " . $row['partei']."</button>";
			}
			else{ 
				echo "<button type='submit' name='partei' value='".$row['id']."' class='button'>".$image. " " . $row['partei']."</button>";
			}

		}

		echo "</form>";
	}

    /**
     * Get name of candidate id
     *
     * @param $candidate_id
     * @return string
     */
	function get_mitglied($candidate_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT name, vorname FROM mitglieder WHERE id = " . $candidate_id;
		$query = mysqli_query($connect, $sql) or die(mysqli_error());
		
		$result = mysqli_fetch_row($query);
		$candidate = $result[0] . ", ".$result[1];

		mysqli_close($connect);
		
		return($candidate);
	}

    /**
     * Prepare election by setting the SESSION variable 'wahl'
     */
	function wahl_abgeben(){
	    if(isset($_SESSION['possible_submission']) && $_SESSION['possible_submission']) {
            if(isset($_SESSION['vote']) && !empty($_SESSION['vote'])) {
                $_SESSION['wahl'] = array_unique($_SESSION['vote']);
            }
            elseif(isset($_SESSION['party_elected']) && count($_SESSION['party_elected']) == 1) {
                $_SESSION['wahl'] = array_unique($_SESSION['party_elected']);
            }
            else {
                // Not possible to reach: Submissions are possible only for direct votes or exactly one selected party.
                throw new Exception('The election got changed and does not function correctly anymore!');
            }
        }
	    else {
            $_SESSION['alert'] = 6;     // Do not prepare ballot card for invalid votes
        }
	}

    /**
     * Method called after a vote is changed
     */
	function stimme_bearbeiten(){
	    // Set actual party to load its members again after changing the vote
        $_POST['partei'] = explode('_', $_POST['vote_changed'])[1];

        // Delete current candidate from SESSION when vote got casted on him/her
        if(isset($_SESSION['vote'])) {
            $candidate = substr($_POST['vote_changed'], 0, -1);
            foreach($_SESSION['vote'] as $vote) {
                $vote_candidate = substr($vote, 0, -1);
                if ($candidate == $vote_candidate) {
                    $key = array_search($vote, $_SESSION['vote']);
                    unset($_SESSION['vote'][$key]);
                }
            }
            $_SESSION['vote'] = array_values($_SESSION['vote']);
        }

	    if(isset($_POST['vote_changed'])) {
	        if(substr($_POST['vote_changed'], -1) != '0' && count(explode('_', $_POST['vote_changed'])) == 4) {
                $_SESSION['vote'][] = $_POST['vote_changed'];
            }
	        elseif(count(explode('_', $_POST['vote_changed'])) == 2) {
	            if(isset($_SESSION['party_elected']) && in_array($_POST['vote_changed'], $_SESSION['party_elected'])) {
	                $key = array_search($_POST['vote_changed'], $_SESSION['party_elected']);
	                unset($_SESSION['party_elected'][$key]);
                }
	            else {
                    $_SESSION['party_elected'][] = $_POST['vote_changed'];
                }
                $_SESSION['party_elected'] = array_values($_SESSION['party_elected']);
            }
        }

	    // Count casted votes
        $_SESSION['number_of_votes'] = 0;
        if(isset($_SESSION['vote']) && !empty($_SESSION['vote'])) {
            foreach($_SESSION['vote'] as $vote) {
                $_SESSION['number_of_votes'] += explode('_', $vote)[3];
            }
        }
        elseif(isset($_SESSION['party_elected']) && count($_SESSION['party_elected']) == 1) {
            $party_elected_id = explode('_', $_SESSION['party_elected'][0])[1];
            $number_candidates = $this->count_members($party_elected_id);
            $maximum_number_votes = $this->MaxStimmen_auslesen();
            $_SESSION['number_of_votes'] =
                ($number_candidates <= $maximum_number_votes ? $number_candidates : $maximum_number_votes);
        }
	}

    /**
     * Get array of strings with all candidates of all parties
     *
     * @return array
     */
	function get_search_value(){
		$parties = $this->parteien_auslesen();
		
		// Save all parties in an array
		$count_parteien = 0;
		while($row =  mysqli_fetch_array($parties)){
			$party = str_replace(" ", "&nbsp;", $row['partei']);
			$party_and_members[] = $row['id'].":&nbsp;".$party;
			$count_parteien++;
		}
		
		// Get all candidates for each party
		for($i = 0; $i <= $count_parteien; $i++){
			$candidates = $this->mitglieder_auslesen($i);
			while($row2 =  mysqli_fetch_array($candidates)){
				$last_name = str_replace(" ", "&nbsp;", $row2['name']);
				$first_name = str_replace(" ", "&nbsp;", $row2['vorname']);
				$partei = str_replace(" ", "&nbsp;", $this->get_partei($row2['partei']));
				$party_and_members[] = $row2['id'].":&nbsp;".$last_name.",&nbsp;".$first_name."&nbsp;(".$partei.")";
			}
		}
		
		return $party_and_members;
	}

    /**
     * Highlight the relevant party or candidate based on given search value
     *
     * @param $suche
     */
	function suche($suche){
		$_SESSION['fehler_suche'] = false;
		$suchanfrage = explode(":", $_POST['search']); //Der String wird an dem Doppelpunkt geteilt
		if(preg_match('/^\d{1,4}$/', $suchanfrage[0])){ //Es wird überprüft, ob es sich bei dem ersten Teil des Strings um eine ID handelt
			if(!empty($suchanfrage[1])){	//Es wird überprüft, ob der String nach dem Doppelpunkt (wenn es einen gibt) nicht leer ist
				$corrected_string = trim(str_replace(chr(194).chr(160), ' ', $suchanfrage[1]));
				/* 	Es wird getestet, ob die Suchanfrage einer Partei entspricht. Ist dies der Fall, wird der Suchstring mit dem Parteinamen verglichen.
					Nur wenn der Parteiname und der String gleich sind, wird die gesuchte Partei geöffnet */
				if($this->is_party($suchanfrage[0])){	 
					if($corrected_string == $this->get_partei($suchanfrage[0])){
						$_POST['partei'] = $suchanfrage[0];
						unset($_SESSION['string_searching']);
					}else{
						$_SESSION['fehler_suche'] = true;
					}	
				}
				/* 	Wenn es sich bei der Suchanfrage um einen Kandidaten handelt, wird getestet, ob der Suchstring einem Mitglie einer Partei entspricht.
					Daraufhin wird die Partei des Kandidaten geöffnet und die Suchanfrage einer Session hinzugefügt.*/
				else if($this->is_member($suchanfrage[0])){
				
					$partei_id = intval($suchanfrage[0]/100);
					$searched_member = $this->get_mitglied($suchanfrage[0])." (".$this->get_partei($partei_id).")";
					
					if($corrected_string == $searched_member){
						$_POST['partei'] = $partei_id;
						$_SESSION['string_searching'] = $suchanfrage[0];
					}else{
						$_SESSION['fehler_suche'] = true;
					}
				}else{
					$_SESSION['fehler_suche'] = true;
				}
			}else{
				$_SESSION['fehler_suche'] = true;
			}
		}else{
			$_SESSION['fehler_suche'] = true;
		}
	}

    /**
     * Check whether given name is a party
     *
     * @param $name
     * @return bool
     */
	function is_party($name){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());

		$sql = "SELECT COUNT(*) FROM parteien WHERE id = '".$name."'";
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		mysqli_close($connect);
		
		mysqli_fetch_row($result)[0]==1 ?  $response = true : $response = false;
		return $response;
	}

	//Diese Methode bekommt einen Namen übergeben und testet, ob es sich dabei um ein Mitglied einer Partei handelt

    /**
     * Check whether given name (id) is a party candidate
     *
     * @param $candidate_id
     * @return bool
     */
	function is_member($candidate_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());

		$sql = "SELECT COUNT(*) FROM mitglieder WHERE id = ".$candidate_id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		mysqli_close($connect);
		
		mysqli_fetch_row($result)[0]==1 ?  $response = true : $response = false;
		return $response;
	}

    /**
     * Create summary of $_SESSION['wahl'] which could be used to retrieve the vote (view info and create PDF)
     *
     * The election code could NOT be used like this for the QR code generation!
     *
     * @return array
     * @throws Exception if more than one party got elected
     */
	function wahlcode_erstellen(){

	    $summary = array(
            'election_type' => $_SESSION['aktive_wahl'],
            'possible_submission' => $_SESSION['possible_submission']
        );

	    if($_SESSION['possible_submission'] && isset($_SESSION['wahl'])) {
	        if(count(explode('_', $_SESSION['wahl'][0])) == 2) {
    	        if(count($_SESSION['wahl']) == 1) {
	                $summary['party_id'] = explode('_', $_SESSION['wahl'][0])[1];
                }
	            else {
                    // It should NOT be possible to elect more than one party.
                    throw new Exception('The election got changed and does not function correctly anymore!');
                }
            }
	        else {
	            $candidates = [];
                foreach($_SESSION['wahl'] as $candidate) {
                    $candidate_exploded =explode('_', $candidate);
                    if(count($candidate_exploded) == 4) {
                        $candidate_party = $candidate_exploded[1];
                        if(!array_key_exists($candidate_party, $candidates)) {
                            $candidates[$candidate_party] = [];
                        }
                        $candidate_id = $candidate_exploded[2];
                        $candidate_votes = $candidate_exploded[3];
                        $candidates[$candidate_party][$candidate_id] = $candidate_votes;
                    }
	            }
                $summary['candidates'] = $candidates;
            }
        }

	    return $summary;
	}

    /**
     * Check election rules and assign the correct header
     */
	function check_rules(){

	    if(isset($_SESSION['vote']) && !empty($_SESSION['vote'])) {
	        if(isset($_SESSION['party_elected']) && !empty($_SESSION['party_elected']) && $_SESSION['number_of_votes'] <= $this->MaxStimmen_auslesen()) {
	            $_SESSION['alert'] = 2;     // Valid: at least one party AND specific candidates selected (case 2)
                $_SESSION['possible_submission'] = true;
            }
	        elseif($_SESSION['number_of_votes'] <= $this->MaxStimmen_auslesen()) {
	            $_SESSION['alert'] = 1;     // Valid: only specific candidates selected (case 1)
                $_SESSION['possible_submission'] = true;
            }
            elseif ($_SESSION['number_of_votes'] > $this->MaxStimmen_auslesen()) {
                $_SESSION['alert'] = 4;     // Invalid: too many candidates selected
                $_SESSION['possible_submission'] = false;
            }
        }
	    elseif(isset($_SESSION['party_elected']) && !empty($_SESSION['party_elected'])) {
	        if(count($_SESSION['party_elected']) == 1) {
                $_SESSION['alert'] = 3;     // Valid: one party selected (case 3)
                $_SESSION['possible_submission'] = true;
            }
	        elseif(count($_SESSION['party_elected']) > 1) {
                $_SESSION['alert'] = 5;     // Irrelevant: multiple parties are selected (case 5)
                $_SESSION['possible_submission'] = false;
            }
        }
	    else {
	        $_SESSION['alert'] = 0;     // Please select something (case 0)
            if(isset($_SESSION['possible_submission'])) {
                unset($_SESSION['possible_submission']);
            }
        }
	}

	//Erstellt das PDF aus dem übergebenen Wahlcode
	function makePDF($wahlcode){
		$number = -1;
		if(isset($wahlcode)){
			$qr_code = $wahlcode;
			$number = explode("-", $qr_code)[1];
			$qr_code = explode("-", $qr_code)[0];
				
		
			//Berechnung der Informationen aus dem Wahlcode
			$tmp = $this->get_infos($qr_code);
			
			$partei = $tmp[0];
			$gueltig = $tmp[1];
			$members_party_indirect = $tmp[2];
			$members_other = $tmp[3];
			$members_deleted_count = $tmp[4];
			$members_deleted = $tmp[5];
			$max_votes = $tmp[6];
			$count_total = $tmp[7];
			$count_list = $tmp[8];
			$not_voted = $tmp[9];
			$aktuelleWahl = $tmp[10];
			$count_party = $tmp[11];
			
			//Erstellung des PDF's
			$pdf = new FPDF();
			
			//Variablen
			$party = $this->get_partei($partei);
			$party = utf8_decode($party);  
			
			$date = $this->get_date();
			$date = utf8_decode($date);
			
			//Bausteine für die verschiedenen Varianten der Info Boxen
			$text0 = "Der Stimmzettel ist ungültig, da die Schaltfläche \n >Ungültig wählen< gedrückt wurde\n\n\n";
			$text1_6  = "Der Stimmzettel ist ungültig, da keine Stimme vergeben wurde.\n\n\n";
			$text4_41 = "Hinweis: Es wurden mehr als $max_votes Stimmen in einer Liste vergeben, daher wurden Stimmen abgeschnitten. Der zählende Teil Ihrer Stimme ist untenstehend Abgebildet.\n\n";
			$text2_3_8  = "$max_votes Stimmen stehen insgesamt zur Verfügung. \nDirekt vergebene Stimmen: $count_total \nüber die Kopfstimme (Listenstimme) vergebene Stimmen: $count_list \nNicht vergebene Stimmen: $not_voted";
			$text5 = "Der Stimmzettel ist ungültig, da Sie mehr als $max_votes Stimmen vergeben haben.\n\n\n";
			$text7 = "Ihre Stimme ist ungültig. Sie haben mehr als eine Kopfstimme vergeben.\n\n\n";
			
			// Kopfbereich des Stimmzettels
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',30);
			$pdf->Cell(60, 10, "Stimmzettel");
			$pdf->ln();
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(150,7,"$aktuelleWahl der Wissenschaftsstadt Darmstadt am $date");
			$pdf->SetX(171);
			$pdf->SetFont('Arial','',8);
			$pdf->MultiCell(25, 3,"QR-Code \nzur automat. Auszählung","","l");
			$pdf->SetFont('Arial','',10);
			
			
			//Feld für die mögliche Listenstimme bzw. Hinweise
			//Mittleres Textfeld
			switch($number)
			{
				//Fall 0: "Der Stimmzettel ist ungültig, da die Schaltfläche >Ungültig wählen< gedrückt wurde".
				case("0"):
					$pdf->Cell(57, 24,"Ungültig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text0", 1);
					break;
				//Fall 2: "Eine Partei wurde gewählt und x Kandidaten. Insgesammt gibt es y Stimmen
				case("1"):
					$pdf->Cell(57, 24, "",1);
					$x=$pdf->getX();
					$y=$pdf->getY();
					$pdf->Image("bilder/initvoteColoured.png", 13, 31);
					$pdf->SetFontSize(18);
					$pdf->Text(30 ,40, "$party");
					$pdf->SetFontSize(8);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text2_3_8", 1);
					break;
				//Fall 3: "Es wurde keine Partei, sondern nur x Kandidaten gewählt
				case("2"):
					$pdf->Cell(57, 24,"Keine Liste gewählt",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text2_3_8", 1);
					break;
				//Fall 4 bzw 4*: Es wurden x Kandidaten aus EINER Liste gewählt, wobei x>maxStimmen ist.
				case("3"):
					$pdf->Cell(57, 24, "",1);
					$x=$pdf->getX();
					$y=$pdf->getY();
					$pdf->Image("bilder/initvoteColoured.png", 13, 31);
					$pdf->SetFontSize(18);
					$pdf->Text(30 ,40, "$party");
					$pdf->SetFontSize(8);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text4_41", 1);
					break;
				//Fall 5: Es wurden x Kandidaten aus mehreren Listen gewählt.
				case("4"):
					$pdf->Cell(57, 24,"Ungültig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text5", 1);
					break;
				//Fall 6: Alle gestzen Stimmen wurden entfernt.
				case("5"):
					$pdf->Cell(57, 24,"Ungültig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text1_6", 1);
					break;
				//Fall 7: Es wurden 2 oder mehr Parteien angekreuzt, aber KEINE Stimmen an einzelne Kandidaten vergeben.
				case("6"):
					$pdf->Cell(57, 24,"Ungültig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text7", 1);
					break;
				//Fall 8: Es wurden 2 oder mehr Parteien angekreuzt und Stimmen an einzelne Kandidaten vergeben.
				case("7"):
					$pdf->Cell(57, 24,"",1);
					$pdf->Text(11, 33, "Hinweis:");
					$pdf->Text(11, 39, "Mehrfach vergebene Kopf-");
					$pdf->Text(11, 45, "stimmen werden ignoriert.");
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text2_3_8", 1);
					break;
				//Fall 1: "Zu Beginn - keine Stimme vergeben bzw. nach "Eingabe löschen"
				default:
					$pdf->Cell(57, 24,"Ungültig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text1_6", 1);
					break;
			}

			// Feld für den QR-Code
			$pdf->SetXY(171, 29);
			$pdf->Cell(25,24,$pdf->Image("http://localhost/EasyVote/qrcode.php/?id=$qr_code",$pdf->getX()+1,$pdf->getY()+1,23,23,'PNG'), 1);
			
			//Aufbau der einzelstimmen bzw. gestrichenen Kandidaten
			//Stimmzettel ungültig?
			$pdf->SetY(55);

			// Alle Stimmen zusammnfügen und sortieren
			$members_other = array_merge($members_party_indirect, $members_other);
			sort($members_other);
		
			$tmp = '';
			$members_other_parties = array();
			$votes_all = array();
			$votes = 0;
			// Zuordnung der Partei zu den Mitgliedern und Zählen der Stimmen pro Partei
			foreach($members_other as $member){
				$member_data = explode("_", $member);
				$member_id = intval($member_data[0]);
				$party_id = floor($member_id / 100);	
				if($tmp != $party_id){
					$tmp = $party_id;
					$members_other_parties[] = "partei_".utf8_decode($this->get_partei($party_id));
					$votes_all[] = $votes;
					$votes = 0;
				}
				$votes = $votes + intval(substr($member_data[1], 0, 1)) + intval(substr($member_data[1], 1, 1));
				$members_other_parties[] = $member;
			}
			$votes_all[] = $votes;
			// Splitten der Anzeige der abgegebenen Stimmen falls nötig
			if(sizeof($members_other_parties) > 43){
				if(explode("_", $members_other_parties[42])[0] == 'partei'){
				$part_one = array_slice($members_other_parties , 0, 42);
				$part_two = array_slice($members_other_parties , 42, sizeof($members_other_parties));
			}else{
				$part_one = array_slice($members_other_parties , 0, 41);
				$part_two = array_slice($members_other_parties , 41, sizeof($members_other_parties));
			}
			}else{
				$part_one = $members_other_parties;
				$part_two = array();
			}			
			//Aufbau der linken Spalte der Stimmen
			$cur_posY = $pdf-> getY();//Merken der aktuellen y Position
			$i = 1;
			foreach($part_one as $member){
				if(explode("_", $member)[0] != 'partei'){
					$a = intval(explode("_", $member)[0]);
					$b = intval(explode("_", $member)[1]);
					$m = utf8_decode($this->get_mitglied($a));
					$image = $this->get_image($b);
					if($image != "ERROR"){
						$pdf->Cell(14, 5,  $pdf->Image($image, $pdf->getX()+1, $pdf->getY()+0.6), 1);
					}else{	
						$pdf->Cell(14, 5, $image, 1);
					}
					$pdf->Cell(50, 5, "$m", 1);
					$pdf->Cell(10, 5, "$a", 1);
					}else{
					$b = explode("_", $member)[1];
					$stimmen_anzahl = $votes_all[$i];
					$pdf->SetFont("Arial", "B");
					$pdf->Cell(37, 5, "$b", "LTB");
					$pdf->Cell(37, 5, "$stimmen_anzahl Stimmen", "TRB", 0, "R");
					$pdf->SetFont("Arial");
					$i++;
				}
				$pdf->ln();
			}

				//Gestrichene Kandidaten
				if($members_deleted_count != 0){
					$posX = 86;
					$pdf->SetXY($posX, $cur_posY);
					$pdf->SetFontSize(6);
					$pdf->MultiCell(34, 3.5, "In der gewählten Liste gestrichene Kandidaten",1, 2);
					foreach($members_deleted as $member){
						$pdf->SetX($posX);
						$a = intval(explode("_", $member)[0]);
						$m = utf8_decode($this->get_mitglied($a));
						$x = $pdf->getX();
						$y = $pdf->getY();
						$pdf->Cell(6, 3.5, "$a", 1);
						$pdf->Cell(28, 3.5, "$m", 1, 2);
						$pdf->SetDrawColor(255, 200, 0);
						$pdf->Line($x, $y+1.75, $x+34, $y+1.75);
						$pdf->SetDrawColor(0,0,0);
					}
				}
				$pdf->SetFontSize(8);
				$posX = 122;
				$pdf->SetXY($posX, $cur_posY);
			foreach($part_two as $member){
					$pdf->SetX($posX);
					
					if(explode("_", $member)[0] != 'partei'){
						$a = intval(explode("_", $member)[0]);
						$b = intval(explode("_", $member)[1]);
						$m = utf8_decode($this->get_mitglied($a));
						$pdf->Cell(10, 5, "$a", 1);
						$pdf->Cell(50, 5, "$m", 1);
						
						$image = $this->get_image($b);
						if($image != "ERROR"){
							$pdf->Cell(14, 5,  $pdf->Image($image, $pdf->getX()+1, $pdf->getY()+0.6), 1);
						}else{	
							$pdf->Cell(14, 5, $image, 1);
						}
					}else{
						$b = explode("_", $member)[1];
						$stimmen_anzahl = $votes_all[$i];
						$pdf->SetFont("Arial", "B");
						$pdf->Cell(37, 5, "$b", "LTB");
						$pdf->Cell(37, 5, "$stimmen_anzahl Stimmen", "TRB", 0, "R");
						$pdf->SetFont("Arial");
						$i++;
					}
					
					$pdf->ln();
				}
			
			$pdf->Output("abgabe.pdf" , "i");
		}else{
			$qr_code = "Fehler bei der Erstellung des PDFs";
		}
	}

    /**
     * Display election summary before creating PDF
     *
     * @param $summary
     * @throws Exception if number of candidates of elected party are not equal to maximum number of votes
     */
	function show_wahl($summary){

		if(isset($summary['party_id']) && !empty($summary['party_id'])) {       // One party got elected.
		    // Each candidate of this party receives one vote.

            $number_candidates = intval($this->count_members($summary['party_id']));
            // Assume that each party has the same number of members as the number of maximum votes.
            if($number_candidates != $this->MaxStimmen_auslesen()) {
                throw new Exception('The maximum number of votes are not equal to the number of candidates of '
                    .'the elected party. Fix the code!');
            }

            $voted = [];
            for($i = intval($summary['party_id'])*100 + 1;
                $i <= intval($summary['party_id'])*100 + $number_candidates;
                $i++) {

                $voted[$i] = 1;
            }

            $party_name = $this->get_partei($summary['party_id']);
            echo "<br /><h1>Abgabe Stimmzettel $party_name</h1>";
            echo "<p>Sie haben die Liste $party_name ausgew&auml;hlt. Alle Kandidierenden dieser Liste erhalten jeweils"
                ." automatisch eine Stimme. Geben Sie <strong>nur den unmarkierten Stimmzettel</strong> der Liste"
                ." $party_name ab.</p>";

            echo "<div id='summary-columns'>";
            echo $this->display_party($summary['party_id'], $voted);
            echo "</div>";
		}
		elseif(isset($summary['candidates']) && !empty($summary['candidates'])) {      // Candidates got directly elected.

		    $parties = array_keys($summary['candidates']);
		    $parties_string = '';
		    for($i = 0; $i < count($parties); $i++) {
		        if($i == count($parties)-1 && $i > 0) {
		            $parties_string .= ' und '.$this->get_partei($parties[$i]);
                }
		        elseif($i == 0) {
		            $parties_string .= $this->get_partei($parties[$i]);
                }
		        else {
		            $parties_string .= ', '.$this->get_partei($parties[$i]);
                }
            }

            echo "<br /><h1>Abgabe mehrerer Stimmzettel</h1>";
			echo "<p>Geben Sie die Stimmzettel f&uuml;r die folgenden Listen ab: $parties_string. Gem&auml;&szlig;"
                ." Ihrer Auswahl m&uuml;ssen Sie auf diesen Stimmzetteln die folgenden Stimmen abgeben.</p>";

            echo "<div id='summary-columns'>";
            foreach($summary['candidates'] as $party_id => $candidates) {
                if(count($candidates) <= $this->MaxStimmen_auslesen() / 3) {
                    echo "<div class='party-complete-no-break'>";
                }
                echo $this->display_party($party_id, $candidates);
                if(count($candidates) <= $this->MaxStimmen_auslesen() / 3) {
                    echo "</div>";
                }
            }
            echo "</div>";
		}
	}

	function display_party($party_id, $candidates) {
	    $response = "<div class='row party-row'>".$this->get_partei($party_id)."</div>";
	    foreach($candidates as $candidate_id => $candidate_votes) {
	        $response .= "<div class='row candidate-row'>"
                ."<table>"
                ."<tr><td>".$candidate_id."</td>"
                ."<td>".$this->get_mitglied($candidate_id)."</td>"
                ."<td>".$candidate_votes."</td></tr>"
                ."</table>"
                ."</div>";
	    }
	    $response .= "<div class='row empty-row'>&nbsp;</div>";

        return $response;
    }
}	
?>
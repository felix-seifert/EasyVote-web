<?php

require_once('Wahl.php');
require_once('Wahl_Interface.php');
// Klasse f�r die Kommunalwahlen
class Kommunalwahl extends Wahl implements Wahl_Interface
 {
 
	function __construct(){
		$this->xmlFile = 'wahlen/xmlFiles/kommunalwahl.xml';
		$this->database = 'kommunalwahl';
	}
	
	//Liest die Anzahl der maximal zu vergebenden Stimmen f�r die Wahl aus der .xml-Datei aus
	function MaxStimmen_auslesen(){
		$maxvotes = 0;
		if(file_exists($this->xmlFile)){
			$xml = simplexml_load_file($this->xmlFile);
			$maxvotes = $xml->rules->max_vote;
		} else{
			exit("Fehler");
		}
		
		return $maxvotes;
	}
	
	//Die Funktion erstellt eine Datenbank f�r eine Kommunalwahl und f�llt sie mit dem Werten, die aus der XML-Datei ausgelesen werden
	function datenbank_erstellen(){

		$connect = mysqli_connect($this->host, $this->username, $this->password) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");
		
		//Die Datenbank wird nur erstellt, wenn sie nicht schon vorhanden ist
		if(mysqli_select_db($connect, $this->database) == '0'){

			mysqli_query($connect, "CREATE DATABASE IF NOT EXISTS " . $this->database) or die(mysqli_error());
			mysqli_query($connect, "USE ".$this->database) or die(mysqli_error());

			$sql = "CREATE TABLE IF NOT EXISTS parteien (id INTEGER NOT NULL, partei VARCHAR(20), PRIMARY KEY(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());

			$sql = "CREATE TABLE IF NOT EXISTS mitglieder (id INTEGER NOT NULL, name VARCHAR(40), vorname VARCHAR(40), partei INTEGER(20), PRIMARY KEY(id), FOREIGN KEY (partei)
			   REFERENCES parteien(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());
			
			//Auslesen der XML-Datei und F�llen der Datenbank
			if(file_exists($this->xmlFile)){
				$xml = simplexml_load_file($this->xmlFile);
					
				//Auslesen der Parteien und Einlesen in die Datenbank
				foreach($xml->parties->party as $partei)
				{
					$sql = "INSERT INTO parteien (id, partei) VALUES (".$partei['id'].", '".$partei."')";
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
					
				//Auslesen der Kandidaten
				foreach($xml->candidates->candidate as $kandidat)
				{
					$sql = "INSERT INTO mitglieder (id, name, vorname, partei) VALUES (".$kandidat['id'].", '".$kandidat['name']."', '".$kandidat['prename']."', (SELECT id FROM parteien WHERE partei = '".$kandidat['partei']."'))";
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
			}
			else{
				exit("Fehler - XML-Datei kann nicht ge�ffnet werden");
			}
		}
		
		mysqli_close($connect);
	}
	
	//Liest die Mitglieder einer Partei aus der Datenbank aus und liefert das Ergebnis der Anfrage zur�ck
	function mitglieder_auslesen($partei){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT * FROM mitglieder WHERE partei = " . $partei;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());

		mysqli_close($connect);
		
		return($result);
	}
	
	//Liest die Anzahl der Mitglieder einer Partei aus der Datenbank aus und liefert das Ergebnis zur�ck
	function count_members($partei){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());

		$sql = "SELECT COUNT(*) FROM mitglieder WHERE partei = " . $partei;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		$tmp = mysqli_fetch_row($result);
		$ergebnis = $tmp[0];

		mysqli_close($connect);
		
		return($ergebnis);
	}
	
	// Z�hlt die Stimmen der Kandidaten aus einem �bergebenen Array aus und gibt die Anzahl der Stimmen zur�ck
	// Das Array enth�lt Werte der Form "member_x_y_z" oder "partei_x", wobei x die Partei-ID, y die ID des Kandidaten und
	// z die Anzahl der Stimmen ist, die einem Kandidaten gegeben wurde
	function voted_counter($vote_array){
		$vote_counter = 0;
		$zero = array();
		$two = array();
		$three = array();
		
		//Teilt den Inhalt des Arrays anhand der vergebenen Stimmen f�r einen Kandidaten in verschiedene Arrays auf und rechnet alle Stimmen zusammen
		foreach($vote_array as $vote){			
			if(explode("_", $vote)[0] == "member"){
				if(explode("_", $vote)[3] != "0"){
					if(explode("_", $vote)[3] == "3"){
						$three[] = $vote;
					}
					if(explode("_", $vote)[3] == "2"){
						$two[] = $vote;
					}
					$vote_counter = $vote_counter + explode("_", $vote)[3]; //Anzahl der insgesamt vergebenen Stimmen
				}else{
					$zero[] = $vote;
				}
			}
		}		
		
		// Pr�ft f�r alle gestrichenen Kandidaten, ob Stimmen f�r diesen vergeben wurden und subtrahiert diese Stimmen 
		foreach($zero as $tmp_zero){
			$x = explode("_", $tmp_zero);
			$tmp_one = $x[0]."_".$x[1]."_".$x[2]."_1";
			$tmp_two = $x[0]."_".$x[1]."_".$x[2]."_2";
			$tmp_three = $x[0]."_".$x[1]."_".$x[2]."_3";
			if(in_array($tmp_one, $vote_array)){
				$vote_counter = $vote_counter - 1;
				if(in_array($tmp_two, $vote_array)){
					$vote_counter = $vote_counter - 1;
					if(in_array($tmp_three, $vote_array)){
						$vote_counter = $vote_counter - 1;
					}
				}
			}else if(in_array($tmp_two, $vote_array)){
				$vote_counter = $vote_counter - 2;
				if(in_array($tmp_three, $vote_array)){
					$vote_counter = $vote_counter - 1;
				}
			}else if(in_array($tmp_three, $vote_array)){
				$vote_counter = $vote_counter - 3;
			}
		}
		
		// Pr�ft f�r alle Kandidaten mit drei Stimmen, ob zus�tzlich eine oder zwei Stimmen vergeben wurde und subtrahiert diese Stimmen
		foreach($three as $tmp_three){
			$x = explode("_", $tmp_three);
			$tmp_one = $x[0]."_".$x[1]."_".$x[2]."_1";
			$tmp_two = $x[0]."_".$x[1]."_".$x[2]."_2";
			if(in_array($tmp_one, $vote_array)){
				$vote_counter = $vote_counter - 1;
				if(in_array($tmp_two, $vote_array)){
					$vote_counter = $vote_counter - 1;
				}
			}else if(in_array($tmp_two, $vote_array)){
				$vote_counter = $vote_counter - 2;
			}
		}
		
		// Pr�ft f�r alle Kandidaten mit zwei Stimmen, ob zus�tzlich eine Stimmen vergeben wurde und subtrahiert diese Stimmen
		foreach($two as $tmp_two){
			$x = explode("_", $tmp_two);
			$tmp_one = $x[0]."_".$x[1]."_".$x[2]."_1";
			$tmp_two = $x[0]."_".$x[1]."_".$x[2]."_2";
			if(in_array($tmp_one, $vote_array)){
				$vote_counter = $vote_counter - 1;
			}
		}
		
		$_SESSION['member_count']=$vote_counter;
		return $vote_counter;
	}
	
	//Ausgabe der Mitgliederliste einer Partei auf der Website
	function mitgliederliste_anzeigen($partei_id)
	{
		$partei_name = $this->get_partei($partei_id);
		$mitglieder_liste = $this->mitglieder_auslesen($partei_id);
		
		$image_one ="<img src='bilder/BallotUnchecked.gif'>";
		$image_two ="<img src='bilder/BallotUnchecked.gif'>";
		$image_three ="<img src='bilder/BallotUnchecked.gif'>";
		$disabled_one = '';
		$disabled_two ='';
		$disabled_three='';
		$image = "<img src='bilder/BallotUnchecked50.gif'>";
		$one_vote_implicit = array();
		$two_vote_implicit = array();
		$three_vote_implicit = array();
		
		// Wenn die Session 'vote' gesetzt wurde, also Stimmen vergeben wurden
		if(isset($_SESSION['vote'])){
			if(in_array('partei_'.$partei_id, $_SESSION['vote'])){
				$image = "<img src='bilder/BallotChecked50.gif'>";
				$max_votes = $this->MaxStimmen_auslesen(); //Anzahl der maximalen Stimmen der Kommunalwahl
				$member_counter = $this->count_members($partei_id); //Anzahl der Mitglieder der aktuell angezeigten Partei
				$stimmen_alle = floor($max_votes / $member_counter);	//Anzahl der implizit zu vergebenden Stimmen an alle Mitglieder
				$stimmen_additiv = $max_votes % $member_counter;	//Anzahl der restlichen Stimmen
				
				//ermittelt, ob eine oder mehrere Parteien angekreuzt wurden
				$one_party = true;
				foreach($_SESSION['vote'] as $vote){
					if(explode("_", $vote)[0] == 'partei' && explode("_", $vote)[1] != $partei_id){
						$one_party = false;
						break;
					}
				}
				
				// wenn genau eine Partei gew�hlt wurde, werden die implizit zu vergebenden Stimmen berechnet und auf die Kandidaten der Partei verteilt
				if($one_party){	
					$member_count = $this->voted_counter($_SESSION['vote']);
					
						//Berechnet, wieviele Stimmen in der gew�hlten Partei implizit vergeben werden
						if($max_votes < $member_count){
							$max_votes = 0;
						}else{
							$max_votes = $max_votes - $member_count;
						}
						$i = intval($partei_id) * 100 + 1;
						$x = intval($partei_id) * 100 + $member_counter;
						
						/* 	Erste Spalte:
							Iteriert �ber alle Mitglieder der gew�hlten Partei und f�gt die implizite Stimme je nach Situation dem Array hinzu, das die implizite Stimme enth�lt
							Dabei werden die Anzahl der verbleibenden, noch zu vergebenden Stimmen dekrementiert
							keine Stimme vergeben 	=> f�ge Stimme zu Array one_vote_implicit hinzu
							1 Stimme vergeben 		=> f�ge Stimme zu Array two_vote_implicit hinzu
							2 Stimmen vergeben		=> f�ge Stimme zu Array three_vote_implicit hinzu
							Kandidat gestrichen	oder 3 Stimmen vergeben => vergebe keine implizite Stimme
						*/
						for($i = intval($partei_id) * 100 + 1; $i <= $x; $i++){
							if($max_votes == 0){break;}		// Wenn keine impliziten Stimmen mehr zu vergeben sind, breche die Schleife ab
							$tmp0 = 'member_'.$partei_id.'_'.$i.'_0';
							$tmp1 = 'member_'.$partei_id.'_'.$i.'_1';
							$tmp2 = 'member_'.$partei_id.'_'.$i.'_2';
							$tmp3 = 'member_'.$partei_id.'_'.$i.'_3';
							
							if(!(in_array($tmp0, $_SESSION['vote']) || in_array($tmp1, $_SESSION['vote']) || in_array($tmp2, $_SESSION['vote']) || in_array($tmp3, $_SESSION['vote']))){
								$max_votes = $max_votes - 1;
								$one_vote_implicit[] = $tmp1;	// Wenn keine Stimme vergeben wurde, f�ge implizite Stimme zu one_vote_implicit hinzu
							}else if(!in_array($tmp0, $_SESSION['vote'])){			
								if(in_array($tmp1, $_SESSION['vote'])){ 
									if(in_array($tmp2, $_SESSION['vote'])){
										if(!in_array($tmp3, $_SESSION['vote'])){
											$max_votes = $max_votes - 1;
											$three_vote_implicit[] = $tmp3; // Wenn erst eine und dann zwei Stimmen, aber keine drei Stimmen vergeben wurden, f�ge implizite Stimme zu three_vote_implicit hinzu
										}
									}else if(!in_array($tmp3, $_SESSION['vote'])){
										$max_votes = $max_votes - 1;
										$two_vote_implicit[] = $tmp2; // Wenn eine Stimme vergeben wurde, f�ge implizite Stimme zu two_vote_implicit hinzu
									}
								}else if(in_array($tmp2, $_SESSION['vote'])){ 
									if(!in_array($tmp3, $_SESSION['vote']) && !in_array($tmp3, $three_vote_implicit)){
										$max_votes = $max_votes - 1;
										$three_vote_implicit[] = $tmp3; // Wenn zwei Stimmen und keine drei Stimmen vergeben wurden, f�ge implizite Stimme zu three_vote_implicit hinzu
									}
								}
							}
						}
						
						// Wenn komplett �ber die erste Spalte iteriert wurde, wird �ber die zweite Spalte iteriert und die impliziten Stimmen vergeben
						for($i = intval($partei_id) * 100 + 1; $i <= $x; $i++){
							if($max_votes == 0){break;}		// Wenn keine impliziten Stimmen mehr zu vergeben sind, breche die Schleife ab
							$tmp0 = 'member_'.$partei_id.'_'.$i.'_0';
							$tmp1 = 'member_'.$partei_id.'_'.$i.'_1';
							$tmp2 = 'member_'.$partei_id.'_'.$i.'_2';
							$tmp3 = 'member_'.$partei_id.'_'.$i.'_3';
							if(!(in_array($tmp0, $_SESSION['vote']) || in_array($tmp1, $_SESSION['vote']) || in_array($tmp2, $_SESSION['vote']) || in_array($tmp3, $_SESSION['vote']))){
								if(!in_array($tmp2, $two_vote_implicit)){
									$max_votes = $max_votes - 1;
									$two_vote_implicit[] = $tmp2;	// Wenn keine Stimme vergeben wurde, f�ge implizite Stimme zu two_vote_implicit hinzu
								}
							}else if(!in_array($tmp0, $_SESSION['vote'])){	
								if(in_array($tmp2, $_SESSION['vote'])){
									if(!in_array($tmp3, $_SESSION['vote']) && !in_array($tmp3, $three_vote_implicit)){
										$max_votes = $max_votes - 1;
										$three_vote_implicit[] = $tmp3; // Wenn zwei Stimmen vergeben wurden und die dritte implizite Stimme noch nicht gesetzt wurde, f�ge implizite Stimme zu three_vote_implicit hinzu
									}
								}else if(in_array($tmp1, $_SESSION['vote']) && in_array($tmp2, $two_vote_implicit)){
									$max_votes = $max_votes - 1;
									$three_vote_implicit[] = $tmp3;	// Wenn eine Stimme vergeben wurde und die zweite implizite Stimme gesetzt wurde, f�ge implizite Stimme zu three_vote_implicit hinzu
								}
							}
						}
						
						// Wenn komplett �ber die zweite Spalte iteriert wurde, wird �ber die dritte Spalte iteriert und die impliziten Stimmen vergeben
						for($i = intval($partei_id) * 100 + 1; $i <= $x; $i++){
							if($max_votes == 0){break;}
							$tmp0 = 'member_'.$partei_id.'_'.$i.'_0';
							$tmp3 = 'member_'.$partei_id.'_'.$i.'_3';
							if(!(in_array($tmp0, $_SESSION['vote']) || in_array($tmp3, $_SESSION['vote']))){
								if(!in_array($tmp3, $three_vote_implicit)){
									$max_votes = $max_votes - 1;
									$three_vote_implicit[] = $tmp3; // Wenn die dritte Stimme noch nicht iterativ vergeben wurde und nicht im Array vorhanden ist, f�ge die implizite Stimme zu $three_vote_implicit hinzu
								}
							}
						}
				} else {
					$image = "<img src='bilder/BallotChecked50.gif'>";	//Wenn mehr als eine Partei gew�hlt wurde, setze nur das Bild auf "gew�hlt"
				}	
			}
		}
		
		// Zeigt den Namen der Partei und einen w�hlbaren Button an
		echo "<h1><span style='width:7em;float:left;'>". $partei_name. "</span><button type='submit' name='vote_changed' value='partei_".$partei_id."' id='$partei_id' class='text_button'>".$image."</button></h1>";
		
		echo "<div id='Tabelle'>";
		echo "<hr />";
		
		//erstellen der Tabelle der Mitglieder der ausgew�hlten Partei
		$mitglied_zeile = array();
		while($row = mysqli_fetch_array($mitglieder_liste)){
			$member = "<b>". $row['name'].",</b> ". $row['vorname'];
			$color = '';
			
			/* definiert die Bilder, die angezeigt werden, wenn dem Kandidaten eine, zwei oder drei implizite Stimmen zugeteilt wurden */
			if(in_array('member_'.$partei_id.'_'.$row['id'].'_1', $one_vote_implicit)){
				$image_one = "<img src='bilder/BallotCheckedGray.gif'>";
			}
			if(in_array('member_'.$partei_id.'_'.$row['id'].'_2', $two_vote_implicit)){
				$image_two = "<img src='bilder/BallotCheckedGray.gif'>";
			}
			if(in_array('member_'.$partei_id.'_'.$row['id'].'_3', $three_vote_implicit)){
				$image_three = "<img src='bilder/BallotCheckedGray.gif'>";
			}
			
			// Wenn ein Kandidat gesucht wurde, wird die Hintergrundfarbe seiner Zelle ge�ndert
			if(isset($_SESSION['string_searching'])){
				if($row['id'] == $_SESSION['string_searching']){
					$color = 'bgcolor = #BEDBEE';
				}
			}
			
			/* 	definiert die Bilder, die angezeigt werden, wenn dem Kandidaten eine, zwei oder drei direkte Stimmen zugeteilt wurden 
				- Wenn zwei Stimmen vergeben wurden, wird auch die erste Stimme als gew�hlt markiert und dieser Button wird gesperrt
				- Wenn drei Stimmen vergeben wurden, werden die anderen Stimmen als gew�hlt markiert und ihre Buttons gesperrt
				- Wenn der Kandidat gestrichen wurde, werden alle Bilder und Stimmen gesperrt
			*/	
			if(isset($_SESSION['vote'])){
				if(in_array('member_'.$partei_id.'_'.$row['id'].'_1', $_SESSION['vote'])){
					$image_one = "<img src='bilder/BallotChecked.gif'>";				
				}
				if(in_array('member_'.$partei_id.'_'.$row['id'].'_2', $_SESSION['vote'])){
					$image_one = "<img src='bilder/BallotChecked.gif'>";
					$disabled_one="disabled='disabled'";
					$image_two = "<img src='bilder/BallotChecked.gif'>";
				}
				if(in_array('member_'.$partei_id.'_'.$row['id'].'_3', $_SESSION['vote'])){
					$image_one = "<img src='bilder/BallotChecked.gif'>";
					$disabled_one="disabled='disabled'";
					$image_two = "<img src='bilder/BallotChecked.gif'>";
					$disabled_two="disabled='disabled'";
					$image_three = "<img src='bilder/BallotChecked.gif'>";
				}
				if(in_array('member_'.$partei_id.'_'.$row['id'].'_0', $_SESSION['vote'])){
					$image_one = "<img src='bilder/BallotUnchecked.gif' disabled='disabled'>";
					$image_two = "<img src='bilder/BallotUnchecked.gif' disabled='disabled'>";
					$image_three = "<img src='bilder/BallotUnchecked.gif' disabled='disabled'>";
					$disabled_one="disabled='disabled'";
					$disabled_two="disabled='disabled'";
					$disabled_three="disabled='disabled'";
					
					$member = "<del>".$member."<del>";
				}
			}
		
			//Speichert die Zeile des Mitgliedes in einem Array
			$mitglied_zeile[] = "<tr $color><td width = 25px><b>". $row['id'] ."</b></td><td><button type='submit' name='vote_changed' value='member_".$partei_id."_".$row['id']."_0' class='text_button'>".$member."</button></td><td width = 80px>".
			"<button type='submit' name='vote_changed' value='member_".$partei_id."_".$row['id']."_1' id='$partei_id' class='text_button' $disabled_one>".$image_one."</button>".
			"<button type='submit' name='vote_changed' value='member_".$partei_id."_".$row['id']."_2' id='$partei_id' class='text_button' $disabled_two>".$image_two."</button>".
			"<button type='submit' name='vote_changed' value='member_".$partei_id."_".$row['id']."_3' id='$partei_id' class='text_button' $disabled_three>".$image_three."</button>".
			"</td></tr>";
			
			//Zur�cksetzen aller ge�nderten Variablen
			$image_one ="<img src='bilder/BallotUnchecked.gif'>";
			$image_two ="<img src='bilder/BallotUnchecked.gif'>";
			$image_three ="<img src='bilder/BallotUnchecked.gif'>";
			$disabled_one='';
			$disabled_two='';
			$disabled_three='';
		}
		
		// Ausgabe der Tabellen der Mitglieder der angezeigten Partei
		echo "<table style='float:left;' margin-right=20px; width = 375px;>";
		$counter = count($mitglied_zeile);
		$first = round($counter / 2);
		for($i = 0; $i < $first; $i++){
			echo $mitglied_zeile[$i];
		}
		echo "</table>";
		echo "<table width = 375px;>";
		for($i = $first; $i < $counter; $i++){
			echo $mitglied_zeile[$i];
		}
		echo "</table>";
		echo "</div>";
	}

	
	//Ausgabe der Parteienliste als Buttons auf der Website
	function parteienliste_laden()
	{
		$ergebnis = $this->parteien_auslesen();
		$image = '';
		
		echo "<form method='post' action='election.php'>";
		while($row = mysqli_fetch_array($ergebnis)){
			if(isset($_SESSION['vote'])){
				$partei = "partei_".$row['id'];
				if(in_array($partei, $_SESSION['vote'])){
					$image= "<img src='bilder/buttonIconPartyVoted.gif' height='15'>";	//Wenn eine Partei gew�hlt wurde, wird ein das Bild auf "Party Voted" gesetzt
				} else{
					foreach($_SESSION['vote'] as $vote){
						if(explode("_", $vote)[0] != 'invalid'){
							if((explode("_", $vote)[0] == 'member') && (explode("_", $vote)[1] == $row['id'])){
							$image= "<img src='bilder/buttonIconCandidateVoted.gif' height='15'>";  //Wenn Kandidaten einer Partei gew�hlt wurden, wird ein das Bild auf "Candiate Voted" gesetzt
							break;
						}else
							$image='';
						}
					}
				}
			}
		
			//Ausgabe der w�hlbaren Parteien als Buttons
			if(isset($_POST['partei']) && ($_POST['partei']== $row['id'])){
				echo "<button type='submit' name='partei' value='".$row['id']."' class='button_aktiv'>".$image." ".$row['id'] . " " . $row['partei']."</button>";
			}
			else{ 
				echo "<button type='submit' name='partei' value='".$row['id']."' class='button'>".$image." ".$row['id'] . " " . $row['partei']."</button>";
			}

		}

		echo "</form>";
	}
	
	
	// Liest den vollst�ndigen Namen eines Mitgliedes anhand seiner ID aus der Datenbank aus und gibt ihn zur�ck
	function get_mitglied($member_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT name, vorname FROM mitglieder WHERE id = " . $member_id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		$ergebnis = mysqli_fetch_row($result);
		$member = $ergebnis[0] . ", ".$ergebnis[1];

		mysqli_close($connect);
		
		return($member);
	}
	
	// Bereitet die Abgabe der Wahl vor, indem der Session 'wahl' entweder die aktuellen Stimmen oder der Wert 'invalid' �bergeben werden
	function wahl_abgeben(){
		if(isset($_SESSION['vote'])){
			$_SESSION['wahl'] = array_unique($_SESSION['vote']);
		}else{
			$_SESSION['wahl'] = 'invalid';
			$_SESSION['alert'] = 5;
		}
	}
	
	// Wenn eine Stimme hinzugef�gt oder gel�scht wird, wird in dieser Funktion die Wahl angepasst
	function stimme_bearbeiten(){
		/* 	Wenn auf den Namen eines Kandidaten geklickt wird, wird �berpr�ft, ob dieser schon gestrichen wurde.
			Ist dies der Fall, wird das Streichen des Kandidaten r�ckg�ngig gemacht, indem er aus der Liste der gestrichenen Kandidaten entfernt wird.
			Andernfalls wird er zu der Liste der gestrichenen Kandidaten hinzugef�gt*/
		if(isset($_SESSION['deleted']) && in_array($_POST['vote_changed'], $_SESSION['deleted'])){
			$i = array_search($_POST['vote_changed'], $_SESSION['deleted']);
			unset($_SESSION['deleted'][$i]);	
		} else{
			$_SESSION['deleted'][] = $_POST['vote_changed'];
		}
		
		// Setzt die aktuelle Partei auf die Partei, in der eine �nderung vorgenommen wurde
		$_POST['partei'] = explode("_", $_POST['vote_changed'])[1];
		
		/* 	Wenn Stimmen vergeben wurden und der gedr�ckte Button schon in der Liste der gew�hlten Kandidaten auftaucht, 
			wird diese Stimme aus der Liste der gew�hlten Kandidaten entfernt.
			Wurde noch keine Stimme vergeben, wird die neue Stimme zu der Liste der gew�hlten Kandidaten hinzugef�gt
		*/
		if(isset($_SESSION['vote'])){
			$_SESSION['vote'] = array_unique($_SESSION['vote']);
			
			if(in_array($_POST['vote_changed'], $_SESSION['vote'])){
				$i = array_search($_POST['vote_changed'], $_SESSION['vote']);
				unset($_SESSION['vote'][$i]);
			}
		}else{
			$_SESSION['vote'][] = $_POST['vote_changed'];
			$_SESSION['vote'] = array_unique($_SESSION['vote']);
		}
		
		// Wenn es eine Liste der gel�schten Kandidaten gitb, wird sie zu der Liste der gew�hlten Kandidaten hinzugef�gt. Anschlie�end werden doppelte Eintr�ge entfernt
		if(isset($_SESSION['deleted'])){
			$_SESSION['vote'] = array_merge($_SESSION['deleted'], $_SESSION['vote']) ;
		}
		
		$_SESSION['vote'] = array_unique($_SESSION['vote']);
		
	}
	
	// Liefert ein Array mit Strings aller Parteien und Mitglieder zur�ck
	function get_search_value(){
		$parteienliste = $this->parteien_auslesen();
		
		//Liest alle Parteien aus und speichert sie in einem Array
		$count_parteien = 0;
		while($row =  mysqli_fetch_array($parteienliste)){
			$party = str_replace(" ", "&nbsp;", $row['partei']);
			$party_and_members[] = $row['id'].":&nbsp;".$party;
			$count_parteien++;
		}
		
		//Liest f�r alle Parteien alle Mitglieder aller Parteien
		for($i = 0; $i <= $count_parteien; $i++){
			$mitgliederliste = $this->mitglieder_auslesen($i);
			while($row2 =  mysqli_fetch_array($mitgliederliste)){
				$name = str_replace(" ", "&nbsp;", $row2['name']);
				$vorname = str_replace(" ", "&nbsp;", $row2['vorname']);
				$partei = str_replace(" ", "&nbsp;", $this->get_partei($row2['partei']));
				$party_and_members[] = $row2['id'].":&nbsp;".$name.",&nbsp;".$vorname."&nbsp;(".$partei.")";
			}
		}
		
		return $party_and_members;
	}
	
	//Bekommt einen Suchtext und markiert die Partei oder den Kandidaten
	function suche($suche){
		$_SESSION['fehler_suche'] = false;
		$suchanfrage = explode(":", $_POST['search']); //Der String wird an dem Doppelpunkt geteilt
		if(preg_match('/^\d{1,4}$/', $suchanfrage[0])){ //Es wird �berpr�ft, ob es sich bei dem ersten Teil des Strings um eine ID handelt
			if(!empty($suchanfrage[1])){	//Es wird �berpr�ft, ob der String nach dem Doppelpunkt (wenn es einen gibt) nicht leer ist
				$corrected_string = trim(str_replace(chr(194).chr(160), ' ', $suchanfrage[1]));
				/* 	Es wird getestet, ob die Suchanfrage einer Partei entspricht. Ist dies der Fall, wird der Suchstring mit dem Parteinamen verglichen.
					Nur wenn der Parteiname und der String gleich sind, wird die gesuchte Partei ge�ffnet */
				if($this->is_party($suchanfrage[0])){	 
					if($corrected_string == $this->get_partei($suchanfrage[0])){
						$_POST['partei'] = $suchanfrage[0];
						unset($_SESSION['string_searching']);
					}else{
						$_SESSION['fehler_suche'] = true;
					}	
				}
				/* 	Wenn es sich bei der Suchanfrage um einen Kandidaten handelt, wird getestet, ob der Suchstring einem Mitglie einer Partei entspricht.
					Daraufhin wird die Partei des Kandidaten ge�ffnet und die Suchanfrage einer Session hinzugef�gt.*/
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
	
	//Diese Methode bekommt einen Namen �bergeben und testet, ob es sich dabei um eine Partei handelt
	function is_party($name){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());

		$sql = "SELECT COUNT(*) FROM parteien WHERE id = '".$name."'";
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		mysqli_close($connect);
		
		mysqli_fetch_row($result)[0]==1 ?  $party = true : $party = false;
		return $party;
	}

	//Diese Methode bekommt einen Namen �bergeben und testet, ob es sich dabei um ein Mitglied einer Partei handelt
	function is_member($id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());

		$sql = "SELECT COUNT(*) FROM mitglieder WHERE id = ".$id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		mysqli_close($connect);
		
		mysqli_fetch_row($result)[0]==1 ?  $member = true : $member = false;
		return $member;
	}
	
	// Erstellt aus $_SESSION['wahl'] den Wahlcode mit der Wahl-ID, ob die Wahl g�ltig ist, der gew�hlten Partei und den gew�hlten und gestrichenen Kandidaten
	function wahlcode_erstellen(){
		$wahl_aktuell = array();
		$deleted_members = array();
		$other_parties = array();
		$partei = '';
		$number_of_parties = 0;	
		$votes_counter = 0;
		//Wenn die Wahl ung�ltig ist, setze $_SESSION['gueltig'] auf 0
		if(isset($_SESSION['wahl']) && $_SESSION['wahl'] == "invalid"){
			$_SESSION['gueltig'] = 0;
		}
		//Wenn die Wahl gesetzt wurde und nicht ung�tlig ist, erstelle den Wahlcode
		if(isset($_SESSION['wahl']) && $_SESSION['wahl'] != "invalid"){
			// Iterieren �ber die Wahl und Z�hlen der gew�hlten Parteien. Setze $partei auf die gefundene Partei
			foreach($_SESSION['wahl'] as $wahl){
				if(explode("_", $wahl)[0] == 'partei'){
					$partei = $wahl;
					$number_of_parties++;
				}
			}
			
			
			foreach($_SESSION['wahl'] as $wahl){
				$vote = explode("_", $wahl);
				$one_party = false;
				if($number_of_parties == 1){
					$one_party = ($vote[1] == explode("_", $partei)[1]); //Wenn eine Partei gew�hlt wurde und der aktuelle String Mitglied dieser Partei ist, setze one_party auf true
				}else if($number_of_parties == 0){
					$one_party = false;	//Wenn keine Partei gew�hlt wurde, setze $one_party auf false
				}else{
					$partei = ''; // Wenn mehr als eine Partei gew�hlt wurde, setze $partei auf einen leeren String
				}
				
				
				/*	Testet ob genau eine Partei gew�hlt wurde und ob die aktuelle Stimme ein Mitglied dieser Partei ist.
					Wenn die Stimme vorhanden ist und es keine gr��ere Stimme gibt (z.B. wenn erst eine und dann die zweite Stimme vergeben wurde), 
					wird die Stimme dem Array wahl_aktuell hinzugef�gt.
					Andernfalls wird ebenfalls getestet, ob die aktuelle Stimme vorhanden ist, wird aber dem Array $other_parties hinzugef�gt*/
				if($one_party){
					if($vote[0] == 'member'){
						$tmp0 = $vote[0]."_".$vote[1]."_".$vote[2]."_0";
						$tmp1 = $vote[0]."_".$vote[1]."_".$vote[2]."_1";
						$tmp2 = $vote[0]."_".$vote[1]."_".$vote[2]."_2";
						$tmp3 = $vote[0]."_".$vote[1]."_".$vote[2]."_3";
							
						if(($wahl == $tmp1) && !(in_array($tmp0, $_SESSION['wahl']) || in_array($tmp2, $_SESSION['wahl']) || in_array($tmp3, $_SESSION['wahl']))){
							$wahl_aktuell[] = $wahl;
							$votes_counter += 1;
						}else if(($wahl == $tmp2) && !(in_array($tmp0, $_SESSION['wahl']) || in_array($tmp3, $_SESSION['wahl']))){
							$wahl_aktuell[] = $wahl;
							$votes_counter += 2;
						}else if(($wahl == $tmp3) && !(in_array($tmp0, $_SESSION['wahl']))){
							$wahl_aktuell[] = $wahl;
							$votes_counter += 3;
						}else if($wahl == $tmp0){
							$deleted_members[] = $wahl; //Die gestrichenen Mitglieder werden nur gez�hlt, wenn sie in einer �ber die Listenstimme gew�hlten Partei enthalten sind
						}
					}
				}else{
					if($vote[0] == 'member'){
						$tmp0 = $vote[0]."_".$vote[1]."_".$vote[2]."_0";
						$tmp1 = $vote[0]."_".$vote[1]."_".$vote[2]."_1";
						$tmp2 = $vote[0]."_".$vote[1]."_".$vote[2]."_2";
						$tmp3 = $vote[0]."_".$vote[1]."_".$vote[2]."_3";
							
						if(($wahl == $tmp1) && !(in_array($tmp0, $_SESSION['wahl']) || in_array($tmp2, $_SESSION['wahl']) || in_array($tmp3, $_SESSION['wahl']))){
							$other_parties[] = $wahl;
							$votes_counter += 1;
						}else if(($wahl == $tmp2) && !(in_array($tmp0, $_SESSION['wahl']) || in_array($tmp3, $_SESSION['wahl']))){
							$other_parties[] = $wahl;
							$votes_counter += 2;
						}else if(($wahl == $tmp3) && !(in_array($tmp0, $_SESSION['wahl']))){
							$other_parties[] = $wahl;
							$votes_counter += 3;
						}
					}
				}
			}
		}
		
		//Wenn genau eine Partei gew�hlt wurde, bringe die Partei-ID in das korrekte Format, andernfalls setze $partei auf '00'
		if($partei != ''){
			$partei = sprintf("%02d", explode("_",$partei)[1]);
		}else{
			$partei = '00';
		}
		
		//Sortieren der Arrays
		sort($wahl_aktuell);
		sort($other_parties);

		$max = $this->MaxStimmen_auslesen();
		
		//Wenn eine Partei gew�hlt wurde und mehr Stimmen vergeben wurden, wird �berpr�ft, ob eine oder keine Partei gew�hlt wurde
		if($votes_counter > $max && $_SESSION['alert'] == 3){
			if($number_of_parties == 1 && sizeof($wahl_aktuell) > 0){
				$wahl_aktuell = $this->heilung($max, $wahl_aktuell, $votes_counter); //Wenn mehr als 0 Stimmen an die gew�hlte Partei vergeben wurden, wende Heilung auf das Array wahl_aktuell an
			}else{
				$other_parties = $this->heilung($max, $other_parties, $votes_counter); //Wenn keine PArtei gew�hlt wurde, oder die gew�hlte Partei keine Stimmen enth�lt, wende Heilung auf das Array other_parties an
			}
		}
		
		$members = '';
		$members_deleted = '';
		$members_other = '';
		//Bilde einen String der gew�hlten Mitglieder der gew�hlten Partei, wobei diese zuvor in das korrekte Format gebracht werden
		foreach($wahl_aktuell as $aktuell){
			$members .= sprintf("%04d", explode("_",$aktuell)[2]).explode("_",$aktuell)[3];
		}
		//Bilde einen String der gew�hlten Mitglieder aus anderen Parteien, wobei diese zuvor in das korrekte Format gebracht werden
		foreach($other_parties as $other){
			$members_other .= sprintf("%04d", explode("_",$other)[2]).explode("_",$other)[3];
		}
		//Bilde einen String der gestrichenen Mitglieder, wobei diese zuvor in das korrekte Format gebracht werden
		foreach($deleted_members as $deleted){
			$members_deleted .= sprintf("%04d", explode("_",$deleted)[2]);
		}
		
		//Gebe den gebildeten Wahlcode zur�ck
		return $_SESSION['aktive_wahl'].$_SESSION['gueltig'].$partei.$members.$members_other."_".$members_deleted;
	}
	
	// Wenn zu viele Stimmen aus einer Partei gew�hlt wurden, muss Heilung angewandt werden, um die �berfl�ssigen Stimmen zu entfernen
	function heilung($max, $member_votes, $votes_counter){
		
		$one_votes = array();
		$two_votes = array();
		$three_votes = array();
		
		//Erstelle auf dem �bergebenen Array drei Arrays, die in die Stimmenanzahl aufgeteilt werden
		foreach($member_votes as $vote){
			if(explode("_", $vote)[3] == '1'){
				$one_votes[] = $vote;
			}
			if(explode("_", $vote)[3] == '2'){
				$two_votes[] = $vote;
			}
			if(explode("_", $vote)[3] == '3'){
				$three_votes[] = $vote;
			}
		}
		
		/* 	Solange die aktuell vergebenen Stimmen gr��er sind als die maximale Anzahl der zu vergebenen Stimmen,
			entferne von hinten nach vorne eine Stimme nach der anderen, bis die Anzahl der Stimmen korrekt ist*/
		while($votes_counter > $max){
			
			/*	Sortiere die Stimmen mit nur einer Stimme und kehre die Reihenfolge seiner Elemente um. 
				Solange es Stimmen mit einer Stimme gibt und die maximale Anzahl nicht erreicht wurde, wird die Stimme aus dem Array entfernt*/
			sort($one_votes);
			$one_votes = array_reverse($one_votes);
			foreach($one_votes as $vote){
				if($votes_counter == $max){
					break;
				}else{
					$i = array_search($vote, $one_votes);
					unset($one_votes[$i]);
					$votes_counter--;
				}
			}
			
			/*	Sortiere die Stimmen mit zwei Stimmen und kehre die Reihenfolge seiner Elemente um. 
				Solange es Stimmen mit zwei Stimmen gibt und die maximale Anzahl nicht erreicht wurde, wird die Stimme aus dem Array entfernt
				und zu der Liste mit einer Stimme hinzugef�gt*/
			sort($two_votes);
			$two_votes = array_reverse($two_votes);
			foreach($two_votes as $vote){
				if($votes_counter == $max){
					break;
				}else{
					$i = array_search($vote, $two_votes);
					unset($two_votes[$i]);
					$one_votes[] = substr($vote, 0, -1)."1";
					$votes_counter--;
				}
			}
			
			/*	Sortiere die Stimmen mit drei Stimmen und kehre die Reihenfolge seiner Elemente um. 
				Solange es Stimmen mit drei Stimmen gibt und die maximale Anzahl nicht erreicht wurde, wird die Stimme aus dem Array entfernt
				und zu der Liste mit zwei Stimmen hinzugef�gt*/
			sort($three_votes);
			$three_votes = array_reverse($three_votes);
			foreach($three_votes as $vote){
				if($votes_counter == $max){
					break;
				}else{
					$i = array_search($vote, $three_votes);
					unset($three_votes[$i]);
					$two_votes[] = substr($vote, 0, -1)."2";
					$votes_counter--;
				}
			}
		}
		
		//Mergen der einzelnen Array und Sortieren des resultierenden Arrays.  
		$member_votes = array_merge($one_votes, $two_votes, $three_votes);
		sort($member_votes);
		
		//R�ckgabe der geheilten Wahl
		return $member_votes;
	}
	
	/*	Bekommt die Partei-ID einer Partei, eine Liste mit gew�hlten Kandidaten, eine Liste mit gestrichenen Kandidaten
		und die noch implizit zu vergebenden Stimmen
		Die Eintr�ge der Liste der gew�hlten bzw. der gestrichenen Kandidaten hat die Form xxxx_y, wobei xxxx die ID
		des Kandidaten ist und y die Anzahl der vergebenen Stimmen.
		Es wird eine Liste mit Eintr�gen der Form xxxx_yz zur�ckgegeben, wobei z die Anzahl der zus�tzlich vergebenen impliziten Stimmen beschreibt*/
	function pdf_votes($partei_id, $voted_members, $deleted_members, $max_votes){
		$member_votes = array();	
		$number_members = $this->count_members($partei_id);
		
		$number = intval($partei_id) * 100 + $number_members;
		
		/* 	Erste Spalte:
			Iteriert �ber alle Kandidaten einer Liste und f�gt jedem Kandidaten, der keine, eine oder zwei Stimmen hat, eine implizite Stimme hinzu*/
		for($i = intval($partei_id) * 100 + 1; $i <= $number; $i++){	
			$tmp0 = sprintf("%04d", $i).'_0';
			$tmp1 = sprintf("%04d", $i).'_1';
			$tmp2 = sprintf("%04d", $i).'_2';
			$tmp3 = sprintf("%04d", $i).'_3';
			
			//Wenn der Kandidat nicht gestrichen wurde und es noch implizite Stimmen gibt
			if(!(in_array($tmp0, $deleted_members))){
				if($max_votes > 0){
					if(in_array($tmp1, $voted_members)){
						$member_votes[] = $tmp1."1";	//Wenn der Kandidat eine Stimme hat, f�ge eine implizite Stimme hinzu -> xxxx_11
						$max_votes = $max_votes - 1;
					}else if(in_array($tmp2, $voted_members)){
						$member_votes[] = $tmp2."1";	//Wenn der Kandidat zwei Stimmen hat, f�ge eine implizite Stimme hinzu -> xxxx_21
						$max_votes = $max_votes - 1;
					}else if(in_array($tmp3, $voted_members)){
						$member_votes[] = $tmp3."0";	//Wenn der Kandidat drei Stimmen hat, f�ge keine implizite Stimme hinzu -> xxxx_30
					}else{
						$member_votes[] = $tmp0."1";	//Wenn der Kandidat keine Stimme hat, f�ge eine implizite Stimme hinzu -> xxxx_01
						$max_votes = $max_votes - 1;
					}
				}else{	//Wenn keine impliziten Stimmen mehr vorhanden sind, wird weiter �ber die Liste iteriert, um die restlichen Stimmen an das neue Format anzupassen
					if(in_array($tmp1, $voted_members)){
						$member_votes[] = $tmp1."0";	//Kandidaten mit einer Stimme -> xxxx_10
					}else if(in_array($tmp2, $voted_members)){
						$member_votes[] = $tmp2."0";	//Kandidaten mit zwei Stimmen -> xxxx_20
					}else if(in_array($tmp3, $voted_members)){
						$member_votes[] = $tmp3."0";	//Kandidaten mit drei Stimmen -> xxxx_30
					}
				}
			}
		}	

		/* 	Zweite Spalte:
			K�nnen noch weitere implizite Stimmen vergeben werden, wird �ber alle Kandidaten einer Liste iteriert und jedem Kandidaten, der keine oder eine Stimme hat, eine implizite Stimme hinzugef�gt*/
		for($i = intval($partei_id) * 100 + 1; $i <= $number; $i++){
			if($max_votes == 0) {break;}
			$tmp0 = sprintf("%04d", $i).'_0';
			$tmp1 = sprintf("%04d", $i).'_1';
			$tmp2 = sprintf("%04d", $i).'_2';
			$tmp3 = sprintf("%04d", $i).'_3';
			
			//Wenn der Kandidat nicht gestrichen wurde, und keine oder eine Stimme hat, wird der alte Eintrag aus dem Array gestrichen und ein aktualisierter Eintrag hinzugef�gt
			if(!(in_array($tmp0, $deleted_members))){
				if(in_array($tmp0."1", $member_votes)){
					$i = array_search($tmp0.'1', $member_votes);
					unset($member_votes[$i]);		//Entferne Eintrag der Form xxxx_01
					$member_votes[] = $tmp0.'2';	//F�ge Eintrag der Form xxxx_02 hinzu
					$max_votes = $max_votes - 1;
				} else if(in_array($tmp1."1", $member_votes)){
					$i = array_search($tmp1.'1', $member_votes);
					unset($member_votes[$i]);		//Entferne Eintrag der Form xxxx_11
					$member_votes[] = $tmp1.'2';	//F�ge Eintrag der Form xxxx_12 hinzu
					$max_votes = $max_votes - 1;
				}
			}
		}
		
		/* 	Dritte Spalte:
			K�nnen noch weitere implizite Stimmen vergeben werden, wird �ber alle Kandidaten einer Liste iteriert und jedem Kandidaten, der keine Stimme hat, eine implizite Stimme hinzugef�gt*/
		for($i = intval($partei_id) * 100 + 1; $i <= $number; $i++){
			if($max_votes == 0) {break;}
			$tmp0 = sprintf("%04d", $i).'_0';
			$tmp1 = sprintf("%04d", $i).'_1';
			$tmp2 = sprintf("%04d", $i).'_2';
			$tmp3 = sprintf("%04d", $i).'_3';
			
			//Wenn der Kandidat nicht gestrichen wurde, und keine Stimme hat, wird der alte Eintrag aus dem Array gestrichen und ein aktualisierter Eintrag hinzugef�gt
			if(!(in_array($tmp0, $deleted_members))){
				if(in_array($tmp0."2", $member_votes)){
					$i = array_search($tmp0.'2', $member_votes);
					unset($member_votes[$i]);		//Entferne Eintrag der Form xxxx_02
					$member_votes[] = $tmp0.'3';	//F�ge Eintrag der Form xxxx_03 hinzu
					$max_votes = $max_votes - 1;
				}
			}
		}
		
		// Liefert die neue Liste mit den aktualisierten Stimmen zur�ck
		sort($member_votes);	
		return $member_votes;
	}
	
	
	// Bei jedem Aufruf dieser Methode wird die aktuelle Wahl analysiert und die korrekte Anzeige im Header erzeugt
	function check_rules(){

		$corrected_list = array();
		$partei = 0;
		$one_party = true;
		
		$chosen_party = '';
		if(isset($_SESSION['vote']) && !empty($_SESSION['vote'])){
			$aktuell = $_SESSION['vote'];
			//Iteriert �ber alle Eintr�ge der Session und schreibt nur Eintr�ge in das Array corrected_list, die noch nicht vorhanden sind
			foreach($aktuell as $tmp){
				if(!in_array($tmp, $corrected_list)){
					$corrected_list[] = $tmp;
				}
			}
			
			//Iteriert �ber das korrigierte Array und z�hlt die Stimmen, die an Parteien vergeben wurden. Zus�tzlich wird die ID der gew�hlten Partei gespeichert
			$abort = false;
			foreach($corrected_list as $vote){
				if(explode("_", $vote)[0] == "partei"){
					$partei = $partei + 1;
					$chosen_party = explode("_", $vote)[1];
				}
			}
			
			/*	Iteriert �ber das korrigierte Array und �berpr�ft, ob Mitglieder einer oder mehrerer Parteien gew�hlt wurden. Die Variable one_party ist true, wenn nur
				Kandidaten einer Partei gew�hlt wurden, sonst false*/
			$tmp = '';
			foreach($corrected_list as $vote){
				if((explode("_", $vote)[0] == 'member') && (explode("_", $vote)[1] != $tmp) && (explode("_", $vote)[3] != '0') && !$abort){
					if($tmp == ''){
						$tmp = explode("_", $vote)[1];
					}else{
						$one_party = false;
						$abort = true;
					}
				}
			}
		}
		
		
		$member = $this->voted_counter($corrected_list);	//Anzahl der insgesamt vergebenen Stimmen in dem korrigierten Array
		$maxstimmen = $this->MaxStimmen_auslesen();			//Anzahl der maximal zu vergebenen Stimmen
		
		$max_party = 0;
		$deleted = 0;
		$votes_members = 0;
		//Wenn exakt eine Partei gew�hlt wurde, werden die Stimmen gez�hlt, die in der Partei vergeben wurden bzw. die Anzahl der gestrichenen Kandidaten
		if($partei == 1){
			$max_party = $this->count_members($chosen_party);
			
			$i = ($chosen_party * 100) + 1;
			$j = ($chosen_party * 100) + $max_party;
			for($i = 0; $i <= $j; $i++){
				$tmp0 = "member_".$chosen_party."_".$i."_0";
				$tmp1 = "member_".$chosen_party."_".$i."_1";
				$tmp2 = "member_".$chosen_party."_".$i."_2";
				$tmp3 = "member_".$chosen_party."_".$i."_3";
				
				
				if(in_array($tmp0, $corrected_list)){
					$deleted = $deleted + 1;		//Inkrementiert die Anzahl der gestrichenen Kandidaten
				}else if(in_array($tmp1, $corrected_list) && !in_array($tmp2, $corrected_list) && !in_array($tmp3, $corrected_list)){
					$votes_members = $votes_members + 1;	//Inkrementiert die Stimmen der gew�hlten Kandidaten um 1
				}else if(in_array($tmp2, $corrected_list) && !in_array($tmp3, $corrected_list)){
					$votes_members = $votes_members + 2;	//Inkrementiert die Stimmen der gew�hlten Kandidaten um 2
				}else if(in_array($tmp3, $corrected_list)){
					$votes_members = $votes_members + 3;	//Inkrementiert die Stimmen der gew�hlten Kandidaten um 3
				}
			}
		}
		
		$implicit_votes = (($max_party - $deleted) * 3) - $votes_members; //Stimmen, die in der Partei noch implizit vergeben werden k�nnen
		$max = $maxstimmen - $member;		//Stimmen, die noch vergeben werden k�nnen
		$_SESSION['implicit'] = min($implicit_votes, $max); //Beschreibt die Anzahl der noch implizit zu vergebenen Stimmen
		
		$this->regel_ausgabe($partei, $member, $maxstimmen, $one_party);
		
	}
	
	//Wertet die Wahl aus und wei�t der aktuellen Wahl anhand von definierten Regeln den korrekten Header zu
	function regel_ausgabe($partei, $member, $maxstimmen, $one_party){
		if($maxstimmen < $member){
			if($one_party){ //es wurden Kandidaten aus genau einer Liste ausgew�hlt
				$_SESSION['alert'] = 3;		//Fall 3 -> es wurden zuviele Mitglieder aus einer(!) Liste ausgew�hlt
				$_SESSION['gueltig'] = 1;
			}else{
				$_SESSION['alert'] = 4;		//Fall 4 -> es wurden zuviele Mitglieder aus mehreren(!) Listen ausgew�hlt
				$_SESSION['gueltig'] = 0;
			}
		}else if($partei == 0){
			if($member == 0){
				$_SESSION['alert'] = 5;		//Fall 5 -> Weder eine Partei, noch ein Mitglied wurde gew�hlt
				$_SESSION['gueltig'] = 0;
			}else{
				$_SESSION['alert'] = 2; 	//Fall 2 -> es wurden nur Mitglieder gew�hlt
				$_SESSION['gueltig'] = 1;
			}
		}else if($partei == 1){
			$_SESSION['alert'] = 1;		//Fall 1 -> Eine Partei wurde gew�hlt und x Stimmen an Mitglieder vergeben
			$_SESSION['gueltig'] = 1;
		}else{
			if($member > 0){
				$_SESSION['alert'] = 7;		//Fall 7 -> zuviele Parteien gew�hlt - nur Mitglieder werden gez�hlt
				$_SESSION['gueltig'] = 1;
			}else{
				$_SESSION['alert'] = 6;		//Fall 6 -> zuviele Parteien gew�hlt
				$_SESSION['gueltig'] = 0;	
			}
		}
	}
	
	//Extrahiert die relevanten Informationen aus dem Wahlcode aus und gibt sie zur�ck
	function get_infos($wahlcode){
		$x = explode("_", $wahlcode);
		$aktuelle_wahl = substr($x[0], 0, 5);			//gibt die aktuelle Wahl aus
		$gueltig = substr($x[0], 5 , 1);				//gibt an, ob die Wahl g�ltig oder ung�ltig ist
		$partei = substr($x[0], 6, 2); 					//gibt die ID der gew�hlten Partei aus
		$length_members = strlen($x[0]) - 8;			
		$length_deleted = strlen($x[1]);
			
		//Liest die IDs und die Stimmen der Kandidaten aus und speichert sie in der Form x_y in einem Array, wobei x die ID und y die Stimmen des Kandidaten sind
		$i = 8;
		$members = array();
		while($length_members > 0){
			$j = $i + 4;
			$members[] = substr($x[0], $i, 4)."_".substr($x[0], $j, 1);
			$i = $i + 5;
			$length_members = $length_members - 5;
		}
		
		$members_party = array();
		$members_other = array();
		$count_party = 0;			//Vergebene Stimmen an Kandidaten aus der gew�hlten Partei
		$count_other = 0;			//Vergebene Stimmen an Kandidaten aus anderen Parteien
		$members_party_count = 0;	//Anzahl der gew�hlten Kandidaten aus der gew�hlten Partei
		$members_other_count = 0;	//Anzahl der gew�hlten Kandidaten aus anderen Parteien
		//Teilt die ausgelesenen Kandidaten in Kandidaten der Partei und Kandidaten anderer Parteien auf
		foreach($members as $member){
			if(substr($member, 0, 2) == $partei){	//Wenn der Kandidat zu der gew�hlten Partei geh�rt, f�ge ihn zum Array members_party hinzu und inkrementiere members_party_count und count_party
				$members_party[] = $member;
				$count_party = $count_party + intval(explode("_",$member)[1]);
				$members_party_count++;
			}else{		//Wenn der Kandidat nicht zu der gew�hlten Partei geh�rt, f�ge ihn zum Array members_other hinzu und inkrementiere members_other_count und count_other
				$members_other[] = $member;
				$count_other = $count_other + intval(explode("_",$member)[1]);
				$members_other_count++;
			}
		}
			
		$i=0;
		$members_deleted = array();
		$members_deleted_count = 0;
		//Erstelle ein Array mit den gestrichenen Kandidaten und berechne die Anzahl der gestrichenen Kandidaten
		while($length_deleted > 0){
			$members_deleted[] = substr($x[1], $i, 4)."_0";
			$i = $i + 4;
			$length_deleted = $length_deleted - 4;
			$members_deleted_count++;
		}
		
		//Anzahl der Kandidaten der Liste der gew�hlten Partei
		$members_party_total = $this->count_members($partei);

		//Maximal zu vergebene Stimmen
		$max_votes = $this->Maxstimmen_auslesen();
		
		//Aktuelle Wahl
		$aktuelleWahl = "Kommunalwahl";
		
		
		$count_total = $count_party + $count_other;	//direkt Vergebene Stimmen
		$count_indirect = $max_votes - $count_total;	
		//Listenstimmen bzw. nicht vergebene Stimmen
		$count_list = 0;
		$not_voted = 0;
		//Berechnet die Anzahl der impliziten Stimmen, die noch auf die gew�hlte Partei verteilt werden k�nnen
		if($partei != 0){
			if(($max_votes - $count_total) > (3*($members_party_total - $members_deleted_count))){
				$count_list = min((($members_party_total - $members_deleted_count)*3-$count_party),($max_votes - $count_total));
				$not_voted = max(0, $max_votes - $count_total - $count_list);
			}else{
				$not_voted = 0;
				$count_list = max(0, $max_votes - $count_total);
			}
		}else{
			$count_list = 0;
			$not_voted = max(0, $max_votes - $count_total);
		}
		
		//Berechnet die implizit zu vergebenden Stimmen f�r jeden Kandidaten, also xxxx_y -> xxxx_yz 
		$members_party_indirect = array();
		$members_party_indirect = $this->pdf_votes($partei, $members_party, $members_deleted, $count_indirect);
		
		//Bringt die Kandidaten anderer Parteien in die gleiche Form, wie die gew�hlten Kandidaten der gew�hlten Partei, also xxxx_y -> xxxx_y0
		$members_other_corrected = array();
		foreach($members_other as $member){
			$members_other_corrected[] = $member."0";
		}
		
		return array($partei, $gueltig, $members_party_indirect, $members_other_corrected, $members_deleted_count, $members_deleted, $max_votes, $count_total, $count_list, $not_voted, $aktuelleWahl, $count_party);

	}
	
	//Erh�lt die Stimmen, die ein Kandidat erhalten hat und gibt das korrekte Bild zu dieser Wahl zur�ck
	function get_image($votes){
		switch($votes)
		{
			case("01"):
				$image = "bilder/init1vote.png";
				break;
			case("02"):
				$image = "bilder/init2vote.png";
				break;
			case("03"):
				$image = "bilder/init3vote.png";
				break;
			case("10"):
				$image = "bilder/init1voteColoured1.png";
				break;
			case("11"):
				$image = "bilder/init2voteColoured1.png";
				break;
			case("12"):
				$image = "bilder/init3voteColoured1.png";
				break;
			case("20"):
				$image = "bilder/init2voteColoured2.png";
				break;
			case("21"):
				$image = "bilder/init3voteColoured2.png";
				break;
			case("30"):
				$image = "bilder/init3voteColoured3.png";
				break;
			default:
				$image = "ERROR";
				break;
		}
		return $image;
	}
	
	//Erstellt das PDF aus dem �bergebenen Wahlcode
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
			
			//Bausteine f�r die verschiedenen Varianten der Info Boxen
			$text0 = "Der Stimmzettel ist ung�ltig, da die Schaltfl�che \n >Ung�ltig w�hlen< gedr�ckt wurde\n\n\n";
			$text1_6  = "Der Stimmzettel ist ung�ltig, da keine Stimme vergeben wurde.\n\n\n";
			$text4_41 = "Hinweis: Es wurden mehr als $max_votes Stimmen in einer Liste vergeben, daher wurden Stimmen abgeschnitten. Der z�hlende Teil Ihrer Stimme ist untenstehend Abgebildet.\n\n";
			$text2_3_8  = "$max_votes Stimmen stehen insgesamt zur Verf�gung. \nDirekt vergebene Stimmen: $count_total \n�ber die Kopfstimme (Listenstimme) vergebene Stimmen: $count_list \nNicht vergebene Stimmen: $not_voted";
			$text5 = "Der Stimmzettel ist ung�ltig, da Sie mehr als $max_votes Stimmen vergeben haben.\n\n\n";
			$text7 = "Ihre Stimme ist ung�ltig. Sie haben mehr als eine Kopfstimme vergeben.\n\n\n";
			
			// Kopfbereich des Stimmzettels
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',30);
			$pdf->Cell(60, 10, "Stimmzettel");
			$pdf->ln();
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(150,7,"$aktuelleWahl der Wissenschaftsstadt Darmstadt am $date");
			$pdf->SetX(171);
			$pdf->SetFont('Arial','',8);
			$pdf->MultiCell(25, 3,"QR-Code \nzur automat. Ausz�hlung","","l");
			$pdf->SetFont('Arial','',10);
			
			
			//Feld f�r die m�gliche Listenstimme bzw. Hinweise
			//Mittleres Textfeld
			switch($number)
			{
				//Fall 0: "Der Stimmzettel ist ung�ltig, da die Schaltfl�che >Ung�ltig w�hlen< gedr�ckt wurde".
				case("0"):
					$pdf->Cell(57, 24,"Ung�ltig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text0", 1);
					break;
				//Fall 2: "Eine Partei wurde gew�hlt und x Kandidaten. Insgesammt gibt es y Stimmen
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
				//Fall 3: "Es wurde keine Partei, sondern nur x Kandidaten gew�hlt
				case("2"):
					$pdf->Cell(57, 24,"Keine Liste gew�hlt",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text2_3_8", 1);
					break;
				//Fall 4 bzw 4*: Es wurden x Kandidaten aus EINER Liste gew�hlt, wobei x>maxStimmen ist.
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
				//Fall 5: Es wurden x Kandidaten aus mehreren Listen gew�hlt.
				case("4"):
					$pdf->Cell(57, 24,"Ung�ltig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text5", 1);
					break;
				//Fall 6: Alle gestzen Stimmen wurden entfernt.
				case("5"):
					$pdf->Cell(57, 24,"Ung�ltig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text1_6", 1);
					break;
				//Fall 7: Es wurden 2 oder mehr Parteien angekreuzt, aber KEINE Stimmen an einzelne Kandidaten vergeben.
				case("6"):
					$pdf->Cell(57, 24,"Ung�ltig",1);
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
				//Fall 1: "Zu Beginn - keine Stimme vergeben bzw. nach "Eingabe l�schen"
				default:
					$pdf->Cell(57, 24,"Ung�ltig",1);
					$pdf->SetX(69);
					$pdf->MultiCell(100, 6,"$text1_6", 1);
					break;
			}

			// Feld f�r den QR-Code
			$pdf->SetXY(171, 29);
			$pdf->Cell(25,24,$pdf->Image("http://localhost/EasyVote/qrcode.php/?id=$qr_code",$pdf->getX()+1,$pdf->getY()+1,23,23,'PNG'), 1);
			
			//Aufbau der einzelstimmen bzw. gestrichenen Kandidaten
			//Stimmzettel ung�ltig?
			$pdf->SetY(55);

			// Alle Stimmen zusammnf�gen und sortieren
			$members_other = array_merge($members_party_indirect, $members_other);
			sort($members_other);
		
			$tmp = '';
			$members_other_parties = array();
			$votes_all = array();
			$votes = 0;
			// Zuordnung der Partei zu den Mitgliedern und Z�hlen der Stimmen pro Partei
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
			// Splitten der Anzeige der abgegebenen Stimmen falls n�tig
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
					$pdf->MultiCell(34, 3.5, "In der gew�hlten Liste gestrichene Kandidaten",1, 2);
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
	
	//Zeigt die Vorschau der Wahl auf der letzten Seite an
	function show_wahl($wahlcode){
		
		//Berechnung der Werte aus dem Wahlcode
		$tmp = $this->get_infos($wahlcode);
		
		$partei = $tmp[0];
		$gueltig = $tmp[1];
		$members_party_indirect = $tmp[2];
		$members_other = $tmp[3];
		$members_deleted = $tmp[5];
		$invalid = "";
		//Erzeugt die korrekte Ausgabe, falls die Wahl ung�ltig ist
		if($gueltig == '0'){
			$invalid = ($_SESSION['lang'] == 'de/') ? "Ung&uuml;ltig" : "Invalid";
		}
		
		//Erstellt die �berschrift mit der Grafik, der gew�hlten Partei bzw. dem String invalid und dem QR-Code
		echo "<table width = 95%>";
		if($partei != '00' && $gueltig == '1'){
			echo "<tr><td width = 50px><img src='bilder/initvoteColoured.png'></td><td><h1>".$this->get_partei($partei)."</h1></td><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100"/>'."</td></tr>";
		}else if($gueltig == '0'){
			echo "<tr><td width = 50px><img src='bilder/initvote.png'></td><td><h1>$invalid</h1></td><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100" />'."</td></tr>";
		}else{
			echo "<tr><td width = 50px><img src='bilder/initvote.png'></td><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100" />'."</td></tr>";
		}
		echo "</table>";
		echo "<hr />";
		
		
		//Merged alle gew�hlten Kandidaten und sortiert das resultierende Array
		$members_other = array_merge($members_party_indirect, $members_other);
		sort($members_other);
		
		//Iteriert �ber alle gew�hlten Mitglieder und erzeugt einen neuen Eintrag f�r jede gew�hlte Partei
		$tmp = '';
		$members_other_parties = array();
		foreach($members_other as $member){
			$member_data = explode("_", $member);
			$member_id = intval($member_data[0]);
			$party_id = floor($member_id / 100);
			if($tmp != $party_id){		//Wenn der Kandidat ein Mitglied einer anderen Partei, als die bisherige ist,
				$tmp = $party_id;		//setze die Partei-ID auf die aktuelle Partei-ID
				$members_other_parties[] = "partei_".$this->get_partei($party_id);	//F�ge dem Array einen String der Form partei_x hinzu, wobei x der Name der Partei ist
			}
			$members_other_parties[] = $member;
		}
		
		//Wenn das Array gr��er als 43 ist, teile es in zwei Arrays
		if(sizeof($members_other_parties) > 43){
			if(explode("_", $members_other_parties[42])[0] == 'partei'){	//Teilt am Eintrag 42, wenn der letzte Eintrag eine Partei ist
				$part_one = array_slice($members_other_parties , 0, 42);
				$part_two = array_slice($members_other_parties , 42, sizeof($members_other_parties));
			}else{	//Ansosnten wird am Eintrag 41 geteilt
				$part_one = array_slice($members_other_parties , 0, 41);
				$part_two = array_slice($members_other_parties , 41, sizeof($members_other_parties));
			}
		}else{
			$part_one = $members_other_parties;
			$part_two = array();
		}
		
		//Erstellt die Tabellenstruktur mit der Vorschau der Wahl. Dabei wird eine Tabelle angelegt, die wiederrum drei Tabellen enth�lt
		echo "<div id='Tabelle2'>";
		echo "<table width='96%' border='0'>";
		echo "<tr valign=top><th align='left'>";
        echo "<table cellpadding='2'>";
		foreach($part_one as $member){
			$member_data = explode("_", $member);
			$member_id = intval($member_data[0]);
			if($member_id != "partei"){
				echo "<tr><td><img src='".$this->get_image($member_data[1])."'></td><td>".$this->get_mitglied($member_id)."</td><td align='right'>". $member_id ."</td></tr>";
			}else{
				echo "<tr ><td colspan='3'><b>".$member_data[1]."</b></td></tr>";
			}
		}
		echo "</table></th>";
        echo "<th  align='center'>";
        echo "<table border = '1'>";
		if(count($members_deleted) != 0){
			echo "<tr><td colspan ='3'><b>In der gew&auml;hlten Liste<br> gestrichene Kandidaten</b></td></tr>";
		}
		foreach($members_deleted as $member){
			$member_id = intval($member);
			echo "<tr><td>". $member_id ."</td><td><del>".$this->get_mitglied($member_id)."<del></td></tr>";
		}
		echo "</table></th>";
        echo "<th align='right' >";
        echo "<table cellpadding='2'>";
		foreach($part_two as $member){
			$member_data = explode("_", $member);
			$member_id = intval($member_data[0]);
			if($member_id != "partei"){
				echo "<tr><td>". $member_id ."</td><td>".$this->get_mitglied($member_id)."</td><td><img src='".$this->get_image($member_data[1])."'></td></tr>";
			}else{
				echo "<tr><td colspan='3'><b>".$member_data[1]."</b></td></tr>";
			}
		}
		echo "<tr><th colspan = '3'><th></tr>";
		echo "</table></th></tr></table>";
		echo "</div>";
	}
	
}	
?>
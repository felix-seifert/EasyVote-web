<?php

require_once('Wahl_Interface.php');

class Landtagswahl extends Wahl implements Wahl_Interface
{

	function __construct(){
		$this->xmlFile = 'wahlen/xmlFiles/landtagswahl.xml';
		$this->database = 'landtagswahl';
	}

	//Überschreibt die Funktion datenbank_erstellen() der Superklasse "Wahl"
	//Die Funktion erstellt eine spezifische Datenbank für eine Landtagswahl und fügt die Werte der spezifischen XML-Datei ein
	function datenbank_erstellen(){

		$connect = mysqli_connect($this->host, $this->username, $this->password) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");
		
		//Die Datenbank wird nur erstellt, wenn sie nicht schon vorhanden ist
		if(mysqli_select_db($connect, $this->database) == '0'){

			mysqli_query($connect, "CREATE DATABASE IF NOT EXISTS " . $this->database) or die(mysqli_error());
			mysqli_query($connect, "USE ".$this->database) or die(mysqli_error());

			//Tabelle der die Parteien
			$sql = "CREATE TABLE IF NOT EXISTS parteien (id INTEGER NOT NULL, partei VARCHAR(20), name VARCHAR(50), PRIMARY KEY(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());
			
			//Tabelle der Kandidaten der Wahlkreisstimme (Erststimme)
			$sql = "CREATE TABLE IF NOT EXISTS erststimme (id INTEGER NOT NULL, name VARCHAR(40), vorname VARCHAR(40), partei INTEGER(20), PRIMARY KEY(id), FOREIGN KEY (partei)
			   REFERENCES parteien(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());
			
			//Tabelle der Kandidaten der Landesstimme (Zweitstimme)
			$sql = "CREATE TABLE IF NOT EXISTS zweitstimme (id INTEGER NOT NULL, name VARCHAR(100), partei INTEGER(20), PRIMARY KEY(id), FOREIGN KEY (partei)
			   REFERENCES parteien(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());

			//Auslesen der XML-Datei
			if(file_exists($this->xmlFile)){
				$xml = simplexml_load_file($this->xmlFile);
					
				//Auslesen der Parteien und Einlesen in die Datenbank
				foreach($xml->parties->party as $partei)
				{
					$sql = "INSERT INTO parteien (id, partei, name) VALUES (".$partei['id'].", '".$partei['partei']."', '".$partei['name']."')";
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
				
				//Auslesen der Kandidaten
				foreach($xml->candidates->candidate as $kandidat)
				{
					$sql = "INSERT INTO erststimme (id, name, vorname, partei) VALUES (".$kandidat['id'].", '".$kandidat['name']."', '".$kandidat['prename']."', (SELECT id FROM parteien WHERE partei = '".$kandidat['partei']."'))";
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
				
				//Auslesen der Zweitstimme
				foreach($xml->party_members->member as $mitglied)
				{
					$sql = "INSERT INTO zweitstimme (id, name, partei) VALUES (".$mitglied['id'].", '".$mitglied['name']."', (SELECT id FROM parteien WHERE partei = '".$mitglied['partei']."'))";	
					mysqli_query($connect, $sql) or die(mysqli_error());
				}
			}
			else{
				exit("Fehler in m Landtag");
			}
		}

		mysqli_close($connect);
	}
	
	//Erstellt den Wahlzettel für die Landtagswahl
	function mitgliederliste_anzeigen($partei_id){
		echo "<style> #Inhalt{ min-width: 710px } #Header1{min-width: 910px }</style>";
		echo "<div class='party'>";
		if($partei_id == 0){
			$parteien = $this->parteien_auslesen();
					
			//Iteriert über alle Parteien der Landtagswahl		
			echo "<table border='1' width:90%>";
			echo "<tr><th width=45% colspan='4'>Wahlkreisstimme (Erststimme)</th><td style='width:0.5%;'></td><th width=45% colspan='4'>Landesstimme (Zweitstimme)</th></tr>";
			while($row = mysqli_fetch_array($parteien)){
			
				//Liest die Kandidaten der Erststimme (Wahlkreisstimme) der aktuellen Partei aus
				$erststimme = $this->mitglieder_auslesen($row['id'], 'erststimme');					
				$eins = $erststimme->fetch_row();
				$zwei = $erststimme->fetch_row();
				
				//Liest die Kandidaten der Zweitstimme (Landesstimme) der aktuellen Partei aus und speichert diese in einem String
				$zweitstimme = $this->mitglieder_auslesen($row['id'], 'zweitstimme');
				$members = $zweitstimme->fetch_row()[1];				
				while($member = $zweitstimme->fetch_row()){
					$members .= ", ".$member[1];
				}
				$image_wks = "<img src='bilder/BallotUnchecked50.gif'>";
				$image_ls = "<img src='bilder/BallotUnchecked50.gif'>";
				
				if(isset($_SESSION['wks'])){
					if('wks_'.$row['id'] == $_SESSION['wks']){
						$image_wks = "<img src='bilder/BallotChecked50.gif'>";
					}else{
						$image_wks = "<img src='bilder/BallotUnchecked50.gif'>";
					}
				}
				
				if(isset($_SESSION['ls'])){
					if('ls_'.$row['id'] == $_SESSION['ls']){
						$image_ls = "<img src='bilder/BallotChecked50.gif'>";
					}else{
						$image_ls = "<img src='bilder/BallotUnchecked50.gif'>";
					}
				}
				
				echo "<tr>"; 
				if($eins[1] != ''){		
					echo "<th rowspan='2'>".$row['id']."</th>";	
					echo "<th>".$eins[1].", ".$eins[2]."</th>";	
					echo "<th rowspan='2'>".$row['partei']."</th>";	
					echo "<td rowspan='2'><button type='submit' name='vote_changed' value='wks_".$row['id']."' id='wks_".$row['id']."' class='text_button'>".$image_wks."</button></td>";	
				} else {echo "<td rowspan='2' colspan='4'></td>";	}
				echo "<td rowspan='2'></td>";	
				echo "<td rowspan='2'><button type='submit' name='vote_changed' value='ls_".$row['id']."' id='ls_".$row['id']."' class='text_button'>".$image_ls."</button></td>";	
				echo "<th rowspan='2'>".$row['partei']."</th>";	
				echo "<th>".$row['name']."</th>";	
				echo "<th rowspan='2'>".$row['id']."</th>";	
				echo "</tr>";
				echo "<tr>";
				if($eins[1] != ''){echo "<td>Ersatzbewerber:<br>".$zwei[1].", ".$zwei[2]."</td>";}
				echo "<td>".$members."</td>";	
				echo "</tr>";
				} 	
			echo "</table>";
		}
		else{
			echo "Fehler beim Anzeigen der Mitgliederliste!";
		}
		echo "</div>";
	}
	
	//Liest die Mitglieder der Erststimme oder der Zweitstimme einer Partei aus
	function mitglieder_auslesen($partei, $stimme){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT * FROM ".$stimme." WHERE partei = ".$partei;
		$ergebnis = mysqli_query($connect, $sql) or die(mysqli_error());
		
		mysqli_close($connect);
		
		return $ergebnis;
	}
	
	//Liefert die Abkürzung und den vollständigen Namen einer Partei zurück
	function get_partei($partei_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT partei, name FROM parteien WHERE id = " . $partei_id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		$ergebnis = mysqli_fetch_row($result);

		mysqli_close($connect);
		
		return($ergebnis);
	}
	
	//Ausgabe des Buttons "Wahlzettel anzeigen" als Link auf der linken Navigationsleiste der Wahl
	function parteienliste_laden()
	{
		echo "<form method='post' action='election.php'>";
		
			if(isset($_POST['partei']) && ($_POST['partei']== '0')){
					echo "<li><button type='submit' name='partei' value='0' class='button_aktiv'>Wahlzettel anzeigen</button></li>";
			}
			else{ 
				echo "<li><button type='submit' name='partei' value='0' class='button'>Wahlzettel anzeigen</button></li>";
			}

		echo "</form>";
	}
	
	// Bereitet die Abgabe der Wahl vor
	function wahl_abgeben(){
		if(isset($_SESSION['wks']) && isset($_SESSION['ls'])){
			$lt_wahl[] = $_SESSION['wks'];
			$lt_wahl[] = $_SESSION['ls'];
			$_SESSION['wahl'] = $lt_wahl;
		} else if(isset($_SESSION['wks'])){
			$lt_wahl[] = $_SESSION['wks'];
			$_SESSION['wahl'] = $lt_wahl;
		} else if(isset($_SESSION['ls'])){
			$lt_wahl[] = $_SESSION['ls'];
			$_SESSION['wahl'] = $lt_wahl;
		}else{
			$_SESSION['wahl'] = 'invalid';
			$_SESSION['alert'] = 1;
		}
	}
	
	// Wenn eine Stimme geändert wird, wird in dieser Funktion die Wahl angepasst
	function stimme_bearbeiten(){
		$_POST['partei'] = 0;
			
		if(isset($_POST['vote_changed'])){			//wenn Stimmen für eine Partei abgegeben wurden
			if(explode("_", $_POST['vote_changed'])[0] == 'wks'){
				if(isset($_SESSION['wks']) && $_SESSION['wks'] == $_POST['vote_changed']){
					unset($_SESSION['wks']);
				}else{
					$_SESSION['wks'] = $_POST['vote_changed'];
				}
			}
				
			if(explode("_", $_POST['vote_changed'])[0] == 'ls'){
				if(isset($_SESSION['ls']) && $_SESSION['ls'] == $_POST['vote_changed']){
					unset($_SESSION['ls']);
				}else{
					$_SESSION['ls'] = $_POST['vote_changed'];
				}
			}
		}
	}
	
	// Erstellt aus $_SESSION['wahl'] den Wahlcode mit der Wahl-ID, ob die Wahl gültig ist und der gewählten Erst- und Zweitstimme
	function wahlcode_erstellen(){
		$partei_wks = '';
		$partei_ls = '';
		if(isset($_SESSION['wahl']) && $_SESSION['wahl'] != 'invalid'){
			foreach($_SESSION['wahl'] as $wahl){
				if(explode("_",$wahl)[0] == 'wks'){
					$partei_wks = $wahl;
					
				}
				if(explode("_",$wahl)[0] == 'ls'){
					$partei_ls = $wahl;
				}
			}
		}
		
		$valide_wahl = $_SESSION['gueltig'];
		
		if($partei_wks != ''){
			$partei_wks = sprintf("%02d", explode("_",$partei_wks)[1])."1";
		}else{
			$partei_wks = '001';
		}
		if($partei_ls != ''){
			$partei_ls = sprintf("%02d", explode("_",$partei_ls)[1])."2";
		}else{
			$partei_ls = '002';
		}
		
		return $_SESSION['aktive_wahl'].$valide_wahl.$partei_wks.$partei_ls;
	}
	
	// Bei jedem Aufruf dieser Methode wird die aktuelle Wahl analysiert und die korrekte Anzeige im Header erzeugt
	function check_rules(){
		if(isset($_SESSION['wks'])){
			$wks_tmp = $_SESSION['wks'];
		}else{
			$wks_tmp = '';
		}
		
		if(isset($_SESSION['ls'])){
			$ls_tmp = $_SESSION['ls'];
		}else{
			$ls_tmp = '';
		}
	
		if(isset($wks_tmp) || isset($ls_tmp)){
			if($wks_tmp == '' && $ls_tmp == ''){
				$land_alert = 1;			// Fall 2 -> Die Wahl ist ungültig / keine Stimme vergeben
				$_SESSION['alert'] = 1;
				$_SESSION['gueltig'] = 0;
			}else if($wks_tmp != '' && $ls_tmp == ''){
				$land_alert = 2;				// Fall 3 -> Nur Wahlkreisstimme vergeben
				$_SESSION['alert'] = 2;
				$_SESSION['gueltig'] = 1;
			}else if($wks_tmp == '' && $ls_tmp != ''){
				$land_alert = 3;				// Fall 4 -> Nur Landesstimme vergeben
				$_SESSION['alert'] = 3;
				$_SESSION['gueltig'] = 1;
			}else if($wks_tmp != '' && $ls_tmp != ''){
				$land_alert = 4;				// Fall 5 -> Wahlkreis- und Landesstimme vergeben
				$_SESSION['alert'] = 4;
				$_SESSION['gueltig'] = 1;
			} else{
				$land_alert = 0;					// Fall 0 -> Keine Stimme vergeben
				$_SESSION['alert'] = 0;
				$_SESSION['gueltig'] = 0;
			}
		}
	}
	
	//erstellt die Suchliste für die Suche (hier nicht implementiert)
	function get_search_value(){
		return null;
	}
	
	//liest den Suchtext aus und zeigt die gewünschte Partei bzw. den gewünschten Kandidaten an oder gibt eine Fehlermeldung aus (hier nicht implementiert)
	function suche($suche){}
	
	//Erstellt den Wahlzettel als PDF aus dem übergebenen Wahlcode
	function makePDF($wahlcode){
		if(isset($wahlcode)){
			$wahlcode = explode("-", $wahlcode)[0];
	
			$aktuelle_wahl = substr($wahlcode, 0, 5);
			$gueltig = substr($wahlcode, 5 , 1);
			$wks = intval(substr($wahlcode, 6 , 2));
			$ls = intval(substr($wahlcode, 9 , 2));	
			
			$wks_partei_values = $this->get_partei($wks);
			$wks_short = utf8_decode($wks_partei_values[0]);
			
			$ls_partei_values = $this->get_partei($ls);
			$ls_short = utf8_decode($ls_partei_values[0]);
			$ls_long = utf8_decode($ls_partei_values[1]);
			
			$wks_kandidaten = $this->mitglieder_auslesen($wks, 'erststimme'); 
			$row_kandidat = mysqli_fetch_row($wks_kandidaten);
			$row_kandidat_ersatz = mysqli_fetch_row($wks_kandidaten);
			$kandidat = utf8_decode($row_kandidat[1].", ".$row_kandidat[2]);
			$kandidat_ersatz = utf8_decode($row_kandidat_ersatz[1].", ".$row_kandidat_ersatz[2]);
			
			$ls_kandidaten = $this->mitglieder_auslesen($ls, 'zweitstimme');
			$mitglieder = utf8_decode($ls_kandidaten->fetch_row()[1]);
			while($row = mysqli_fetch_array($ls_kandidaten)){
				$mitglieder .= ", ".utf8_decode($row['name']);
			}
			
			$date = utf8_decode($this->get_date());
			
			//Erstellung des PDF's
			$pdf = new FPDF();
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',30);
			$pdf->Cell(60, 10, "Stimmzettel");
			$pdf->ln();
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(150,7,"für die Wahl zum Hessischen Landtag am $date");
			$pdf->SetX(171);
			$pdf->SetFont('Arial','',8);
			$pdf->MultiCell(25, 3,"QR-Code \nzur automat. Auszählung","","l");
			$pdf->SetFont('Arial','',10);
			
			//Wahl ungültig?
			if($gueltig == 0){
				//ja -> Textfelder entsprechend Setzen
				$pdf->SetFontSize(12);
				$pdf->Cell(57, 24,"Ungültig",1);
				$pdf->SetX(69);
				$pdf->SetFontSize(10);
				$pdf->MultiCell(100, 24,"Ihre Stimme ist ungültig.", 1);
			}else{
				//Nein -> Parteifeld bzw. Textfeld setzen
				$pdf->Cell(57, 24, "",1);
				$x=$pdf->getX();
				$y=$pdf->getY();
				$pdf->Image("bilder/initvoteColoured.png", 13, 31);
				$pdf->SetFontSize(10);
				$pdf->SetX(69);
				$pdf->MultiCell(100, 8,"Ihre Stimme ist gültig.\nNachfolgend finden Sie eine Auflistung des Kandidaten bzw. der Abgeordneten.", 1);
				$pdf->ln();				
				//Eigentliche Stimme
				//Wahlkreisstimme gesetzt, aber keine Landesstimme
				if($wks != 0 and $ls == 0){
					$pdf->ln();
					$x=10; // Seitenabstand links
					$z=130; // Zellenbreite
					$pdf->SetX($x);
					$pdf->SetFontSize(16);
					$pdf->Cell($z, 15, "Wahlkreisstimme", 1, 2, "C");
					$y= $pdf->getY();
					$pdf->Cell($z, 15, "$kandidat", "LRT", 1);
					$pdf->SetFontSize(10);
					$pdf->SetX($x);
					$pdf->Cell($z, 10, "Ersatzbewerber:", 'LR', 1);
					$pdf->SetX($x);
					$pdf->Cell($z, 10, "$kandidat_ersatz", 'LRB', 1);
					$pdf->SetXY(92, $y);
					$pdf->SetFontSize(12);
					$pdf->Cell(30, 35, "$wks_short", 1);
					$pdf->Line(10, $y+15, 92, $y+15);
					$pdf->Image("bilder/initvoteColoured.png", 123.5, $y+11);
				}else{
					//Landesstimme gesetzt, aber keine Wahlkreisstimme
					if($wks == 0 and $ls != 0){
						$pdf->ln();
						$x=10; // Seitenabstand links
						$z=130; // Zellenbreite
						$pdf->SetX($x);
						$pdf->SetFontSize(16);
						$pdf->Cell($z, 15, "Landesstimme", 1, 2, "C");
						$y = $pdf->getY();
						$pdf->SetFontSize(11);
						$pdf->Cell($z, 15, "$ls_long", "LRT", 1);
						$pdf->SetFontSize(10);
						$pdf->SetX($x);
						$y2 = $pdf->GetY();
						$pdf->Cell($z, 20, "", 'LRB', 1);
						$pdf->SetXY($x, $y2);
						$pdf->MultiCell(82, 10, "$mitglieder", 0);
						$pdf->SetXY(92, $y);
						$pdf->SetFontSize(12);
						$pdf->Cell(30, 35, "$ls_short", 1);
						$pdf->Line(10, $y+15, 92, $y+15);
						$pdf->Image("bilder/initvoteColoured.png", 123.5, $y+11);				
					}else{
						//Wahlkreis und Landesstimme gesetzt
						$pdf->ln();
						$x=10; // Seitenabstand links
						$z=130; // Zellenbreite
						$pdf->SetX($x);
						$pdf->SetFontSize(16);
						$pdf->Cell($z, 15, "Wahlkreisstimme", 1, 2, "C");
						$y= $pdf->getY();
						$pdf->Cell($z, 15, "$kandidat", "LRT", 1);
						$pdf->SetFontSize(10);
						$pdf->SetX($x);
						$pdf->Cell($z, 10, "Ersatzbewerber:", 'LR', 1);
						$pdf->SetX($x);
						$pdf->Cell($z, 10, "$kandidat_ersatz", 'LRB', 1);
						$pdf->SetXY(92, $y);
						$pdf->SetFontSize(12);
						$pdf->Cell(30, 35, "$wks_short", 1);
						$pdf->Line(10, $y+15, 92, $y+15);
						$pdf->Image("bilder/initvoteColoured.png", 123.5, $y+11);
						
						//Zweitstimme (Landesstimme) modelieren
						$pdf->SetXY($x, $y+40);
						$pdf->SetFontSize(16);
						$pdf->Cell($z, 15, "Landesstimme", 1, 2, "C");
						$y = $pdf->getY();
						$pdf->SetFontSize(11);
						$pdf->Cell($z, 15, "$ls_long", "LRT", 1);
						$pdf->SetFontSize(10);
						$pdf->SetX($x);
						$y2 = $pdf->GetY();
						$pdf->Cell($z, 20, "", 'LRB', 1);
						$pdf->SetXY($x, $y2);
						$pdf->MultiCell(82, 10, "$mitglieder", 0);
						$pdf->SetXY(92, $y);
						$pdf->SetFontSize(12);
						$pdf->Cell(30, 35, "$ls_short", 1);
						$pdf->Line(10, $y+15, 92, $y+15);
						$pdf->Image("bilder/initvoteColoured.png", 123.5, $y+11);
					}
				}
			}
			// Feld für den QR-Code
			$pdf->SetXY(171, 29);
			$pdf->Cell(25,24,$pdf->Image("http://localhost/EasyVote/qrcode.php/?id=$wahlcode",$pdf->getX()+1,$pdf->getY()+1,23,23,'PNG'), 1);
			$pdf->Output("abgabe.pdf" , "i");
		
			/*Designvorschlag 2 für die Gestaltung des PDF's.
			// Kopfbereich des Stimmzettels
			$pdf->AddPage();
			$pdf->SetLeftMargin(20);
			$pdf->SetRightMargin(20);
			$pdf->SetFont('Arial','B',30);
			$pdf->Cell(170, 10, "Stimmzettel", 0, 1, "C");
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,7, "für die Wahl zum Hessischen Landtag", 0, 1, "C");
			$pdf->Cell(170,7, "am 18. Januar 2009", 0, 1, "C");
			$pdf->ln();
			//Feld für den QR-Code
			$y = $pdf->getY();
			$pdf->Text(80, $pdf->getY(), "QR-Code");
			$pdf->ln();
			$pdf->Text(80, $pdf->getY(), "zur automat.");
			$pdf->ln();
			$pdf->Text(80, $pdf->getY(), "Auszählung");
			$pdf->Image("http://localhost/EasyVote/qrcode.php/?id=$wahlcode", 105, $y-5, 23, 23, 'PNG');
			$pdf->Rect(78, $y-5, 50, 23);
			$pdf->Line(105, $y-5, 105, $y+18);
			$pdf->ln();
			
			//Eigentliche Stimme
			//Wahl gültig?
			if($gueltig != 1){
				//Nein -> Textausgabe, fertig
				$pdf->ln();
				$pdf->SetX(78);
				$pdf->Cell(50, 7, "Ihre Wahl ist ungültig. $gueltig", 1, 0, "C");
				$pdf->Output();
			}else{
				//Ja -> Erststimme (Wahlkreisstimme) modelieren
				$pdf->ln();
				$x=33; // Seitenabstand links
				$z=130; // Zellenbreite
				$pdf->SetX($x);
				$pdf->SetFontSize(16);
				$pdf->Cell($z, 15, "Wahlkreisstimme", 1, 2, "C");
				$y= $pdf->getY();
				$pdf->Cell($z, 15, "$kandidat", "LRT", 1);
				$pdf->SetFontSize(10);
				$pdf->SetX($x);
				$pdf->Cell($z, 10, "Ersatzbewerber:", 'LR', 1);
				$pdf->SetX($x);
				$pdf->Cell($z, 10, "$kandidat_ersatz", 'LRB', 1);
				$pdf->SetXY(115, $y);
				$pdf->SetFontSize(12);
				$pdf->Cell(30, 35, "$wks_short", 1);
				$pdf->Line(33, $y+15, 115, $y+15);
				$pdf->Image("bilder/ballotChecked50.gif", 148, $y+11);
				
				//Zweitstimme (Landesstimme) modelieren
				$pdf->SetXY($x, $y+40);
				$pdf->SetFontSize(16);
				$pdf->Cell($z, 15, "Landesstimme", 1, 2, "C");
				$y = $pdf->getY();
				$pdf->Cell($z, 15, "$ls_long", "LRT", 1);
				$pdf->SetFontSize(10);
				$pdf->SetX($x);
				$y2 = $pdf->GetY();
				$pdf->Cell($z, 20, "", 'LRB', 1);
				$pdf->SetXY($x, $y2);
				$pdf->MultiCell(82, 10, "$mitglieder", 0);
				$pdf->SetXY(115, $y);
				$pdf->SetFontSize(12);
				$pdf->Cell(30, 35, "$ls_short", 1);
				$pdf->Line(33, $y+15, 115, $y+15);
				$pdf->Image("bilder/ballotChecked50.gif", 148, $y+11);
				$pdf->Output();
			}*/
		}
	}
	
	//Zeigt die Wahl des Users als Vorschau auf der letzten Seite an
	function show_wahl($wahlcode){
		
		$invalid = ($_SESSION['lang'] == 'de/') ? "Ung&uuml;ltig" : "Invalid";
		if(isset($wahlcode)){
			$wahlcode = explode("-", $wahlcode)[0];
	
			$aktuelle_wahl = substr($wahlcode, 0, 5);
			$gueltig = substr($wahlcode, 5 , 1);
			$wks = intval(substr($wahlcode, 6 , 2));
			$ls = intval(substr($wahlcode, 9 , 2));
			
			if($gueltig != "0"){
			
				if($wks == 0){
					$image_wks ="bilder/initvote.png";
					$wks_short = $invalid;
					$kandidat = "";
					$kandidat_ersatz = "";
				}else{
					$image_wks = "bilder/initvoteColoured.png";
					$wks_partei_values = $this->get_partei($wks);
					$wks_short = $wks_partei_values[0];
					
					$wks_kandidaten = $this->mitglieder_auslesen($wks, 'erststimme'); 
					$row_kandidat = mysqli_fetch_row($wks_kandidaten);
					$row_kandidat_ersatz = mysqli_fetch_row($wks_kandidaten);
					$kandidat = $row_kandidat[1].", ".$row_kandidat[2];
					$kandidat_ersatz = $row_kandidat_ersatz[1].", ".$row_kandidat_ersatz[2];
				}
				
				if($ls == 0){
					$image_ls ="bilder/initvote.png";
					$ls_short = "";
					$ls_long = $invalid;
					$mitglieder = "";
				}else{
					$image_ls = "bilder/initvoteColoured.png";
					$ls_partei_values = $this->get_partei($ls);
					$ls_short = $ls_partei_values[0];
					$ls_long = $ls_partei_values[1];
					
					$ls_kandidaten = $this->mitglieder_auslesen($ls, 'zweitstimme');
					$mitglieder = $ls_kandidaten->fetch_row()[1];
					while($row = mysqli_fetch_array($ls_kandidaten)){
						$mitglieder .= ", ".$row['name'];
					}
				}
				
				$invalid_vote = "";
				if($ls == 0 && $wks == 0){
					$invalid_vote = $invalid;
				}
			
				echo "<table width = 95%>";
				echo "<tr><td><h1>$invalid_vote</h1></td><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100"/>'."</td></tr>";
				echo "</table>";
				echo "<hr />";	
					
				echo "<div id='TabelleEuropa'>";
				echo "<table border='1' width='96%'><tr><th colspan='3'>Wahlkreisstimme";
				if($wks != 0){
					echo "<tr><th width='50%'>$kandidat</th><th rowspan='2'>$wks_short</th><td rowspan='2' width='25%'><center><img src='$image_wks'></center></td></tr>";
					echo "<tr><td width='25%'>Ersatzbewerber:<br><br>$kandidat_ersatz</td></tr>";
				}else{
					echo "<tr><th colspan = '2' width='75%'>$wks_short</th><td width='25%'><center><img src='$image_wks'></center></td></tr>";
				}
				echo "</table></th>";
				
				echo "<table border='1' width='96%'><tr><th colspan='3'>Landesstimme";
				if($ls != 0){
					echo "<tr><th width='50%'>$ls_long</th><th rowspan='2'>$ls_short</th><td rowspan='2' width='25%'><center><img src='$image_ls'></center></td></tr>";
					echo "<tr><td  width='25%'>$mitglieder</td></tr>";
				}else{
					echo "<tr><th colspan = '2'  width='75%'>$ls_long</th><td width='25%'><center><img src='$image_ls'></center></td></tr>";
				}
				echo "</th></tr></table>";
				echo "</div>";
			}else{
				echo "<table width = 95%>";
				echo "<tr><td width = 50px><img src='bilder/initvote.png'></td><td><h1>$invalid</h1></td><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100" />'."</td></tr>";
				echo "</table>";
				echo "<hr />";
			}
		}
		
	}
}
?>
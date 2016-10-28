<?php

require_once('Wahl.php');
require_once('Wahl_Interface.php');

class Europawahl extends Wahl implements Wahl_Interface
{
	//Setzt die zu Ladende .xml-Datei
	function Europawahl()
	{
		$this->xmlFile = 'wahlen/xmlFiles/europawahl.xml';
		$this->database = 'europawahl';
	}
	
	//Überschreibt die Funktion datenbank_erstellen() der Superklasse "Wahl"
	//Die Funktion erstellt eine spezifische Datenbank für eine Europawahl und fügt die Werte der spezifischen XML-Datei ein
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
				exit("Fehler");
			}
		}

		mysqli_close($connect);	
	}


	//liest die Mitglieder einer Partei aus und liefert das Ergebnis zurück
	function mitglieder_auslesen($partei_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error()); 
		mysqli_set_charset($connect, "utf8");
		
		$sql = "SELECT * FROM mitglieder WHERE partei = " . $partei_id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		mysqli_close($connect);	
		
		return($result);
	}
	
	//Erstellt den Wahlzettel für die Europawahl
	function mitgliederliste_anzeigen($partei_id)
	{
		echo "<div class='party'>";
		if($partei_id == 0){
			$parteien = $this->parteien_auslesen();
			$image = "<img src='bilder/BallotUnchecked50.gif'>";
			
			//Iteriert über alle Parteien und liest die Mitglieder der jeweiligen Partei aus und fügt diese in die Tabelle ein
			echo "<table border='1' width:90% style='align:center; margin-left:auto; margin-right:auto;'>";
			echo "<style> td{ font-size: 5px } </style>";
			while($row = mysqli_fetch_array($parteien)){
			
				$mitglieder = $this->mitglieder_auslesen($row['id']);
				$partei_members = array();
				while($rows = mysqli_fetch_array($mitglieder)){
					$partei_members[] = $rows;
				}
				
				if(isset($_SESSION['vote'])){
					if('partei_'.$row['id'] == $_SESSION['vote']){
						$image = "<img src='bilder/BallotChecked50.gif'>";
					}else{
						$image = "<img src='bilder/BallotUnchecked50.gif'>";
					}
				}			
				
				echo "<tr><th rowspan='6'>".$row['id']."</th><th style='font-size:1.2em' colspan='2'>".$row['partei']."</th><th rowspan='6'>".
				"<button type='submit' name='vote_changed' value='partei_".$row['id']."' class='text_button'>".$image."</button></th></tr>";
				for($i = 0; $i <= 4; $i++){
					$x = $i + 1;
					$j = $i + 5;
					echo "<tr><td style='font-size:0.8em' width=225px>$x. ".$partei_members[$i]['vorname']." <b>".$partei_members[$i]['name']."</b></td>";
					
					if(count($partei_members) > $j){
						$y = $j + 1;
						echo "<td style='font-size:0.8em' width=225px>$y. ".$partei_members[$j]['vorname']." <b>".$partei_members[$j]['name']."</b></td></tr>";
					}else{
						echo "<td></td></tr>";
					}
				}
			}	
			echo "</table>";
		}
		else{
			echo "Fehler beim Anzeigen der Mitgliederliste!";
		}
		echo "</div>";
	}
	
	//Ausgabe des Buttons "Wahlzettel anzeigen" als Link auf der linken Navigationsleiste der Wahl
	function parteienliste_laden()
	{
		echo "<form method='post' action='election.php'>";
		
			if(isset($_POST['partei']) && ($_POST['partei']== '0')){
				if($_SESSION['lang'] == 'en/'){
					echo "<li><button type='submit' name='partei' value='0' class='button_aktiv'>Show ballot card</button></li>";
				}else{
					echo "<li><button type='submit' name='partei' value='0' class='button_aktiv'>Wahlzettel anzeigen</button></li>";
				}
			}else{ 
				if($_SESSION['lang'] == 'en/'){
					echo "<li><button type='submit' name='partei' value='0' class='button'>Show ballot card</button></li>";
				}else{
					echo "<li><button type='submit' name='partei' value='0' class='button'>Wahlzettel anzeigen</button></li>";
				}
			}

		echo "</form>";
		
	}
	
	// Bereitet die Abgabe der Wahl vor
	function wahl_abgeben(){
		if(isset($_SESSION['vote'])){
			$_SESSION['wahl'] = $_SESSION['vote'];
		}else{
			$_SESSION['wahl'] = 'invalid';
			$_SESSION['alert'] = 2;
		}
	}
	
	// Wenn eine Stimme geändert wird, wird in dieser Funktion die Wahl angepasst
	function stimme_bearbeiten(){
		$_POST['partei'] = 0;
		if(isset($_POST['vote_changed'])){			//wenn Stimmen für eine Partei abgegeben wurden
			if(isset($_SESSION['vote']) &&  $_SESSION['vote'] == $_POST['vote_changed']){ 
				unset($_SESSION['vote']);	//Wenn der Button schon vorhanden ist, entferne das Kreuz bei dieser Partei
			}else{	
				$_SESSION['vote'] = $_POST['vote_changed'];	//andernfalls setze die Session auf die neue Partei
			}
		}
	}
	
	// Erstellt aus $_SESSION['wahl'] den Wahlcode mit der Wahl-ID, ob die Wahl gültig ist und der gewählten Partei
	function wahlcode_erstellen(){
		if(isset($_SESSION['wahl']) && $_SESSION['wahl'] != 'invalid'){
			$partei = sprintf("%02d", explode("_",$_SESSION['wahl'])[1]);
		}else{
			$partei = '00';
		}
		
		$valide_wahl = $_SESSION['gueltig'];
		
		$members = '';
		$members_deleted = '';
		$members_other = '';
		
		return $_SESSION['aktive_wahl'].$valide_wahl.$partei.$members.$members_other."_".$members_deleted;
	}
	
	// Bei jedem Aufruf dieser Methode wird die aktuelle Wahl analysiert und die korrekte Anzeige im Header erzeugt
	function check_rules(){
		if(isset ($_SESSION['vote'])){
			$euro_vote = $_SESSION['vote'];
		}else{
			$euro_vote = '';
		}
			
		if($euro_vote == ''){
			$_SESSION['alert'] = 2;		// Fall 2 -> Die Wahl ist ungültig, da keine Stimme vergeben wurde
			$_SESSION['gueltig'] = 0;
		}else{
			$_SESSION['alert'] = 3;		// Fall 3 -> Die Wahl ist gültig 
			$_SESSION['gueltig'] = 1;
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
			$tmp_wahlcode = explode("-", $wahlcode)[0];
		
			$wahlcode = substr($wahlcode, 0, -2);
			
			$aktuelle_wahl = substr($tmp_wahlcode, 0, 5);
			$gueltig = substr($tmp_wahlcode, 5 , 1);
			$stimme = intval(substr($tmp_wahlcode, 6 , 2));
			
			//Erstellung des PDF's
			$pdf = new FPDF();
			
			//Variablen
			$party = utf8_decode($this->get_partei($stimme));  
			$date = utf8_decode($this->get_date());
		
		
			$partei_members = array();	
			$mitglieder = $this->mitglieder_auslesen($stimme);	
			while($row = mysqli_fetch_array($mitglieder)){
				$partei_members[] = $row[1].", ".$row[2];
			}		
			//Designvorschlag 1 für das PDF, bei Bedarf durch den Code von Vorschlag 2 ersetzen
			
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',30);
			$pdf->Cell(60, 10, "Stimmzettel");
			$pdf->ln();
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(150,7,"für die Wahl der Abgeordneten des Europäischen Parlaments am $date");
			$pdf->SetX(171);
			$pdf->SetFont('Arial','',8);
			$pdf->MultiCell(25, 3,"QR-Code \nzur automat. Auszählung","","l");
			
			//Wahl ungültig?
			if($gueltig == 0){
				//ja -> Textfelder entsprechend Setzen
				$pdf->SetFontSize(12);
				$pdf->Cell(57, 24,"Ungültig",1);
				$pdf->SetX(69);
				$pdf->SetFontSize(10);
				$pdf->MultiCell(100, 12,"Ihre Stimme ist ungültig, da Sie keine Partei gewählt haben.\n\n", 1);
			}else{
				//Nein -> Parteifeld bzw. Textfeld setzen
				$pdf->Cell(57, 24, "",1);
				$x=$pdf->getX();
				$y=$pdf->getY();
				$pdf->Image("bilder/initvoteColoured.png", 13, 31);
				$pdf->SetFontSize(12);
				$pdf->Text(30 ,40, "$party");
				$pdf->SetFontSize(10);
				$pdf->SetX(69);
				$pdf->MultiCell(100, 8,"Ihre Stimme ist gültig. Sie haben folgende Partei gewählt: $party. \nNachfolgend finden Sie eine Auflistung der Abgeordneten.", 1);
				$pdf->ln();
				//Auflistung der Abgeordneten
				foreach($partei_members as $member){
					$a = utf8_decode($member);
					$pdf->Cell(57, 7, "$a", 1, 1);
				}	
			}
			
			// Feld für den QR-Code
			$pdf->SetXY(171, 29);
			$pdf->Cell(25,24,$pdf->Image("http://localhost/EasyVote/qrcode.php/?id=$wahlcode",$pdf->getX()+1,$pdf->getY()+1,23,23,'PNG'), 1);
			
			//Ausgabe->fertig
			$pdf->output("abgabe.pdf" , "i");
			
			/*Designvorschlag 2 für das PDF, bei Bedarf den Code von Vorschlag 1 durch diesen ersetzen
			// Kopfbereich des Stimmzettels
			$pdf->AddPage();
			$pdf->SetLeftMargin(20);
			$pdf->SetRightMargin(20);
			$pdf->SetFont('Arial','B',30);
			$pdf->Cell(170, 10, "Stimmzettel", 0, 1, "C");
			$pdf->SetFont('Arial','',12);
			$pdf->Cell(170,7, "für die Wahl der Abgeordneten des Europäischen Parlaments", 0, 1, "C");
			$pdf->Cell(170,7, "am 25. Mai 2014", 0, 1, "C");
			$pdf->Cell(170,7, "im Land Hessen", 0, 1, "C");
			$pdf->ln();
			
			
			//Feld für den QR-Code
			$y= $pdf->getY();
			$pdf->Text(80, $pdf->getY(), "QR-Code");
			$pdf->ln();
			$pdf->Text(80, $pdf->getY(), "zur automat.");
			$pdf->ln();
			$pdf->Text(80, $pdf->getY(), "Auszählung");
			$pdf->Image("http://localhost/EasyVote/qrcode.php/?id=$wahlcode", 105, $y-5, 15, 15, 'PNG');
			$pdf->Rect(78, $y-5, 50, 23);
			$pdf->Line(105, $y-5, 105, $y+18);
			$pdf->ln();
			
			//Eigentliche Stimme
			//Wahl gültig?
			if($gueltig != 1){
				//Nein -> Textausgabe, fertig
				$pdf->ln();
				$pdf->SetX(78);
				$pdf->Cell(50, 7, "Ihre Wahl ist ungültig.", 1, 0, "C");
				$pdf->Output();
			}else{
				//Ja -> Partei + Mitglieder laden, fertig
				$pdf->ln();
				$pdf->SetX(68);
				$y= $pdf->getY();
				$pdf->SetFontSize(16);
				$pdf->Cell(70, 15, "$party", 1, 1);
				$pdf->Image("bilder/ballotChecked50.gif", 120, $y+1.5);
				$pdf->SetFontSize(10);
				foreach($partei_members as $member){
					$pdf->SetX(68);
					$pdf->Cell(70, 7, "$member", 1, 1, "C");
				}
				$pdf->Output();
			}*/
		}
	}
	
	//Zeigt die Wahl des Users als Vorschau auf der letzten Seite an
	function show_wahl($wahlcode){
		if(isset($wahlcode)){		
		
			$aktuelle_wahl = substr($wahlcode, 0, 5);
			$gueltig = substr($wahlcode, 5 , 1);
			$partei = intval(substr($wahlcode, 6 , 2));
			$initvote = "";
			if($gueltig == '0'){
				$invalid = ($_SESSION['lang'] == 'de/') ? "Ung&uuml;ltig" : "Invalid";
			}
			
			echo "<table width = 95%>";
			if($partei != '00'){
				echo "<tr><td width = 50px><img src='bilder/initvoteColoured.png'></td><td><h1>".$this->get_partei($partei)."</h1></td><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100"/>'."</td></tr>";
			}else{
				echo "<tr><td width = 50px><img src='bilder/initvote.png'></td><td><h1>$invalid</h1><td align='right'>".'<img src="qrCode.php?id='.$wahlcode.'" width = "100" />'."</td></tr>";
			}
			echo "</table>";
			echo "<hr />";
			
			$partei_members = array();	
			$mitglieder = $this->mitglieder_auslesen($partei);
			
			//Liest alle Namen der Kandidaten einer Partei aus und speichert sie in einem Array
			while($row = mysqli_fetch_array($mitglieder)){
				$partei_members[] = $row[1].", ".$row[2];
				
			}
			
			//Gibt alle Namen der Kandidaten einer Partei aus
			echo "<table width = 95% >";
			foreach($partei_members as $member){
				echo "<tr><td><h3>$member</h3></td></tr>";
			}
			echo "</table>";
		}
	}
}
?>
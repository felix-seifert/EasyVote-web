<?php


// Superklasse f�r alle Wahl-Klassen
class Wahl
{
	public $wahl_id;
	public $wahl_name;
	public $maxvotes;
	protected $xmlFile;
	
	//Zugangsdaten der Datenbank
	protected $host = 'localhost';
	protected $username = 'root';
	protected $password = '';
	protected $database = 'wahlen';
	private $table = 'wahlen';

	
	function __construct(){}

	//Liest den Tag, den Monat und das Jahr der Wahl aus der .xml-Datei aus und liefert es als Datum zurück
	function get_date(){
		if(file_exists($this->xmlFile)){
			$xml = simplexml_load_file($this->xmlFile);
			
			$day = $xml->election_day;
			$month = $xml->election_month;
			$year = $xml->election_year;
		
			$date = "$day. $month $year";
		} else{
			exit("Fehler");
		}
		
		return $date;
	}
	
	//diese Funktion liefert die ID der aktuell geladenen (aktiven) Wahl zurück
	function get_aktive_wahl(){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error()); 
		$wahl_id='';

		$sql = "SELECT wahl_id FROM ".$this->table." WHERE aktiv = 'T'";
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		while($row = mysqli_fetch_array($result)){
			$wahl_id = $row['wahl_id'];
		}
		mysqli_close($connect);
		
		if($wahl_id == ''){
			return '0';
		}
		
		return $wahl_id;
	}
	
	//$wahl_id: ID der neuen aktuellen Wahl
	//Die Funktion aktualisiert die aktive Wahl und setzt alle anderen Wahlen auf inaktiv
	function update_aktive_wahl($wahl_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error()); 
		mysqli_query($connect, "UPDATE ".$this->table." SET aktiv='F'") or die(mysqli_error());
		mysqli_query($connect, "UPDATE ".$this->table." SET aktiv='T' WHERE wahl_id='".$wahl_id."'") or die(mysqli_error());
		mysqli_close($connect);
	}
	
	
	//Entfernt eine bestimmte Datenbank ($filename)
	function DB_entfernen($filename)
	{
		$connect = mysqli_connect($this->host, $this->username, $this->password) or die(mysqli_error()); 
		mysqli_query($connect, "DROP DATABASE if EXISTS ".$filename) or die(mysqli_error());
		mysqli_query($connect, "UPDATE ".$this->database.".".$this->table." SET aktiv='F' WHERE wahlart='".$filename."'") or die(mysqli_error());
		mysqli_close($connect);
	}	
	
	//Liest die Parteien aus der XML-Datei aus 
	function parteien_auslesen(){
	
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error()); 
		mysqli_set_charset($connect, "utf8");
		
		$sql = "SELECT * FROM parteien";
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		mysqli_close($connect);	
		
		return($result);
	}
	
	// Gibt den Namen einer Partei mit der übergeben ID zurück
	function get_partei($partei_id){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		mysqli_set_charset($connect, "utf8");

		$sql = "SELECT partei FROM parteien WHERE id = " . $partei_id;
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		
		$ergebnis = mysqli_fetch_row($result)[0];

		mysqli_close($connect);
		
		return($ergebnis);
	}
	
	// Aktualisiert den Eintrag eines Benutzers, falls dieser seine Stimme hochlädt
	function abgabe_wahl($benutzername, $passwort, $wahl){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		
		$sql = "UPDATE stimmen SET wahl = '".$wahl."' WHERE benutzername='".$benutzername."' AND passwort='".$passwort."'";
		mysqli_query($connect, $sql) or die(mysqli_error());
			
		mysqli_close($connect);
	}
	
	// Testet, ob die Benuterdaten eines Users (Benutzername und Passwort) in der Datenbank enthalten sind
	function benutzerdaten_korrekt($benutzername, $passwort){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		
		$sql = "SELECT COUNT(*) FROM stimmen WHERE benutzername='".$benutzername."' AND passwort='".$passwort."'";
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		mysqli_close($connect);
		
		mysqli_fetch_row($result)[0]==1 ?  $ergebnis = true : $ergebnis = false;
		return $ergebnis;
	}
	
	// Testet, ob schon eine Stimme für einen Benutzernamen abgegeben wurde, das Feld wahl also noch frei ist
	function wahl_is_null($benutzername){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $this->database) or die(mysqli_error());
		
		$sql = "SELECT COUNT(*) FROM stimmen WHERE benutzername='".$benutzername."' AND wahl IS NULL";
		$result = mysqli_query($connect, $sql) or die(mysqli_error());
		mysqli_close($connect);
		
		mysqli_fetch_row($result)[0]==1 ?  $ergebnis = true : $ergebnis = false;
		return $ergebnis;
	}
}
?>
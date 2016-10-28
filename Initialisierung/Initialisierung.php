<?php

class Initialisierung 
{
	private $host = 'localhost';
	private $username = 'root';
	private $password = '';
	
	function OnlineAbgabe(){
		$this->host = 'localhost';
		$this->username = 'root';
		$this->password = '';
	}

	
	function datenbank_erstellen($wahlname){

		$connect = mysqli_connect($this->host, $this->username, $this->password, $wahlname);
		if (mysqli_connect_errno()) {
			printf("Für die Wahl ".$wahlname." konnte keine Abgabe erstellt werden");
			exit();
		}else{
			//Tabelle der die Parteien
			$sql = "CREATE TABLE IF NOT EXISTS stimmen (id INT NOT NULL AUTO_INCREMENT, benutzername VARCHAR(8), passwort VARCHAR(150), wahl VARCHAR(650), PRIMARY KEY(id))";
			mysqli_query($connect, $sql) or die(mysqli_error());

			$sql = "INSERT INTO stimmen (benutzername, passwort) VALUES ('uv94w2xy', '".hash('sha256', 'xyz33abcuv94w2xy')."')";
			mysqli_query($connect, $sql) or die(mysqli_error());
			$sql = "INSERT INTO stimmen (benutzername, passwort) VALUES ('lmm19nop', '".hash('sha256', 'nop91mlmlmm19nop')."')";
			mysqli_query($connect, $sql) or die(mysqli_error());
			$sql = "INSERT INTO stimmen (benutzername, passwort) VALUES ('87vwx375', '".hash('sha256', '773abc7787vwx375')."')";
			mysqli_query($connect, $sql) or die(mysqli_error());
			$sql = "INSERT INTO stimmen (benutzername, passwort) VALUES ('ad54cc37', '".hash('sha256', 'bd753abdad54cc37')."')";
			mysqli_query($connect, $sql) or die(mysqli_error());
			$sql = "INSERT INTO stimmen (benutzername, passwort) VALUES ('avstu79b', '".hash('sha256', '37bvms2gavstu79b')."')";
			mysqli_query($connect, $sql) or die(mysqli_error());		

			mysqli_close($connect);
		}
	}
	
	
	
	function eintrag_hinzufuegen($benutzername, $passwort, $wahlname){
		$connect = mysqli_connect($this->host, $this->username, $this->password, $wahlname) or die(mysqli_error());
		
		$sql = "INSERT INTO stimmen (benutzername, passwort) VALUES ('".$benutzername."', '".$passwort."')";
		mysqli_query($connect, $sql) or die(mysqli_error());
			
		mysqli_close($connect);
	}
	
	//Erstellt die Datenbank "Wahlen" mit der Tabelle "Wahlen", in der die aktive Wahl abgespeichert wird
	function wahl_tabelle_erstellen(){
		$connect = mysqli_connect($this->host, $this->username, $this->password) or die(mysqli_error());
		
		$sql = "CREATE DATABASE IF NOT EXISTS wahlen";
		mysqli_query($connect, $sql) or die(mysqli_error());
		
		$sql = "USE wahlen";
		mysqli_query($connect, $sql) or die(mysqli_error());
		
		$sql = "CREATE TABLE IF NOT EXISTS wahlen (wahl_id INT NOT NULL, wahlart VARCHAR(20), aktiv VARCHAR(1), PRIMARY KEY (wahl_id))";
		mysqli_query($connect, $sql) or die(mysqli_error());
		
		$sql = "INSERT INTO wahlen (wahl_id, wahlart, aktiv) VALUES (54321, 'kommunalwahl', 'F')";
		mysqli_query($connect, $sql) or die(mysqli_error());
		$sql = "INSERT INTO wahlen (wahl_id, wahlart, aktiv) VALUES (88888, 'europawahl', 'F')";
		mysqli_query($connect, $sql) or die(mysqli_error());
		$sql = "INSERT INTO wahlen (wahl_id, wahlart, aktiv) VALUES (99999, 'landtagswahl', 'F')";
		mysqli_query($connect, $sql) or die(mysqli_error());
			
		mysqli_close($connect);
	}
	
	

}


?>
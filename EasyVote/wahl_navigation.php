<?php
require_once('wahlen/Kommunalwahl.php');
require_once('wahlen/Europawahl.php');
require_once('wahlen/Landtagswahl.php');
require_once('wahlen/Wahl.php');

/*
Die Klasse WahlNavi funktioniert als Schnittstelle zwischen dem generellen Aufbau der Seite und der Klassenstruktur, in der die 
Fuktionen der Seite definiert werden. In dieser Klasse wird die korrekte Klasse ausgewählt und aufgerufen. 
Die folgenden Kommentare beschreiben, was die Methoden, die aufgerufen werden, machen.
*/
class WahlNavi
{
	//Notwendige Variablen
	public $wahl_id;
	public $wahl_name;
	public $maxvotes;
	private $aktuelleWahl;

	//Konstruktor der WahlNavi-Klasse
	function __construct(){
		if($this->get_wahl() != ""){
			$this->aktuelleWahl = $this->get_wahl(); 	//wenn es eine aktive Wahl gibt, wird diese hier der Variable $aktuelleWahl zugewiesen
		}
	}
	
	//In dieser Methode wird die aktive Wahl ausgelesen und die benötigte Klasse zurückgegeben
	function get_wahl(){
		$wahl = new Wahl();
		$wahl_id = $wahl->get_aktive_wahl();
		
		switch($wahl_id)
		{
			case('0'):
				break;
			case ('54321'):
				return new Kommunalwahl();
				break;
					
			case ('88888'):
				return new Europawahl();
				break;
					
			case ('99999'):
				return new Landtagswahl();
				break;
				
			default: echo 'Fehler!';
				break;
		}
	}	
	
	//In dieser Methode wird die aktive Wahl ausgelesen und der Name der Wahl zurückgegeben
	function get_wahl_Name(){
		$wahl = new Wahl();
		$wahl_id = $wahl->get_aktive_wahl();
		
		switch($wahl_id)
		{
			case ('54321'):
				return 'Kommunalwahl';
				break;
					
			case ('88888'):
				return 'Europawahl';
				break;
					
			case ('99999'):
				return 'Landtagswahl';
				break;
				
			default: echo 'Fehler!';
				break;
		}
	}
	
	//In dieser Methode wird der Dateiname ausgelesen und die ID der Wahl zurückgegeben
	function get_wahlID($filename){
		switch($filename){
			case ('kommunalwahl'):
				return '54321';		
				break;
			
			case ('europawahl'):
				return '88888';
				break;
			
			case ('landtagswahl'):
				return '99999';
				break;
			
			default: echo 'Fehler!';
				break;
		}
	}
	
	// Funktion, um eine bestimmte Datenbank ($filename) zu erstellen
	function DB_erstellen(){
		$wahl_aktuell = $this->get_wahl();				//bestimmt, welche Wahl geladen werden muss
		$wahl_aktuell->datenbank_erstellen();				//Befehl zum erstellen der Datenbank
	}
	
	//Funktion, um eine bestimmte Datenbank ($filename) zu entfernen
	function DB_entfernen($filename){
		$wahl = new Wahl();
		$wahl->DB_entfernen($filename);
	}
	
	//Lädt die Optionen für die Suchliste
	function search_value(){
		return $this->aktuelleWahl->get_search_value();
	}
	
	// Funktion, um die Mitglieder einer Partei zu laden
	function mitglieder_laden($partei_id){
		$this->aktuelleWahl->mitgliederliste_anzeigen($partei_id);
	}
	
	// Funktion, um die Parteienliste der aktuellen Wahl zu laden
	function parteien_laden(){
		$this->aktuelleWahl->parteienliste_laden();	
	}
	
	//Setzt die aktive/aktuelle Wahl auf die gesetzte Wahl ($filename)
	function update_aktive_wahl($filename){
		$wahl_navi = new WahlNavi();
		$wahl_id = $wahl_navi->get_wahlID($filename);
		
		$wahl = new Wahl();
		$wahl->update_aktive_wahl($wahl_id);	
	}
		
	//gibt die aktive Wahl zurück
	function get_aktive_wahl(){
		$wahl = new Wahl();
		return $wahl->get_aktive_wahl();
	}
	
	//Liest die maximal zu vergebende Stimmenanzahl der Wahl aus
	function get_maxvotes(){
		return $this->aktuelleWahl->MaxStimmen_auslesen();
	}
	
	//Ruft die Methode wahl_abgeben auf, um die Abgabe der Wahl vorzubereiten
	function stimme_abgeben(){
		$this->aktuelleWahl->wahl_abgeben();
	}
	
	// Erstellt den Wahlcode, der für den QR-Code und die Erstellung des PDFs genutzt wird
	function wahl_auswerten(){
		return $this->aktuelleWahl->wahlcode_erstellen();
	}
	
	//Aktualisiert die Wahl des Users
	function stimme_bearbeiten(){
		$this->aktuelleWahl->stimme_bearbeiten();
	}
	
	// Bereitet die Abgabe der Wahl vor
	function abgabe_wahl($benutzername, $passwort, $vote){
		$this->aktuelleWahl->abgabe_wahl($benutzername, $passwort, $vote);
	}
	
	//Testet, ob die Benutzerdaten korrekt angegeben wurden
	function benutzerdaten_korrekt($benutzername, $passwort){
		return $this->aktuelleWahl->benutzerdaten_korrekt($benutzername, $passwort);
	}
	
	//Testet, ob schon eine Stimme für einen Benutzer abgegeben wurde
	function wahl_is_null($login){
		return $this->aktuelleWahl->wahl_is_null($login);
	}
	
	//Erzeugt die Ausgabe des Headers
	function create_rules(){
		return $this->aktuelleWahl->check_rules();
	}
	
	//Findet die Partei oder den Kandidaten und öffnet die entsprechende Partei
	function suche($suche){
		$this->aktuelleWahl->suche($suche);
	}
	
	//Erzeugt ein PDF mit der Wahl des Benutzers
	function createPDF($wahlcode){
		$this->aktuelleWahl->makePDF($wahlcode);
	}
	
	//Erzeugt eine Vorschau der Wahl des Benutzers auf der letzten Seite
	function showWahl($wahlcode){
		$this->aktuelleWahl->show_wahl($wahlcode);
	}
	
}
?>
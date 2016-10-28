

<?php
// Deklariere das Interface 'iTemplate'
interface Wahl_Interface
{
   //Funktion, die eine Datenbank für eine konkrete Wahl erstellt
	function datenbank_erstellen();
	
	//Definiert die Anzeige einer Mitgliederliste für eine bestimmte Partei
	function mitgliederliste_anzeigen($partei_id);
	
	//Funktion, mit der die Parteienliste geladen und angezeigt wird
	function parteienliste_laden();
	
	//Bereitet die Abgabe der Wahl vor
	function wahl_abgeben();
	
	/*Wird aufgerufen, wenn ein Button betätigt wurde (Listenstimme angekreuzt, Stimme an Kandidaten vergeben oder Kandidat gestrichen).
	Die Änderung wird in dieser Methode vorgenommen.*/
	function stimme_bearbeiten();
	
	//Erstellt den Wahlcode mit dem der QR-Code und das PDF erstellt wird
	function wahlcode_erstellen();
	
	//Definition der Regeln für den Header
	function check_rules();
	
	//Wenn eine Suchfunktion für die Wahl erstellt werden soll, werden die folgenden Methoden benötigt, um die Suche zu erstellen
	function get_search_value(); //erstellt die Suchliste für die Suche
	function suche($suche); //liest den Suchtext aus und zeigt die gewünschte Partei bzw. den gewünschten Kandidaten an oder gibt eine Fehlermeldung aus
	
	//Erstellt ein PDF aus dem übergebenen Wahlcode
	function makePDF($wahlcode);
	
	//Zeigt die Wahl des Users als Vorschau an
	function show_wahl($wahlcode);
}
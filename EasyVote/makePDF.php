<?php
require('fpdf/fpdf.php');
require('wahl_navigation.php');

	// Öffnet die Methode, die das PDF erstellt
	$wahlNavi = new WahlNavi();
	$wahlNavi->createPDF($_POST['pdf']);
	
?>



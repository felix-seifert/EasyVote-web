<?php

$wahl = new WahlNavi();
$maxstimmen = $wahl->get_maxvotes();

//Ausgabe der Regeln im Header

if(isset($_SESSION['alert'])){
		switch($_SESSION['alert']){
			case(0):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie 'Ungültig wählen' manuell betätigt haben.</p>";		
				break;
			case(1):
				$left_votes = $_SESSION['implicit'];
				echo "<style> #Header1{ background-color: #3DB653; } </style>";  
				echo "<p>Von $maxstimmen Stimmen haben Sie ".$_SESSION['member_count']." direkt an Kandidaten vergeben, $left_votes werden auf die gew&auml;hlte Liste verteilt.</p>";
				break;
			case(2):
				$left_votes = $maxstimmen - $_SESSION['member_count'];
				echo "<style> #Header1{ background-color: #3DB653; } </style>";  
				echo "<p>Von $maxstimmen Stimmen haben Sie ".$_SESSION['member_count']." direkt an Kandidaten vergeben. Es stehen noch $left_votes Stimme(n) zur Verf&uuml;gung.</p>";
				break;
			case(3):
				$left_votes = $_SESSION['member_count'] - $maxstimmen;
				echo "<style> #Header1{ background-color: #FFA500; } </style>";  
				echo "<p>Sie haben innerhalb einer Liste ".$_SESSION['member_count']." von $maxstimmen Stimmen vergeben. Es werden $left_votes Stimmen beim Druck abgeschnitten.</p>";
				break;
			case (4):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie mehr als $maxstimmen Kandidaten ausgew&auml;hlt haben.</p>";		
				break;
			case (5):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Ihre Stimme ist ung&uuml;ltig, da weder eine Liste noch ein Kandidat ausgew&auml;hlt wurden.</p>";		
				break;	
			case (6):
				echo "<style> #Header1{ background-color: EE2D29; } </style>";  
				echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie mehr als eine Liste angekreuzt haben.</p>";
				break;
				
			case (7):
				echo "<style> #Header1{ background-color: #FFA500; } </style>";  
				echo "<p>Es werden nach geltendem Wahlgesetz nur ihre Kandidatenstimmen beachtet, da Sie mehr als eine Liste angekreuzt haben.</p>";
				break;
			default: echo '<p>Fehler!';
					break;
			}
	}else{
		echo "<p>Sie haben bisher noch keine Stimme vergeben.</p>";
	}
?>
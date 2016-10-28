<?php

if(isset($_SESSION['alert'])){
		switch($_SESSION['alert']){
			case(0):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie 'Ungültig wählen' manuell betätigt haben.</p>";		
				break;
			case (1): 
				echo "<p>Sie haben bisher noch keine Stimme vergeben</p>";
				break;
			case(2):
				echo "<style> #Header1{ background-color: EE2D29; } </style>";  
				echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie keine Stimme an eine Partei vergeben haben.</p>";
				break;
			case(3):
				echo "<style> #Header1{ background-color: #3DB653; } </style>";  
				echo "<p>G&uuml;ltige Wahl!</p>";
				break;
			default: echo 'Fehler!';
				break;
			}
	}else{
		echo "<p>Sie haben bisher noch keine Stimme vergeben</p>";
	}
?>
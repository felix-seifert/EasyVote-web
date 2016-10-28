<?php
if(isset($_SESSION['alert'])){
	switch($_SESSION['alert']){
		case(0):
			echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
			echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie 'Ungültig wählen' manuell betätigt haben.</p>";		
			break;
		case (1):
			echo "<style> #Header1{ background-color: #EE2D29; } </style>";
			echo "Ung&uuml;ltige Wahl.";
			break;
		case(2):
			echo "<style> #Header1{ background-color: #FFA500; } </style>";
			echo "Sie haben bisher nur eine Stimme f&uuml;r den Wahlkreis vergeben";
			break;
		case(3):
			echo "<style> #Header1{ background-color: #FFA500; } </style>"; 
			echo "Sie haben bisher nur eine Stimme f&uuml;r den Landkreis vergeben";
			break;
		case (4):
			echo "<style> #Header1{ background-color: #3DB653; } </style>"; 
			echo "G&uuml;ltige Wahl";		
			break;
		default: echo 'Fehler!';
			break;
	}
}else{
	echo "Sie haben bisher noch keine Stimme vergeben.";
}


	
?>
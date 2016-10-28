<?php
if(isset($_SESSION['ausgabe'])){
	switch($_SESSION['ausgabe']){
		case 1: echo "<style> div#Abgabe fieldset {color: green;} </style>";
				echo "Ihre Stimme wurde gez&auml;hlt!"; 
				break;
		case 2: echo "<style> div#Abgabe fieldset {color: red;} </style>";
				echo "Es liegt bereits eine Abgabe f&uuml;r diesen Benutzernamen vor."; 
				break;
		case 3: echo "<style> div#Abgabe fieldset {color: red;} </style>";
				echo "Der eingegebene Benutzername oder das Passwort ist falsch."; 
				break;
		case 4: echo "<style> div#Abgabe fieldset {color: red;} </style>";
				echo "Der Benutzername hat nicht die korrekte L&auml;nge. Bitte pr&uuml;fen Sie nach, ob Sie die Daten korrekt eingegeben haben."; 
				break;
		default: echo "";
	}
}
?>
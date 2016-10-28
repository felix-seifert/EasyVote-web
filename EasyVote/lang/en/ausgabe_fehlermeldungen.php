<?php

if(isset($_SESSION['ausgabe'])){
	switch($_SESSION['ausgabe']){
		case 1: echo "<style> div#Abgabe fieldset {color: green;} </style>";
				echo "You voted in the election!"; break;
		case 2: echo "<style> div#Abgabe fieldset {color: red;} </style>";
				echo "You already casted a ballot"; break;
		case 3: echo "<style> div#Abgabe fieldset {color: red;} </style>";
				echo "Username or Password is incorrect."; break;
		case 4: echo "<style> div#Abgabe fieldset {color: red;} </style>";
				echo "Lenght of the Username is incorrect."; break;
		default: echo "";
	}
}
?>
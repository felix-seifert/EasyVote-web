<?php

//Infofeld auf der rechten Seite auf der Startseite. Die Sprache kann hier nur in dieser Form geändert werden, da die Session noch nicht gesetzt wurde
if(isset($_POST['en'])){
	require_once("lang/en/info_right.php");
}else{
	require_once("lang/de/info_right.php");
}
?>
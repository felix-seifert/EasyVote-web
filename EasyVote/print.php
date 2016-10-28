<?php
	session_start();
?>

<html>

<!-- Erzeugt die Oberfläche der Vorschau - für den Aufbau werden div-Tags verwendet, die wiederrum im Stylesheet durch Flexboxen definiert werden -->

<?php
	include 'wahl_navigation.php';
	require_once('header.php');
?>
<div id="complete">
		<div id="main">
	  <div id="Info"><?php require_once("nav_right_info_print.php"); ?></div>
	  <div id="Inhalt" ><?php require_once("wahlzettel.php"); ?></div>
	  <div id="Navigation"><?php require_once("nav_left_print.php"); ?></div>
	</div>
	
	<div id="headerForm">
		<div id="Header1"><?php require_once('header1.php');?></div>
		<div id="Header2"></div>	
	</div>
</div>

</html>
<?php
	session_start();
?>

<!-- Erzeugt die Oberfläche des Wahlzettels - für den Aufbau werden div-Tags verwendet, die wiederrum im Stylesheet durch Flexboxen definiert werden -->

<html>
<?php
	require_once('header.php');
	include 'wahlen/Wahl.php';
	include 'wahl_navigation.php';
?>	

<div id="complete">

	<div id="main">
	  <div id="Info"><?php require_once("nav_right.php"); ?></div>
	  <div id="Inhalt" ><?php require_once("inhalt.php"); ?></div>
	  <div id="Navigation"><?php require_once("nav_left.php"); ?></div>
	</div>
	
	<div id="headerForm">
		<div id="Header1"><?php require_once('header1.php');?></div>
		<div id="Header2"><?php require_once('lang/'.$_SESSION['lang'].$_SESSION['aktive_wahl'].'/suche.php');?></div>	
	</div>

</div>
<footer><?php require_once("footer.php"); ?></footer>
</html>

<?php
session_start();
?>

<!-- Erzeugt die Oberfl�che der Startseite - f�r den Aufbau werden div-Tags verwendet, die wiederrum im Stylesheet durch Flexboxen definiert werden -->

<html>
<div id="headerForm">
	
	<div id="Header3"></div>
	<div id="Header2" style="background: #BEDBEE;"><?php require_once('header2.php');?></div>
	<?php require_once('header.php');?>
</div>

<div id="main">
  <div id="Info"><?php require_once("nav_right_info.php"); ?></div>
  <div id="Startseite"><?php require_once("startseite.php"); ?></div>
</div>
<footer><?php require_once("footer.php"); ?></footer>
</html>
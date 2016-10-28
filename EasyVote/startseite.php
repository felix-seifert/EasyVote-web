<?php
	require_once('wahl_navigation.php');
?>
  
  <div id="Startseite">
  <?php
  
	//Setzt eine Session für die ausgewählte Spracheinstellung
	if(isset($_POST['en'])){
		$_SESSION['lang'] = 'en/';
	} else{
		$_SESSION['lang'] = 'de/';
	}
	
	$wahlNavi = new WahlNavi();
	$_SESSION['aktive_wahl'] = $wahlNavi->get_aktive_wahl();
	$_SESSION['gueltig'] = 0;	//Die Wahl ist zu Beginn ungültig
	
	
	//Wenn eine Wahl aktiv ist, wird die Startseite dieser Wahl angezeigt.
	if(!empty($_SESSION['aktive_wahl'])){
		require_once("lang/".$_SESSION['lang'].$_SESSION['aktive_wahl']."/start.php");
		$_SESSION['suche'] = $wahlNavi->search_value();		//Erstellen der Suchliste
	}else{
		echo "<h2><center>Es ist derzeit keine Wahl verf&uuml;gbar!</center></h2>";
	}
	echo "<br><br>"
	
	?>
	
	<!-- Auswahl der Spracheinstellung durch die Buttons mit den Länderflaggen -->
	<form method="post" action='index.php'>
		<center><button type="submit" name='de' value='deutsch'><img src='bilder/de2.png' width ='50px' alt='deutsch'></button>
		<button type="submit" name='en' value='english'><img src='bilder/en2.png' width ='50px' alt='english'></button> </center>
	</form>  
	
	
  </div>
 
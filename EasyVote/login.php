<?php
session_start();

//Benutzername und PW für die Authentifizierung
$benutzername = 'Admin';
$passwort = 'Admin';
$ordner = "wahlen/xmlFiles/";

if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || 
		($_SERVER['PHP_AUTH_USER'] != $benutzername) || ($_SERVER['PHP_AUTH_PW'] != $passwort)){
	//Zugansdaten falsch, Authentifizierungsheader senden
	header('WWW-Authenticate: Basic realm ="My Realm"');
	header('HTTP/1.1 401 Unauthorizied');
	exit('<h2>index.php</h2>Zugriff nur mit den korrekten Zugangsdaten.');	
}
?>


<!------------------Feld zum Hochladen einer XML Datei-------------------------------------->
<link rel="stylesheet" type="text/css" href="style.css">

<div id="Login">
<div id="Datei_hochladen">
<form action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data" method="post">
  <fieldset>
  <legend><b>Hier können Sie eine Wahl (als XML-Datei) hochladen:</b></legend>
  <b>Hinweis:</b> Der Name der Datei muss als Präfix die Wahlart enthalten gefolgt von einem Leerzeichen oder einem Unterstrich z.B. "Kommunalwahl (Hessen).xml".<br><br>
    <label for="wahl">1. Wahlart auswählen:</label>
		<select name="wahlart" size="1" id="wahl"> 
			<option value="europawahl">Europawahl</option>
            <option value="kommunalwahl">Kommunalwahl</option>
            <option value="kommunalwahl_karlsruhe">Kommunalwahl Karlsruhe</option>
			<option value="landtagswahl">Landtagswahl</option>
		</select>
    <br />
    <label for="datei">2. Datei auswählen:</label>
    <input name="wahlfile" id="wahlXML" type="file" id="datei">
    <br />
    <label><input type="submit" value="Datei hochladen"><label>
  </fieldset>
</form>

<?php
	require_once('wahl_navigation.php');

	echo "<fieldset>";
	
	//Verschiebt die hochgeladene Datei auf dem Server, so dass Zugriff möglich ist.
	if(isset($_FILES['wahlfile'])){
		if($_FILES['wahlfile']['type']=="text/xml"){

			$prefix_under = strtolower(explode("_", $_FILES['wahlfile']['name'])[0]);
			$prefix_slash = strtolower(explode(" ", $_FILES['wahlfile']['name'])[0]);
			$prefix_point = strtolower(explode(".", $_FILES['wahlfile']['name'])[0]);
			
			//Testet, ob das Präfix der hochzuladenden Datei mit der gewählten Wahlart übereinstimmt
			if(($prefix_under == $_POST['wahlart']) || ($prefix_slash == $_POST['wahlart']) || ($prefix_point == $_POST['wahlart']))
			{
				$speicherort = $ordner . $_POST['wahlart'].".xml";
				$filename = $_FILES['wahlfile']['name'];
				
				if($_FILES['wahlfile']['error']){
					die("<p>Fehler beim Upload der Datei '$filename': " . $_FILES['wahlfile']['error']."</p>");
				}
				
				if(file_exists($speicherort)){
					echo "<p>Eine Datei der gewählten Wahlart existiert schon. Löschen Sie bitte die existierende Datei, bevor Sie eine neue Datei laden!</p>";			
				} else {
					if(file_exists($_FILES['wahlfile']['tmp_name'])){
						$xml = simplexml_load_file($_FILES['wahlfile']['tmp_name']);
						$wahl_id = $xml->election_id;
						$wahl = new WahlNavi();	
						//Testet ob die WahlID der hochzuladenden Datei mit der gewählten Wahlart übereinstimmt
						if($wahl->get_wahlID($_POST['wahlart']) == $wahl_id){
							move_uploaded_file($_FILES['wahlfile']['tmp_name'], $speicherort) 
							  or die ("<p>Fehler beim Speichern der Datei '" . $speicherort . "': ".print_r(error_get_last(), true)."</p>");
							
							//evtl hier noch chmod einfügen, damit auf Linux-Servern gearbeitet werden kann
							echo "<p>Die Datei wurde erfolgreich hochgeladen.</p>";
						}
					}
				}
				
			}else{
				echo "<p>WahlID und Wahlart stimmen nicht überein. Bitte überprüfen Sie, ob Sie die korrekte Wahlart ausgewählt haben und ob die WahlID des Dokumentes korrekt ist.</p>";
			}
		}
	}
?>

 </fieldset><br>

</div>


<!-------------------Feld zum  Laden einer Wahl-------------------------------------->

<div id="Wahl_laden">
<form action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data" method="post">
  <fieldset>
  <legend><b>Ab hier können die Wahlarten ausgewählt, geladen oder gelöscht werden</b></legend>
    <label for="1">1. Soll die Wahl geladen oder gelöscht werden?</label>
		<div id='radio'><fieldset>
		<input type='radio' name='wahl_bearbeiten' id = '1' value='laden'> Wahl laden<br> 
		<input type='radio' name='wahl_bearbeiten' id = '1' value='entfernen'> Wahl entfernen 
		</fieldset></div>
    <br>
    <label for="datei">2. Wählen Sie die gewünschte Wahlart:</label>
	<select name='wahl' size='1'> 
    <br>


<?php
// Ordner auslesen und Array in Variable speichern
         
// Schleife um Array "$alledateien" aus scandir Funktion auszugeben
// Einzeldateien werden dabei in der Variable $datei abgelegt
$alledateien = scandir($ordner);
foreach ($alledateien as $datei) {
	// Zusammentragen der Dateiinfo
	$dateiinfo = pathinfo($ordner."/".$datei);
	if ($datei != "." && $datei != ".."  && $datei != "_notes") {
		echo "<option value='".$dateiinfo['filename']."'>".ucfirst($dateiinfo['filename'])."</option> ";
	}
}
echo "</select><br> <input type='submit' value='Aktion ausführen' id='datei'><br> </fieldset></form>";

echo "<br><fieldset>";

//Wenn der Radiobutton "Wahl laden" gewählt wurde, wird die Wahl in die Datenbank geladen
//Wenn der Radiobutton "Wahl entfernen" gewählt wurde, wird die Wahl aus der Datenbank entfernt und die XML-Datei entfernt
$wahl = new WahlNavi();
if(isset($_POST['wahl_bearbeiten'])){
	if($_POST['wahl_bearbeiten'] == 'laden'){
		$wahl->update_aktive_wahl($_POST['wahl']);
		$wahl->DB_erstellen();
		echo "<p>Die ".ucfirst($_POST['wahl'])." wurde erfolgreich geladen</p>";
	} else if($_POST['wahl_bearbeiten'] == 'entfernen'){
		$wahl->DB_entfernen($_POST['wahl']);
		unlink($ordner.$_POST['wahl'].".xml");
		header('location:'.$_SERVER['PHP_SELF']);
	} 
}else {
	echo "<p>Keine Aktion ausgewählt</p>";
}
?> 
</fieldset>
</div>


<br><br><a href="index.php">Zur&uuml;ck zum Startmenü</a>
</div>
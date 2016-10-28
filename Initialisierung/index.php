<html>

	<?php include('Initialisierung.php'); ?>
	
	<p>1. Erstellt die Tabelle "Wahlen", in der alle Wahlen enthalten sind und die aktive Wahl gespeichert wird:<p>
	<form method="post" action="index.php">
		<input type="submit" value="Wahltabelle erstellen" name ="wahlen_erstellen" class="button"/>
	</form>
	<br><br>
	
	<p>2. Erstellt eine Tabelle für jede Wahl mit 5 voreingestellten Benutzernamen. Diese dient dazu, dass eine Stimme hochgeladen werden kann.<p>
	<form method="post" action="index.php">
		<button type="submit" name="db_erstellen" value="kommunalwahl" class="button">Abgabetabelle für Kommunalwahl erstellen</button><br>
		<button type="submit" name="db_erstellen" value="europawahl" class="button">Abgabetabelle für Europawahl erstellen</button><br>
		<button type="submit" name="db_erstellen" value="landtagswahl" class="button">Abgabetabelle für Landtagswahl erstellen</button><br>
	</form>
	<br><br>
	
	<p>3. Hier können eigene Benutzer oder Passwörter für die Stimmenabgabe definiert werden.</p> 
	<form method="post" action="index.php">
		Angabe, welcher Wahl ein Benutzer hinzugefügt werden soll:<br>
		<input type='radio' name='wahlname' id = '1' value='kommunalwahl'> Kommunalwahl<br> 
		<input type='radio' name='wahlname' id = '1' value='europawahl'> Europawahl<br>
		<input type='radio' name='wahlname' id = '1' value='landtagswahl'> Landtagswahl<br><br>
	
		<label for "login">Benutzername </label>
		<input type="text" id="login" name="login"/><br>
				
		<label for "passwortlog">Passwort: </label>
		<input type="password" id="passwortlog" name="passwortlog" /><br>
				
		<input type="submit" value="Benutzer hinzug&uuml;gen" name ="submit" class="button"/>
	</form>
	
	<?php 
		if(isset($_POST['wahlen_erstellen'])){
			$init = new Initialisierung();
			$init->wahl_tabelle_erstellen();
		}
		
		if(isset($_POST['db_erstellen'])){
			$init = new Initialisierung();
			$init->datenbank_erstellen($_POST['db_erstellen']);
		}
		
		if(isset($_POST['submit'])){
			$init = new Initialisierung();
			$passwort = hash('sha256', $_POST['passwortlog'].$_POST['login']);
			$init->eintrag_hinzufuegen($_POST['login'], $passwort, $_POST['wahlname']);
		}
		
	?>
	
</html>
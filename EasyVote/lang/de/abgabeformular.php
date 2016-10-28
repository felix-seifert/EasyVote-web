<div id='Abgabe'>
	<form method="post" action="print.php">
		 <fieldset>
		<legend><b>Geben Sie hier die Ihnen zugesandten Daten ein</b></legend>
			<label for "login">Benutzername: </label>
			<input type="text" id="login" name="login" /><br>
			<br>
			<label for "passwortlog">Passwort: </label>
			<input type="password" id="passwortlog" name="passwortlog" /><br>
			<br>
			<input type="submit" value="Wahl hochladen" name ="submit" class="suchbutton"/>
		  </fieldset>
		</form>

		<?php if(isset($_SESSION['ausgabe'])){
			echo "<fieldset>";
			require_once("lang/".$_SESSION['lang']."ausgabe_fehlermeldungen.php");		
			echo "</fieldset>";
			
		}?>

<br><br><br><a href='print.php' class='button2'>Zur&uuml;ck zur Wahl&uuml;bersicht</a>
</div>
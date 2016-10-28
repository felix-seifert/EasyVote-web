<div id='Abgabe'>
	<form method="post" action="print.php">
		 <fieldset>
		<legend><b>Please enter your provided data</b></legend>
			<label for "login">Username: </label>
			<input type="text" id="login" name="login"/><br>
			<br>
			<label for "passwortlog">Password: </label>
			<input type="password" id="passwortlog" name="passwortlog"/><br>
			<br>
			<input type="submit" value="Upload Ballot Card" name ="submit" class="suchbutton"/>
		  </fieldset>
		</form>

		<?php if(isset($_SESSION['ausgabe'])){
			echo "<fieldset>";
			require_once("lang/".$_SESSION['lang']."ausgabe_fehlermeldungen.php");		
			echo "</fieldset>";
			
		}?>
		
<br><br><br><a href='print.php' class='button2'>Back to the ballot card</a>
</div>
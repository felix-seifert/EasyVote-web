
		<?php
			//Laden der Parteien
			$wahl = new WahlNavi();
			$wahl->parteien_laden();
			
		?>
	
		<!-- Erzeugt die Buttons, die bei jeder Wahl angezeigt werden, also "Ungültig wählen", "Eingaben löschen", "Neu Starten" und "Stimme ansehen/Druck" -->
		<br><br><br><br>
		<?php 
			if($_SESSION['lang'] == 'de/'){?>
				<button type="submit" name="ungueltig" class="special_button">Ung&uuml;ltig w&auml;hlen</button>
		
				<button type="submit" name="reset" class="special_button">Eingaben l&ouml;schen</button>
		
				<button type="submit" name="neustart" class="special_button">Neu Starten</button>
	 
				<button type="submit" name="wahl_submit" class="special_button_aktiv">Stimme ansehen<br>Druck</button>
			<?php } else{ ?>
				<button type="submit" name="ungueltig" class="special_button">Spoil ballot</button>
		
				<button type="submit" name="reset" class="special_button">Clear entries</button>
		
				<button type="submit" name="neustart" class="special_button">Restart</button>
	 
				<button type="submit" name="wahl_submit" class="special_button_aktiv">View ballot card<br>Print</button>
			<?php } ?>
		
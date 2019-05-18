<br><br><br><br>

<?php

        //Erhält den Wahlcode und fügt die Session-Variable hinzu, die den Header bestimmt      FIXME
        $wahlNavi = new WahlNavi();
        $wahlcode = $wahlNavi->wahl_auswerten();
        if($_SESSION['aktive_wahl'] != 76131) {
            if (isset($_SESSION['alert'])) {
                $wahlcode = $wahlcode . "-" . $_SESSION['alert'];
            } else {
                $wahlcode = $wahlcode . "-";
            }
        }

		//Erzeugt die Buttons, die bei jeder wahl angezeigt werden, also "Zurück", "Stimme hochladen" und "PDF erstellen"
		if($_SESSION['lang'] == 'de/'){?>
			<form method='post' action='print.php'>
				<button type="submit" name="back" class="button"><<< Zur&uuml;ck</button>
                <!-- Disable for the municipal elections Karlsruhe
				<button type="submit" name="onlineAbgabe" class="button">Stimme hochladen</button> -->
			</form>
		  
		  
			<form method='post' action='makePDF.php'>
				<?php //echo "<button type='submit' name='pdf' value=$wahlcode class='button'> PDF erstellen</button>"      // FIXME ?>
			</form>
		<?php }else{?>
			<form method='post' action='print.php'>
				<button type="submit" name="back" class="button"><<< Back</button>
                <!-- Disable for the municipal elections Karlsruhe
				<button type="submit" name="onlineAbgabe" class="button">Upload Vote</button> -->
			</form>
		  
			<form method='post' action='makePDF.php'>
				<?php echo "<button type='submit' name='pdf' value=$wahlcode class='button'> Create PDF</button>"?>
			</form> 
		<?php }?>
  
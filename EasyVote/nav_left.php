
		<?php
			//Laden der Parteien
			$wahl = new WahlNavi();
			$wahl->parteien_laden();
		?>
	
		<!-- Erzeugt die Buttons, die bei jeder Wahl angezeigt werden, also "Ungültig wählen", "Eingaben löschen", "Neu Starten" und "Stimme ansehen/Druck" -->
		<br><br><br><br>
		<?php

            //Wenn die Wahl geändert wird, wird überprüft, ob ein neuer Header geladen werden muss (durch die Methode create_rules)
            if(isset($_POST['vote_changed'])){
                $wahl = new WahlNavi();
                $wahl->create_rules();
            }

            if(isset($_SESSION['possible_submission']) && $_SESSION['possible_submission']) {
                $disabled = '';
                $tooltip = '';
            }
            else {
                $disabled = 'disabled';
                // Tooltip when submission is not possible
                $tooltip = "<span class='tooltip'>"
                    ."F&uuml;r eine ung&uuml;ltige bzw. leere Auswahl wird kein m&ouml;glicher Stimmzettel generiert."
                    ."</span>";
            }

			if($_SESSION['lang'] == 'de/'){?>
                <form method='post' action='election.php'>
                    <!-- Disable for the municipal elections Karlsruhe
				    <button type="submit" name="ungueltig" class="special_button">Ung&uuml;ltig w&auml;hlen</button>
		            -->
                    <button type="submit" name="show_rules" class="special_button">Regeln ansehen</button>
                    <button type="submit" name="reset" class="special_button">Eingaben l&ouml;schen</button>
				    <button type="submit" name="neustart" class="special_button">Neu Starten</button>
                    <div class="view_votes_button">
    				    <button type="submit" name="wahl_submit" class="special_button_aktiv" <?php echo $disabled; ?>>Stimmabgabe ansehen</button>
                        <?php echo $tooltip; ?>
                    </div>
                </form>
			<?php } else{ ?>
                <form method='post' action='election.php'>
                    <!-- Disable for the municipal elections Karlsruhe
				    <button type="submit" name="ungueltig" class="special_button">Spoil ballot</button>
				    -->
                    <button type="submit" name="show_rules" class="special_button">View Rules</button>
    				<button type="submit" name="reset" class="special_button">Clear Entries</button>
    				<button type="submit" name="neustart" class="special_button">Restart</button>
                    <div class="view_votes_button">
    				    <button type="submit" name="wahl_submit" class="special_button_aktiv" <?php echo $disabled; ?>>View Ballot Card</button>
                        <?php echo $tooltip; ?>
                    </div>
                </form>
			<?php } ?>
		
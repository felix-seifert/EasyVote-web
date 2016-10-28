<?php if(isset($_SESSION['fehler_suche']) && $_SESSION['fehler_suche']){
	echo"<p>Um einen Kandidaten zu finden, wählen Sie bitte einen Vorschlag aus der Liste aus.</p>";
}else{ ?>

	<p>Wählen Sie in der linken Spalte die Partei aus, in der Sie Stimmen vergeben wollen. Sie sehen dann alle der Partei zugehörigen Kandidaten. Sie können einen Kandidaten auch suchen indem Sie die Suche in der rechten oberen Ecke benutzen. Beachten Sie bitte dabei, dass Sie den Listeneintrag des gesuchten Kandidaten auswählen, bevor Sie auf "Suchen" klicken.</p>
	<br>
	<p>Kreuze können zurück genommen werden, indem noch einmal auf das Kreuz geklickt wird.</p>
	<br>
	<p>Um einen Kandidaten zu streichen, klicken Sie auf den entsprechenden seinen Namen.</p>
	<br>
	<p>Um Ihren Stimmzettel als "ungültig" zu kennzeichnen, drücken Sie auf den Button "Ungültig wählen".</p>
	<br>
	<p>Um Ihre Stimme im Anschluss zu drucken oder hochzuladen, drücken Sie auf "Stimme ansehen". </p>

<?php } ?>
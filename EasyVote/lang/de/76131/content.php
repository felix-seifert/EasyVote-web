<?php if(isset($_SESSION['fehler_suche']) && $_SESSION['fehler_suche']){
	echo"<p>Um Kandidierende zu finden, wählen Sie bitte einen Vorschlag aus der Liste aus.</p>";
}else{ ?>

	<p>
        Wählen Sie in der linken Spalte die Liste aus, in der Sie Stimmen vergeben wollen. Sie sehen dann alle
        der Liste zugehörigen Kandidierenden. Sie können Kandidierende auch suchen, indem Sie das Suchfeld in der
        rechten oberen Ecke benutzen. Beachten Sie dabei bitte, den Listeneintrag der gesuchten Person auszuwählen,
        bevor Sie auf "Suchen" klicken.
    </p>
	<br>
    <p>
        Das Ankreuzen einer Liste bedeutet, dass der Stimmzettel dieser Liste ohne weitere Markierungen abgegeben
        wird und so alle Kandidierenden der Liste eine Stimme bekommen. Das Kreuz kann zurück genommen werden, indem
        noch einmal auf das Kreuz geklickt wird.
    </p>
    <br>
    <p>Kandidierenden können bis zu drei Stimmen über das Auswahlfeld gegeben werden.</p>
    <br>
	<p>
        Im Anschluss können Sie über das Menü links Ihre Stimmabgabe ansehen und eine Empfehlung für das korrekte
        Ausfüllen erhalten.
    </p>

<?php } ?>
<?php if(isset($_SESSION['fehler_suche']) && $_SESSION['fehler_suche']){
	echo"<p>To find a candidate, please choose a recommendation from the provided list.</p>";
}else{ ?>

	<p>Select a party on the left hand side to see the candidates that run for the party.
	If your are looking for a specific party member you can use the search function in the right upper corner.
	Please note that you have to choose a entry from the list before clicking "Search".</p>

	<br>
	<p>To reverse a vote cast uncheck it.</p>

	<br>
	<p>Click on a candidate's name to delete him from the ballot card.</p>

	<br>
	<p>Select "Spoil ballot" in the left cloumn to spoil your ballot card.</p>

	<br>
	<p>If you want to print or upload your ballot card click on "View ballot card". </p>

<?php } ?>
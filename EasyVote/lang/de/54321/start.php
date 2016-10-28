 <?php 
 require_once('wahl_navigation.php');
$wahlNavi = new wahlNavi;
$maxvotes = $wahlNavi->get_maxvotes();
?>

<h1>Herzlich Willkommen zur Kommunalwahl<br>des Landes Hessen 2013</h1>
<br>
<ul>
<li>Sie können alle <?php echo "$maxvotes" ?> Stimmen an verschiedene Bewerberinnen und Bewerber in verschiedenen Wahlvorschlägen vergeben - panaschieren - 
	und dabei jeder Person auf dem Stimmzettel bis zu drei Stimmen geben - kumulieren - (<img src='bilder/init1vote.png' height='15'> oder <img src='bilder/init2vote.png' height='15'> oder <img src='bilder/init3vote.png' height='15'>).
	</li>
<br>
<li>Sie können, wenn Sie nicht alle <?php echo "$maxvotes" ?> Stimmen einzeln vergeben wollen oder noch Stimmen übrig haben, zusätzlich einen Wahlvorschlag in der Kopfleiste kennzeichnen <img src='bilder/initvote.png' height='15'>.
	In diesem Fall hat das Ankreuzen der Kopfleiste zur Folge, dass den Bewerberinnen und Bewerbern des betroffenen Wahlvorschlags in der Reihenfolge ihrer Benennung so lange eine weitere Stimme zugerechnet wird, bis alle Stimmen verbraucht sind.
	</li>
<br>
<li>Sie können einen Wahlvorschlag auch nur in der Kopfleiste kennzeichnen <img src='bilder/initvote.png' height='15'>, ohne Stimmen an Personen zu vergeben. Das hat zur Folge, dass jede Person in der Reihenfolge des Wahlvorschlags so lange jeweils eine Stimme erhält bis alle <?php echo "$maxvotes" ?> Stimmen vergeben oder jeder Person des Wahlvorschlags drei Stimmen zugeteilt sind.
</li>
<br>
<li>Falls Sie einen Wahlvorschlag in der Kopfleiste kennzeichnen, können Sie auch Bewerberinnen und Bewerber in diesem Wahlvorschlag streichen. Diesen Personen werden keine Stimmen zugeteilt.
</li>
</ul>
<br><br><br>

<center><a href='election.php' class='button2'>Stimmabgabe beginnen</a></center>
 